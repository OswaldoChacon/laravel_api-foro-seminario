<?php

namespace App\Http\Controllers;

use JWTAuth;
use App\User;
use App\Proyecto;
use Carbon\Carbon;
use App\Notificacion;
use App\TipoDeSolicitud;
use Illuminate\Http\Request;
use App\Http\Requests\SolicitudRequest;
use Illuminate\Database\Eloquent\Builder;


class AlumnoController extends Controller
{
    
   
    public function cancelarSolicitud($folio)
    {
        $usuarioLogueado = JWTAuth::user();
        $proyecto = Proyecto::Buscar($folio)->firstOrFail();
        if (!$usuarioLogueado->esMiProyecto($proyecto))
            return response()->json(['message' => 'No puedes cancelar la solicitud del proyecto ya que no perteneces a él'], 400);
        if ($proyecto->aceptado)
            return response()->json(['message' => 'El proyecto ya fue aceptado'], 200);
        $proyecto->enviado = false;
        $proyecto->save();

        $solicitud = TipoDeSolicitud::where('nombre_', 'Registro de proyecto')->firstOrFail();
        $notificacion = $proyecto->notificaciones()->ReceptorConRol('Docente')->where([['tipo_de_solicitud_id', $solicitud->id]])->first();
        if (is_null($notificacion))
            return response()->json(['message' => 'Solicitud cancelada'], 200);
        if ($notificacion->respuesta === false)
            return response()->json(['message' => 'El docente ya ha rechazado el proyecto'], 200);
        $notificacion->delete();
        return response()->json(['message' => 'Solicitud cancelada'], 200);
    }

