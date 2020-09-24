<?php

namespace App\Http\Controllers;

use JWTAuth;
use App\User;
use App\Foro;
use App\Rol;
use App\Proyecto;
use Carbon\Carbon;
use App\Notificacion;
use App\TipoDeSolicitud;
use App\Mail\ForgotPassword;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Database\Eloquent\Builder;
use App\Http\Requests\CambiarPasswordRequest;
use Illuminate\Database\Eloquent\Collection;

class UsuariosController extends Controller
{
    //
    public function roles(Request $request)
    {
        $roles = Rol::all();
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
        Mail::to($request->email)->send(new ForgotPassword);
        return response()->json(['message' => 'Contraseña generada y enviada a su correo'], 200);
    }

    public function misForos(Request $request)
    {
        $usuario = JWTAuth::user();
        if ($request->rol === 'Alumno')
            $misForos = $usuario->proyectos()->with('foro')->get()->pluck('foro');
        if ($request->rol === 'Docente')
            $misForos = $usuario->asesor()->with('foro')->get()->pluck('foro');
        if ($request->rol === 'Administrador')
            $misForos = Foro::where('user_id', $usuario->id)->get();
        $misForos = $misForos->filter(function ($foro) {
            return $foro->activo !== 1;
        });
        return response()->json($misForos, 200);
    }
    public function misNotificaciones(Request $request)
    {
        $respuesta = $request->respuesta == 'Aceptados' ? true : ($request->respuesta == 'Rechazados' ? false : null);

        $notificacionesQuery = Notificacion::query();
        $usuario = JWTAuth::user();
        $notificacionesQuery = $usuario->misNotificaciones();
        $foro = Foro::Activo()->first();
        if ($request->no_foro === 'Foro en curso') {
            if (is_null($foro))
                return response()->json(['data' => []], 200);
            $notificacionesQuery->whereHas('proyecto.foro', function (Builder $query) {
                $query->where('activo', true);
            });
        } else {
            $notificacionesQuery->whereHas('proyecto.foro', function (Builder $query) use ($request) {
                $query->where('no_foro', $request->no_foro);
            });
        }
        if (is_null($foro))
            return response()->json(['mensaje' => 'No hay ningun foro activo'], 200);


        $hoy = Carbon::now()->toDateString();

        $notificacionesQuery->with(['proyecto', 'tipo_de_solicitud', 'nuevo_asesor:id,prefijo,nombre,apellidoP,apellidoM'])->where('respuesta', $respuesta);

        if ($request->rol === 'Administrador') {
            $notificacionesQuery->where('administrador', 1);
        } else if ($request->rol === 'Docente') {
            $notificacionesQuery->where('administrador', 0);
        } else if ($request->rol === 'Alumno') {
            $notificacionesQuery->where('administrador', 0);
        }

        $misNotificaciones['data'] = $notificacionesQuery->get();


        // if ($hoy > $foro->fecha_limite)
        //     $misNotificaciones['data'] = $misNotificaciones['data']->filter(function ($notificacion) use ($foro) {
        //         return $notificacion->fecha > $foro->fecha_limite;
        //     })->values();


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
        $foro = Foro::Activo()->first();
        if (is_null($foro))
            return response()->json(['mensaje' => 'No hay ningun foro activo'], 200);

        $miSolicitud['proyecto'] = $usuario->proyectos()
            // ->select('folio','titulo','empresa','objetivo','lineadeinvestigacion_id','tipos_proyectos_id','asesor','enviado','permitir_cambios')
            ->with(['linea_de_investigacion', 'tipo_de_proyecto', 'asesor'])->whereHas('foro', function (Builder $query) {
                $query->where('activo', true);
            })->first();
        if (is_null($miSolicitud['proyecto']))
            return response()->json(['mensaje' => 'No tienes ningún proyecto registrado al foro en curso'], 200);

        $hoy = Carbon::now()->toDateString();
        $miSolicitud['data'] = $usuario->miSolicitud()->whereHas('proyecto.foro', function (Builder $query) {
            $query->where('activo', true);
        })->with('tipo_de_solicitud', 'proyecto:id,titulo,folio')->with(['receptor'])->orderBy('respuesta', 'desc')->get();


        if ($hoy > $foro->fecha_limite) {
            $miSolicitud['data'] = $miSolicitud['data']->filter(function ($notificacion) use ($foro) {
                return $notificacion->fecha > $foro->fecha_limite;
            });
            $miSolicitud['proyecto']->enviar = false;
            $miSolicitud['proyecto']->editar = false;
            $miSolicitud['proyecto']->cancelar = false;
            $miSolicitud['proyecto']->inTime = !$miSolicitud['proyecto']->aceptado ? false : true;
        } else {
            $miSolicitud['proyecto']->editar = $miSolicitud['proyecto']->editarDatos();
            $miSolicitud['proyecto']->enviar = $miSolicitud['proyecto']->enviarSolicitud();
            $miSolicitud['proyecto']->cancelar = $miSolicitud['proyecto']->cancelarSolicitud();
            $miSolicitud['proyecto']->inTime = true;
        }
        $miSolicitud['data'] = $miSolicitud['data']->groupBy('solicitud');





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
        // aqui poner en el request un validate
        // validar respuestas, pueden hacerlo con postman
        $usuario = JWTAuth::user();
        $proyecto = Proyecto::where('folio', $folio)->first();
        // $proyecto = $usuario->getProyectoActual();        
        if (is_null($proyecto))
            return response()->json(['message' => 'Proyecto no encontrado'], 404);
        // $foro = $proyecto->foro()->first();
        $foro = $proyecto->foro;
        if (!$foro->activo)
            return response()->json(['message' => 'No puedes responder notificaciones de foros inactivos'], 400);
        $solicitud = TipoDeSolicitud::where('nombre_', $request->solicitud)->first();
        $notificacion = $usuario->misNotificaciones()->where([
            ['proyecto_id', $proyecto->id],
            ['receptor_id', $usuario->id],
            ['tipo_de_solicitud_id', $solicitud->id]
        ])->first();
        if (is_null($notificacion))
            return response()->json(['message' => 'Notificación no encontrada'], 404);
        $notificacion->respuesta = $request->respuesta;


        $notificacionesAceptadas = 0;
        if ($solicitud->nombre_ !== 'REGISTRO DE PROYECTO') {
            $integrantes = $proyecto->integrantes()->count() - 1;
            if ($integrantes === $proyecto->notificaciones()->whereHas('receptor.roles', function (Builder $query) {
                $query->where('nombre_', 'Alumno');
            })->where([['respuesta', true], ['tipo_de_solicitud_id', $solicitud->id]])->count() && $usuario->hasRole('Alumno')) {
                $this->agregarNotifcacion($notificacion, $proyecto->asesor()->first()->id, 0);
            }
        }

        if ($usuario->hasRole('Docente')) {
            if ($notificacion->respuesta === false) {
                if ($solicitud->nombre_ === 'REGISTRO DE PROYECTO') {
                    $proyecto->enviado = 0;
                    $proyecto->aceptado = 0;
                    $proyecto->permitir_cambios = 0;
                    $proyecto->asesor_id = null;
                }
            } else if ($notificacion->respuesta === true) {
                if ($solicitud->nombre_ === 'REGISTRO DE PROYECTO') {
                    // checar si alguien más no lo ha notificado



                    $notificacionesPendientes = $proyecto->notificaciones()->whereHas('receptor.roles', function (Builder $query) {
                        $query->where('nombre_', 'Docente');
                    })->where([['respuesta', null], ['tipo_de_solicitud_id', $solicitud->id], ['receptor_id','!=',$usuario->id]])->count();
                    if ($notificacionesPendientes > 0)
                        return response()->json(['message' => 'No es posible aceptar el proyecto. Ya han notificado a otro maestro'], 400);


                    $proyecto->aceptado = 1;
                    $proyecto->asesor()->associate($usuario);
                } else {
                    $admin = User::UsuariosConRol('Administrador')->first();
                    if (is_null($admin))
                        return response()->json(['message' => 'Administrador no encontrado'], 404);
                    $this->agregarNotifcacion($notificacion, $admin->id, 1);
                }
            }            
        }
        $notificacion->save();
        $proyecto->save();


        $message = $request->respuesta ? 'Solicitud aceptada' : 'Solicitud rechazada';
        return response()->json(['message' => $message], 200);
    }


