<?php

namespace App\Http\Controllers;

use JWTAuth;
use App\User;
use App\Foro;
use App\Proyecto;
use Carbon\Carbon;
use App\Notificacion;
use App\TipoDeSolicitud;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class UsuariosController extends Controller
{   
    public function misForos(Request $request)
    {
        $usuarioLogueado = JWTAuth::user();
        if ($request->rol === 'Alumno')
            $misForos = $usuarioLogueado->proyectos()->with('foro')->get()->pluck('foro');
        if ($request->rol === 'Docente')
            $misForos = $usuarioLogueado->asesor()->with('foro')->get()->pluck('foro');
        if ($request->rol === 'Administrador')
            $misForos = Foro::where('user_id', $usuarioLogueado->id)->get();
        $misForos = $misForos->map(function ($foro) {
            if (!$foro->activo)
                return 'Foro ' . $foro->no_foro;
        });
        return response()->json($misForos, 200);
    }
    public function misNotificaciones(Request $request)
    {
        $respuesta = $request->respuesta == 'Aceptados' ? true : ($request->respuesta == 'Rechazados' ? false : null);
        $notificacionesQuery = Notificacion::query();
        $usuarioLogueado = JWTAuth::user();
        $notificacionesQuery = $usuarioLogueado->misNotificaciones();
        $foro = Foro::Activo(true)->first();
        if ($request->no_foro === 'Foro en curso') {
            if (is_null($foro))
                return response()->json(['data' => []], 200);
            $notificacionesQuery->whereHas('proyecto.foro', function (Builder $query) {
                $query->Activo(true); //where('activo', true);
            });
        } else {
            $notificacionesQuery->whereHas('proyecto.foro', function (Builder $query) use ($request) {
                $query->where('no_foro', $request->no_foro);
            });
        }
        if (is_null($foro))
            return response()->json(['mensaje' => 'No hay ningun foro activo'], 200);
        $notificacionesQuery->with(['proyecto', 'tipo_de_solicitud', 'nuevo_asesor', 'anterior_asesor'])->where('respuesta', $respuesta);
        if ($request->rol === 'Administrador') {
            $notificacionesQuery->where('administrador', 1);
        } else if ($request->rol === 'Docente' || $request->rol === 'Alumno') {
            $notificacionesQuery->where('administrador', 0);
        }
        $misNotificaciones['data'] = $notificacionesQuery->get();

        $misNotificaciones['data'] = $misNotificaciones['data']->groupBy('solicitud');
        $misNotificaciones['total'] = $usuarioLogueado->misNotificaciones()->where('respuesta', null)->whereHas('proyecto.foro', function(Builder $query){
            $query->Activo(true);
        })->count();
        return response()->json($misNotificaciones, 200);
    }  
    public function responderNotificacion(Request $request, $folio)
    {
        $request->validate([
            'solicitud' => 'required|exists:tipos_de_solicitud,nombre_',
            'respuesta' => 'required|boolean',
            'rol' => 'required|exists:roles,nombre_',
            'folio' => 'required|exists:notificaciones,folio'
        ]);
        $usuarioLogueado = JWTAuth::user();
        $proyecto = Proyecto::where('folio', $folio)->first();
        if (is_null($proyecto))
            return response()->json(['message' => 'Proyecto no encontrado'], 404);
        if ($usuarioLogueado->hasRole('Alumno')) {
            if (!$usuarioLogueado->esMiProyecto($proyecto)) {
                return response()->json(['message' => 'No puedes responder esta notificacion ya que no perteneces al proyecto'], 400);
            }
        }

        if (!$proyecto->foro->activo)
            return response()->json(['message' => 'No puedes responder notificaciones de foros inactivos'], 400);
        $solicitud = TipoDeSolicitud::where('nombre_', $request->solicitud)->first();
        $rol = $request->rol === 'Administrador' ? true : false;
        $notificacion = $usuarioLogueado->misNotificaciones()->where([
            ['folio', $request->folio],
            ['proyecto_id', $proyecto->id],
            ['receptor_id', $usuarioLogueado->id],
            ['tipo_de_solicitud_id', $solicitud->id],
            ['administrador', $rol]
        ])->first();
        if (is_null($notificacion))
            return response()->json(['message' => 'Notificación no encontrada'], 404);
        $notificacion->respuesta = $request->respuesta;
        $notificacion->comentarios = $request->comentarios;
        $notificacion->save();

        if ($solicitud->nombre_ !== 'REGISTRO DE PROYECTO') {
            $integrantes = $proyecto->integrantes()->count() - 1;
            if ($integrantes === $proyecto->notificaciones()->ReceptorConRol('Alumno')->where([['respuesta', true], ['tipo_de_solicitud_id', $solicitud->id]])->count() && $usuarioLogueado->hasRole('Alumno')) {
                $this->agregarNotifcacion($notificacion, $proyecto->asesor->id, 0);
            }
        }

        if ($usuarioLogueado->hasRole('Docente') && $request->rol === 'Docente') {
            if ($notificacion->respuesta === false) {
                if ($solicitud->nombre_ === 'REGISTRO DE PROYECTO') {
                    $proyecto->enviado = 0;
                    $proyecto->aceptado = 0;
                    $proyecto->permitir_cambios = 0;
                    $proyecto->asesor()->dissociate();
                    $usuarioLogueado->jurado_proyecto()->detach($proyecto);
                }
            } else if ($notificacion->respuesta === true) {
                $admin = User::UsuariosConRol('Administrador')->first();
                if (is_null($admin))
                    return response()->json(['message' => 'Administrador no encontrado'], 404);
                if ($solicitud->nombre_ === 'REGISTRO DE PROYECTO') {
                    $notificacionesPendientes = $proyecto->notificaciones()->ReceptorConRol('Docente')->where([['respuesta', null], ['tipo_de_solicitud_id', $solicitud->id], ['receptor_id', '!=', $usuarioLogueado->id]])->count();
                    if ($notificacionesPendientes > 0)
                        return response()->json(['message' => 'No es posible aceptar el proyecto. Ya han notificado a otro maestro'], 400);
                    $proyecto->enviado = 0;
                    $proyecto->aceptado = 1;                    
                    $proyecto->asesor()->associate($usuarioLogueado);
                    $usuarioLogueado->jurado_proyecto()->detach($proyecto);
                    $usuarioLogueado->jurado_proyecto()->attach($proyecto);
                } else if ($solicitud->nombre_ === 'CAMBIO DE ASESOR') {
                    if ($usuarioLogueado->id === $proyecto->asesor_id)
                        $this->agregarNotifcacion($notificacion, $notificacion->nuevo_asesor_id, 0);
                    else if ($usuarioLogueado->id === $notificacion->nuevo_asesor_id)
                        $this->agregarNotifcacion($notificacion, $admin->id, 1);
                } else {
                    $this->agregarNotifcacion($notificacion, $admin->id, 1);
                }
            }
        } else if ($usuarioLogueado->hasRole('Administrador') && $request->rol === 'Administrador') {
            if ($notificacion->respuesta === true) {
                $this->realizarCambios($solicitud->nombre_, $notificacion, $proyecto);
            }
        }

        $proyecto->save();
        $message = $request->respuesta ? 'Solicitud aceptada' : 'Solicitud rechazada';
        return response()->json(['message' => $message], 200);
    }
    public function misProyectos(Request $request)
    {
        $usuarioLogueado = JWTAuth::user();
        $proyectos = new Collection();
        if ($usuarioLogueado->hasRole('Alumno')) {
            $proyectos = $usuarioLogueado->proyectos()->with(['asesor', 'linea_de_investigacion', 'tipo_de_proyecto'])->get();
        } else if ($usuarioLogueado->hasRole('Docente')) {
            $proyectos = $usuarioLogueado->asesor()->where('aceptado',true)->with(['integrantes', 'linea_de_investigacion', 'tipo_de_proyecto'])->get();
            foreach ($proyectos as $proyecto) {
                $proyecto->append('inTime');
                // $proyecto->inTime = $proyecto->inTime();
            }
        }
        return response()->json($proyectos, 200);
    }
    public function agregarNotifcacion(Notificacion $notificacion, int $id, int $admin)
    {
        $notificaciones[] = [
            'folio' => $notificacion->folio,
            'emisor_id' => $notificacion->emisor_id,
            'receptor_id' => $id,
            'administrador' => $admin,
            'proyecto_id' => $notificacion->proyecto_id,
            'tipo_de_solicitud_id' => $notificacion->tipo_de_solicitud_id,
            'anterior_asesor_id' => $notificacion->anterior_asesor_id,
            'nuevo_asesor_id' => $notificacion->nuevo_asesor_id,
            'titulo_nuevo' => $notificacion->titulo_nuevo,
            'titulo_anterior' => $notificacion->titulo_anterior,
            'motivo' => $notificacion->motivo,
            'fecha' => Carbon::now()->toDateString()
        ];
        Notificacion::insert($notificaciones);
    }
    public function realizarCambios($solicitud, Notificacion $notificacion, Proyecto $proyecto)
    {
        switch ($solicitud) {
            case 'CAMBIO DE TITULO DEL PROYECTO':
                $proyecto->titulo = $notificacion->titulo_nuevo;
                break;
            case 'CAMBIO DE ASESOR':
                $anteriorAsesor = User::find($notificacion->anterior_asesor_id);
                $nuevoAsesor = User::find($notificacion->nuevo_asesor_id);                
                $proyecto->asesor()->associate($nuevoAsesor);
                $anteriorAsesor->jurado_proyecto()->detach($proyecto);
                $nuevoAsesor->jurado_proyecto()->attach($proyecto);
                break;
            case 'CANCELACION DE PROYECTO':
                $proyecto->participa = 0;
                $proyecto->cancelado = 1;
                break;
            case 'DAR DE BAJA A UN INTEGRANTE':
                $integrante = User::find($notificacion->emisor_id);
                $integrante->proyectos()->detach($proyecto);
                break;
        }
    }
}