    public function enviarSolicitud($folio)
    {
        $usuarioLogueado = JWTAuth::user();
        $proyecto = Proyecto::Buscar($folio)->firstOrFail();
        if (!$usuarioLogueado->esMiProyecto($proyecto))
            return response()->json(['message' => 'No puedes enviar la solicitud del proyecto ya que no perteneces a él'], 400);
        if ($proyecto->enviado)
            return response()->json(['message' => 'El proyecto ya fue notificado al asesor'], 400);
        if ($proyecto->aceptado)
            return response()->json(['message' => 'El proyecto ya fue aceptado'], 400);
        if ($proyecto->outTime())
            return response()->json(['message' => 'No puedes enviar la solicitud. Estas fuera de tiempo para registrar tu proyecto'], 400);
        if (!$proyecto->todosAceptaron())
            return response()->json(['message' => 'No todos los integrantes han aceptado el proyecto'], 400);
        $proyecto->enviado = true;

        $solicitud = TipoDeSolicitud::where('nombre_', 'Registro de proyecto')->firstOrFail();

        $asesores = $proyecto->notificaciones()->ReceptorConRol('Docente')->where('tipo_de_solicitud_id', $solicitud->id)->get()->pluck('receptor')->toArray();
        if (in_array($proyecto->asesor, $asesores))
            return response()->json(['message' => 'El asesor que has elegido ya ha rechazado tu solicitud. Elige a otro asesor o espera a que acepte tu solicitud'], 400);

        $proyecto->save();

        $solicitudProyecto = $proyecto->notificaciones()->where('tipo_de_solicitud_id', $solicitud->id)->first();

        $notificacion = new Notificacion();
        $notificacion->folio = $solicitudProyecto->folio;
        $notificacion->emisor_id = $usuarioLogueado->id;
        $notificacion->receptor_id = $proyecto->asesor_id;
        $notificacion->proyecto_id = $proyecto->id;
        $notificacion->tipo_de_solicitud_id = $solicitud->id;
        $notificacion->fecha = Carbon::now()->toDateString();
        $notificacion->save();
        return response()->json(['message' => 'Solicitud enviada'], 200);
    }
    public function agregarIntegrante(Request $request)
    {
        $request->validate([
            'num_control' => 'required|exists:users,num_control'
        ]);
        $usuarioLogueado = JWTAuth::user();
        $proyecto = $usuarioLogueado->getProyectoActual();
        if (is_null(($proyecto)))
            return response()->json(['message' => 'No puedes añadir integrantes. No tienes ningún proyecto en curso'], 400);
        if ($proyecto->aceptado)
            return response()->json(['message' => 'No puedes añadir integrantes. El proyecto ya fue aceptado'], 400);
        if ($proyecto->enviado)
            return response()->json(['message' => 'No puedes añadir integrantes. El proyecto esta en proceso de aceptación por el asesor'], 400);
        $foro = $proyecto->foro;
        if ($proyecto->integrantes()->count() >= $foro->lim_alumnos)
            return response()->json(['message' => 'No puedes agregar más integrantes. Tu equipo esta al limite de lo permitido'], 400);
            
        $integrante = User::Buscar($request->num_control)->first();
        if (!$integrante->validarDatosCompletos())
            return response()->json(['message' => 'Los datos del alumno no están completos'], 400);
        if (!$integrante->hasProject())
            return response()->json(['message' => 'El alumno que desea agregar ya cuenta con un proyecto'], 400);

        $solicitud = TipoDeSolicitud::where('nombre_', 'registro de proyecto')->first();
        $integrantes = $proyecto->notificaciones()->ReceptorConRol('Alumno')->where('tipo_de_solicitud_id', $solicitud->id)->get()->pluck('receptor_id')->toArray();
        if (in_array($request->num_control, $integrantes))
            return response()->json(['message' => 'El integrante que deseas agregar ya ha rechazado tu solicitud'], 400);

        $miSolicitud = $proyecto->notificaciones()->where([
            ['tipo_de_solicitud_id', $solicitud->id],
        ])->first();

        $integrante->proyectos()->attach($proyecto);
        $notificacion = new Notificacion();
        $notificacion->folio = $miSolicitud->folio;
        $notificacion->emisor_id = $usuarioLogueado->id;
        $notificacion->receptor_id = $integrante->id;
        $notificacion->proyecto_id = $proyecto->id;
        $notificacion->tipo_de_solicitud_id = TipoDeSolicitud::where('nombre_', 'REGISTRO DE PROYECTO')->firstOrFail()->id;
        $notificacion->fecha = Carbon::now()->toDateString();
        $notificacion->save();
        return response()->json(['mensaje' => 'Integrante añadido'], 200);
    }
    public function eliminarIntegrante(Request $request)
    {
        $request->validate([
            'num_control' => 'required|exists:users,num_control'
        ]);
        $usuarioLogueado = JWTAuth::user();
        $proyecto = $usuarioLogueado->getProyectoActual();
        if (is_null(($proyecto)))
            return response()->json(['message' => 'No puedes añadir integrantes. No tienes ningún proyecto en curso'], 400);
        $integrante = User::Buscar($request->num_control)->firstOrFail();

        // aqui falla porque si quieren eliminar al que solicito no lo encuentra porque el no tienen niguna notificacion
        // $notificacion = $integrante_eliminar->misNotificaciones()->where('proyecto_id', $proyecto->id)->firstOrFail();
        $notificacion = $integrante->misNotificaciones()->where('proyecto_id', $proyecto->id)->first();
        // if($integrante_eliminar->miSolicitud()->where('proyecto_id',$proyecto->id))

        if (!is_null($notificacion)) {
            if ($notificacion->respuesta)
                return response()->json(['message' => 'No puedes eliminar al integrante cuando ya ha aceptado la solicitud.'], 400);
            $notificacion->delete();
        }
        $integrante->proyectos()->detach($proyecto);
        return response()->json(['mensaje' => 'Integrante eliminado'], 200);
    }

