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
use App\Roles;
use Illuminate\Database\Eloquent\Builder;

class UsuariosController extends Controller
{
    //
    public function roles(Request $request)
    {
        $roles = Roles::all();
        // $roles[sizeof($roles)] = array('nombre' => 'Todos');
        return response()->json($roles, 200);
    }
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

    public function misForos(Request $request)
    {
        $usuario = JWTAuth::user();
        if ($request->rol === 'Alumno')
            $misForos = $usuario->proyectos()->with('foro')->get()->pluck('foro');
        if ($request->rol === 'Docente')
            $misForos = $usuario->asesor()->with('foro')->get()->pluck('foro');
        if ($request->rol === 'Administrador')
            $misForos = Foros::where('user_id', $usuario->id)->get();
        $misForos = $misForos->map(function ($foro) {
            if ($foro->acceso === 1)
                return 'Foro en curso';
            return 'Foro ' . $foro->no_foro;
        });
        return response()->json($misForos, 200);
    }
    public function misNotificaciones(Request $request)
    {
        $respuesta = $request->respuesta == 'Aceptados' ? true : ($request->respuesta == 'Rechazados' ? false : null);       

        $notificacionesQuery = Notificaciones::query();
        $usuario = JWTAuth::user();
        $notificacionesQuery = $usuario->misNotificaciones();
        $foro = Foros::where('acceso', true)->first();
        if ($request->no_foro === 'Foro en curso') {
            if (is_null($foro))
                return response()->json(['data' => []], 200);
            $notificacionesQuery->whereHas('proyecto.foro', function (Builder $query) {
                $query->where('acceso', true);
            });
        } else {
            $notificacionesQuery->whereHas('proyecto.foro', function (Builder $query) use ($request) {
                $query->where('no_foro', $request->no_foro);
            });
        }
        if (is_null($foro))
            return response()->json(['mensaje' => 'No hay ningun foro activo'], 200);
        

        $hoy = Carbon::now()->toDateString();

        $notificacionesQuery->with(['proyecto', 'tipo_solicitud', 'nuevo_asesor:id,prefijo,nombre,apellidoP,apellidoM'])->where('respuesta', $respuesta);
        if ($request->rol === 'Administrador') {
            // $misNotificaciones['data'] = $notificacionesQuery->where('administrador', 1)->get();
            $notificacionesQuery->where('administrador', 1);
        } else if ($request->rol === 'Docente') {
            // $misNotificaciones['data'] = $notificacionesQuery->where('administrador', 0)->get();
            $notificacionesQuery->where('administrador', 0);
            // ->whereHas('proyecto', function (Builder $query) {
                // $query->where('enviado', true);
            // });
        } else if ($request->rol === 'Alumno') {
            $notificacionesQuery->where('administrador', 0);
        }
        
        $misNotificaciones['data'] = $notificacionesQuery->get();


        if ($hoy > $foro->fecha_limite)
            $misNotificaciones['data'] = $misNotificaciones['data']->filter(function ($notificacion) use ($foro) {
                return $notificacion->fecha > $foro->fecha_limite;
            })->values();
        $misNotificaciones['data'] = $misNotificaciones['data']->groupBy('solicitud');
        // foreach
        $total = 0;
        // if ($request->respuesta === 'Pendientes') {
        foreach ($misNotificaciones['data'] as $key => $solicitud) {
            // la llave
            foreach ($solicitud as $itemSolicitud) {                                
                // $itemSolicitud->editar = false;
                $total++;
            }
        }
        // }
        $misNotificaciones['total'] = $total;
        return response()->json($misNotificaciones, 200);
    }
    public function miSolicitud()
    {
        $usuario = JWTAuth::user();
        $foro = Foros::where('acceso', true)->first();
        if (is_null($foro))
            return response()->json(['mensaje' => 'No hay ningun foro activo'], 200);

        $miSolicitud['proyecto'] = $usuario->proyectos()
        // ->select('folio','titulo','empresa','objetivo','lineadeinvestigacion_id','tipos_proyectos_id','asesor','enviado','permitir_cambios')
        ->with(['lineadeinvestigacion', 'tipos_proyectos', 'asesora'])->whereHas('foro', function (Builder $query) {
            $query->where('acceso', true);
        })->first();
        if (is_null($miSolicitud['proyecto']))
            return response()->json(['mensaje' => 'No tienes ningún proyecto registrado al foro en curso'], 200);

        $hoy = Carbon::now()->toDateString();
        $miSolicitud['data'] = $usuario->miSolicitud()->whereHas('proyecto.foro', function (Builder $query) {
            $query->where('acceso', true);
        })->with('tipo_solicitud', 'proyecto:id,titulo,folio')->with(['receptor'])->orderBy('respuesta', 'desc')->get();


        if ($hoy > $foro->fecha_limite) {
            $miSolicitud['data'] = $miSolicitud['data']->filter(function ($notificacion) use ($foro) {
                return $notificacion->fecha > $foro->fecha_limite;
            });
            $miSolicitud['proyecto']->enviar = false;
            $miSolicitud['proyecto']->editar = false;
            $miSolicitud['proyecto']->cancelar = false;
        }
        $miSolicitud['data'] = $miSolicitud['data']->groupBy('solicitud');


        $miSolicitud['proyecto']->editar = $miSolicitud['proyecto']->editarDatos();
        $miSolicitud['proyecto']->enviar = $miSolicitud['proyecto']->enviarSolicitud();
        $miSolicitud['proyecto']->cancelar = $miSolicitud['proyecto']->cancelarSolicitud();        


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
        if($usuario->hasRole('Docente')){
            if($notificacion->respuesta === false){
                $proyecto->enviado = 0;
                $proyecto->aceptado = 0;
            }
            else if($notificacion->respuesta === true){
                $proyecto->aceptado= 1;
            }            
            $proyecto->save();
        }        


        $message = $request->respuesta ? 'Proyecto aceptado' : 'Proyecto rechazado';
        return response()->json(['message' => $message], 200);
    }
}
