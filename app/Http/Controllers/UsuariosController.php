<?php

namespace App\Http\Controllers;

use App\Foros;
use JWTAuth;
use App\Mail\ForgotPassword;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Requests\CambiarPasswordRequest;
use App\Notificaciones;
use Carbon\Carbon;
use App\User;
use App\Proyectos;
use App\TiposSolicitud;
use Illuminate\Database\Eloquent\Builder;

class UsuariosController extends Controller
{
    //
    public function cambiar_contrasena(CambiarPasswordRequest $request)
    {
        $usuarioLogueado = JWTAuth::user();
        $usuarioLogueado->password = bcrypt($request->nuevo_password);
        $usuarioLogueado->save();
        return response()->json(['message' => 'Contraseña actualizada'], 200);
    }
    public function forgot_password(Request $request)
    {
        $request->validate([
            'email' => 'exists:users,email'
        ]);
        // Mail::to($request->email)->send(new ForgotPassword);

    }
    public function misNotificaciones(Request $request)
    {
        $usuario = JWTAuth::user();
        $administradores = User::whereHas('roles', function (Builder $query) {
            $query->where('roles.nombre_', 'Administrador');
        })->get()->pluck('id')->toArray();
        $hoy = Carbon::now()->toDateString();
        $foro = Foros::where('acceso', true)->firstOrFail();
        $notificaciones = Notificaciones::query();
        if ($request->rol === 'Administrador') {
            $misNotificaciones['data'] = $usuario->misNotificaciones()->whereHas('proyecto.foro', function (Builder $query) {
                $query->where('acceso', true);
            })->with(['proyecto:id,titulo,folio', 'tipo_solicitud', 'nuevo_asesor' => function ($query) {
                $query->select('id', DB::raw("CONCAT(IFNULL(prefijo,''),' ',nombre,' ',apellidoP,' ',apellidoM) AS nombreCompleto"));
            }])->where('respuesta', null)->where('administrador',1)->get();
        } else {
            $misNotificaciones['data'] = $usuario->misNotificaciones()->whereHas('proyecto.foro', function (Builder $query) {
                $query->where('acceso', true);
            })->with(['proyecto:id,titulo,folio', 'tipo_solicitud', 'nuevo_asesor' => function ($query) {
                $query->select('id', DB::raw("CONCAT(IFNULL(prefijo,''),' ',nombre,' ',apellidoP,' ',apellidoM) AS nombreCompleto"));
            }])->where('respuesta', null)->where('administrador',0)->get();
        }
        $misNotificaciones2 = $misNotificaciones['data']->unique()->values('receptor')->all();


        // return response()->json($misNotificaciones, 200);
        if ($hoy > $foro->fecha_limite)
            $misNotificaciones['data'] = $misNotificaciones['data']->filter(function ($notificacion) use ($foro) {
                return $notificacion->fecha > $foro->fecha_limite;
            })->values();
        $misNotificaciones['data'] = $misNotificaciones['data']->groupBy('solicitud');
        $total = 0;
        foreach ($misNotificaciones['data'] as $solicitud) {
            foreach ($solicitud as $itemSolicitud) {
                $total++;
            }
        }
        $misNotificaciones['total'] = $total;
        return response()->json($misNotificaciones, 200);
    }
    public function miSolicitud()
    {
        $usuario = JWTAuth::user();
        // User::where('num_control', 'prueba')->firstOrFail();
        $foro = Foros::where('acceso', true)->firstOrFail();
        $hoy = Carbon::now()->toDateString();
        $miSolicitud['data'] = $usuario->miSolicitud()->whereHas('proyecto.foro', function (Builder $query) {
            $query->where('acceso', true);
        })->with('tipo_solicitud', 'proyecto:id,titulo,folio')->with(['receptor' => function ($query) {
            $query->select('id', DB::raw("CONCAT(IFNULL(prefijo,''),' ',nombre,' ',apellidoP,' ',apellidoM) AS nombreCompleto"));
        }])->orderBy('respuesta', 'desc')->get();


        if ($hoy > $foro->fecha_limite)
            $miSolicitud['data'] = $miSolicitud['data']->filter(function ($notificacion) use ($foro) {
                return $notificacion->fecha > $foro->fecha_limite;
            });
        $miSolicitud['data'] = $miSolicitud['data']->groupBy('solicitud');
        $miSolicitud['proyecto'] = $usuario->proyectos()->select('titulo', 'folio')->whereHas('foro', function (Builder $query) {
            $query->where('acceso', true);
        })->firstOrFail();

        $miSolicitud['proyecto']->editar = $hoy > $foro->fecha_limite ? false : true;

        foreach ($miSolicitud['data'] as $solicitud) {
            $aceptados = 0;
            foreach ($solicitud as $receptores) {
                if ($receptores->respuesta)
                    $aceptados++;
            }
            $solicitud[] = (object)$aceptados;
        }
        return response()->json($miSolicitud, 200);
    }

    public function responder_notificacion(Request $request, $folio)
    {
        $usuario = JWTAuth::user();
        $proyecto = Proyectos::where('folio', $folio)->firstOrFail();
        $solicitud = TiposSolicitud::where('nombre_', $request->solicitud)->firstOrFail();
        $notificacion = $usuario->misNotificaciones()->where([
            ['proyecto_id', $proyecto->id],
            ['receptor', $usuario->id],
            ['tipo_solicitud', $solicitud->id]
        ])->firstOrFail();
        $notificacion->respuesta = $request->respuesta;
        $notificacion->save();
        $message = $request->respuesta ? 'Proyecto aceptado' : 'Proyecto rechazado';
        return response()->json(['mensaje' => $message], 200);
    }
}