    public function getRegistrarSolicitud(Request $request)
    {
        $usuarioLogueado = JWTAuth::user();
        $proyectoEnCurso = $usuarioLogueado->getProyectoActual();
        if (is_null($proyectoEnCurso))
            return response()->json(['message' => 'No tienes ningun proyecto en curso'], 404);
        if (Carbon::now()->toDateString() <= $proyectoEnCurso->foro->fecha_limite)
            return response()->json(['message' => 'No puedes registrar ninguna solicitud por el momento. La fecha de registro sigue en curso'], 400);
        $solicitudes = TipoDeSolicitud::where('nombre_', '!=', 'REGISTRO DE PROYECTO')->get();
        $docentes = User::UsuariosConRol('Docente')->where('num_control', '!=', $proyectoEnCurso->asesor->num_control)->get();
        return response()->json(['solicitudes' => $solicitudes, 'docentes' => $docentes, 'proyecto' => $proyectoEnCurso], 200);
    }
    public function registrarSolicitud(SolicitudRequest $request)
    {
        $usuarioLogueado = JWTAuth::user();
        $proyectoActual = $usuarioLogueado->getProyectoActual();
        // if (Carbon::now()->toDateString() <= $proyectoActual->foro()->first()->fecha_limite)
        if (Carbon::now()->toDateString() <= $proyectoActual->foro->fecha_limite)
            return response()->json(['message' => 'No puedes registrar ninguna solicitud durante el periodo de registro'], 400);        
        $solicitudesPendientes = $usuarioLogueado->miSolicitud()->where('respuesta', null)->count();
        $notificacionesPendientes = $proyectoActual->notificaciones()->where('respuesta', null)->count();
        if ($solicitudesPendientes > 0 || $notificacionesPendientes > 0)
            return response()->json(['message' => 'No puedes registrar más de una solicitad al mismo tiempo. Debes concluir la que tengas pendiente'], 400);

        if ($request->tipo_solicitud == 'registro de proyecto')
            return response()->json(['message' => 'No puedes hacer un registro de proyecto'], 400);
        $solicitud = TipoDeSolicitud::where('nombre_', $request->tipo_solicitud)->firstOrFail();

        $titulo_nuevo = null;
        $titulo_anterior = null;
        $nuevo_asesor = null;
        $anterior_asesor = null;
        $integrantes = array();
        switch ($solicitud->nombre_) {
            case 'CANCELACION DEL PROYECTO':
                $integrantes = $proyectoActual->first()->integrantes()->where('user_id', '!=', $usuarioLogueado->id)->get()->pluck('id')->toArray();
                break;
            case 'CAMBIO DE TITULO DEL PROYECTO':
                $request->validate(['nuevo_titulo' => 'required']);
                $titulo_anterior = $proyectoActual->titulo;
                $titulo_nuevo = $request->nuevo_titulo;
                $integrantes = $proyectoActual->first()->integrantes()->where('user_id', '!=', $usuarioLogueado->id)->get()->pluck('id')->toArray();
                break;
            case 'DAR DE BAJA A UN INTEGRANTE':
                array_push($integrantes, $proyectoActual->asesor_id);
                break;
            case 'CAMBIO DE ASESOR':
                $request->validate(['nuevo_asesor' => 'required|exists:users,num_control']);
                $integrantes = $proyectoActual->first()->integrantes()->where('user_id', '!=', $usuarioLogueado->id)->get()->pluck('id')->toArray();
                $anterior_asesor = $proyectoActual->asesor->id;
                $nuevo_asesor = User::Buscar($request->nuevo_asesor)->first()->id;
                break;
        }
        $total = Notificacion::all()->unique('folio')->count();
        $folio = 'RS-' . ($total + 1);
        $notificaciones = [];
        foreach ($integrantes as $integrante) {
            $notificaciones[] = [
                'folio' => $folio,
                'emisor_id' => $usuarioLogueado->id,
                'receptor_id' => $integrante,
                'administrador' => 0,
                'proyecto_id' => $proyectoActual->id,
                'tipo_de_solicitud_id' => $solicitud->id,
                'anterior_asesor_id' => $anterior_asesor,
                'nuevo_asesor_id' => $nuevo_asesor,
                'titulo_anterior' => $titulo_anterior,
                'titulo_nuevo' => $titulo_nuevo,
                'motivo' => $request->motivo,
                'fecha' => Carbon::now()->toDateString()
            ];
        }
        Notificacion::insert($notificaciones);

        return response()->json(['message' => 'Solicitud registrada'], 200);
    }
}