    public function misProyectos(Request $request)
    {
        $usuario = JWTAuth::user();
        $proyectos = new Collection();
        if ($usuario->hasRole('Alumno')) {
            $proyectos = $usuario->proyectos()->with(['asesor', 'linea_de_investigacion', 'tipo_de_proyecto'])->get();
        } else if ($usuario->hasRole('Docente')) {
            $proyectos = $usuario->asesor()->with(['integrantes', 'linea_de_investigacion', 'tipo_de_proyecto'])->get();
            foreach ($proyectos as $proyecto) {
                $proyecto->inTime = $proyecto->inTime();
            }
        }
        return response()->json($proyectos, 200);
        // return $proyectos;
    }


    public function agregarNotifcacion(Notificacion $notificacion, int $id, int $admin)
    {
        $notificaciones[] = [
            // Notificaciones::create([
            'emisor_id' => $notificacion->emisor_id,
            'receptor_id' => $id, //$proyecto->asesora()->first()->id,
            'administrador' => $admin,
            'proyecto_id' => $notificacion->proyecto_id,
            'tipo_de_solicitud_id' => $notificacion->tipo_de_solicitud_id,
            'anterior_asesor_id' => $notificacion->nuevo_asesor_id,
            'nuevo_asesor_id' => $notificacion->nuevo_asesor_id,
            'titulo_nuevo' => $notificacion->nuevo_titulo,
            'titulo_anterior' => $notificacion->nuevo_titulo,
            'motivo' => $notificacion->motivo,
            'fecha' => Carbon::now()->toDateString()
        ];
        Notificacion::insert($notificaciones);
    }

    public function realizarCambios($solicitud)
    {
        switch ($solicitud) {
            case '':
                break;
            case '':
                break;
            case '':
                break;
            case '':
                break;
        }
    }
}
