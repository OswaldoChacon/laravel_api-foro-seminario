<?php

namespace App\Http\Controllers;

use JWTAuth;
use App\User;
use App\Foros;
use App\Roles;
use App\Proyectos;
use Carbon\Carbon;
use App\Notificaciones;
use App\TiposProyectos;
use App\TiposSolicitud;
use Illuminate\Http\Request;
use App\LineasDeInvestigacion;
use Illuminate\Support\Facades\DB;
use App\Http\Requests\SolicitudRequest;
use Illuminate\Database\Eloquent\Builder;
use App\Http\Requests\Proyecto\ProyectoRequest;
use App\Http\Requests\Proyecto\EditarProyectoRequest;

class AlumnoController extends Controller
{
    //
    public function __construct()
    {
        // $this->middleware('jwtAuth');
    }
    public function roles()
    {
        return Roles::all();
    }

    public function foro_actual()
    {
        $usuario = JWTAuth::user();
        if (!$usuario->hasProject())
            return response()->json(['message' => 'No puedes registrar más proyectos'], 400);
        $foro = Foros::select('no_foro', 'nombre', 'periodo', 'anio', 'lim_alumnos', 'fecha_limite')->activo()->first();
        if (is_null($foro))
            return response()->json(['message' => 'No hay ningún foro en curso'], 404);
        $hoy = Carbon::now()->toDateString();
        if ($hoy > $foro->fecha_limite)
            return response()->json(['message' => 'Estas fuera de tiempo para registrar un proyecto'], 400);
        $foro->lim_alumnos -= 1;

        // if ($usuario->proyectoActual()->enviado) {
            $lineas = LineasDeInvestigacion::all();
            $tipos_proyectos = TiposProyectos::all();
            $docentes = User::DatosBasicos()->whereHas('roles', function ($query) {
                $query->where('roles.nombre_', 'Docente');
            })->get();
        // }
        $alumnos = User::DatosBasicos()->whereHas('roles', function ($query) {
            $query->where('roles.nombre_', 'Alumno');
        })->doesntHave('proyectos')->where('num_control', '!=', $usuario->num_control)->get();
        return response()->json(['foro' => $foro, 'lineas' => $lineas, 'tipos' => $tipos_proyectos, 'docentes' => $docentes, 'alumnos' => $alumnos], 200);
    }
    public function lista_alumnos()
    {
        $usuario = JWTAuth::user();
        $foro = Foros::where('acceso', true)->first();
        if (is_null($foro))
            return response()->json(['mensaje' => 'No hay ningun foro activo'], 200);
        $hoy = Carbon::now()->toDateString();
        $miProyecto = $usuario->proyectos()->whereHas('foro', function (Builder $query) {
            $query->where('acceso', true);
        })->first();
        if (is_null($miProyecto))
            return response()->noContent();
        $miEquipo = $miProyecto->integrantes()->select('num_control', 'nombre', 'apellidoP', 'apellidoM')->where('user_id', '!=', $usuario->id)->get();

        // if($hoy > $foro->fecha_limite || $miProyecto->respuestaAsesor())
        if ($hoy > $foro->fecha_limite || $miProyecto->enviado)
            return response()->json($miEquipo, 200);

        $alumnos = User::select('num_control', 'nombre', 'apellidoP', 'apellidoM')->whereHas('roles', function ($query) {
            $query->where('roles.nombre_', 'Alumno');
        })->where('num_control', '!=', $usuario->num_control)->get();

        foreach ($alumnos as $alumno) {
            $alumno->myTeam = $miEquipo->contains('num_control', $alumno->num_control);
        }
        $alumnosOrdenados = $alumnos->sortByDesc('myTeam')->values();
        return response()->json($alumnosOrdenados, 200);
    }
    public function registrar_proyecto(ProyectoRequest $request)
    {
        $foro = Foros::where('acceso', true)->firstOrFail();
        $hoy = Carbon::now()->toDateString();
        if ($hoy > $foro->fecha_limite)
            return response()->json(['message' => 'Estas fuera de tiempo para registrar un proyecto'], 400);
        $user = JWTAuth::user();
        if (!JWTAuth::user()->hasProject())
            return response()->json(['message' => 'Tienes un proyecto en curso'], 400);
        $solicitud = TiposSolicitud::where('nombre_', 'Registro de proyecto')->firstOrFail();
        if (!$foro->acceso)
            return response()->json(['message' => 'Foro no activo'], 400);
        $receptores = array();

        // array_push($receptores, $request->asesor);

        $proyecto = new Proyectos();
        $folio = $proyecto->folio($foro);
        $proyecto->fill($request->all());
        $proyecto->asesor = User::Buscar($request->asesor)->firstOrFail()->id;
        $proyecto->lineadeinvestigacion_id = LineasDeInvestigacion::where('clave', $request->linea)->firstOrFail()->id;
        $proyecto->tipos_proyectos_id = TiposProyectos::where('clave', $request->tipo)->firstOrFail()->id;
        $proyecto->foros_id = $foro->id;
        $proyecto->folio = $folio;
        $proyecto->save();

        JWTAuth::user()->proyectos()->attach($proyecto);

        $notificacion = new Notificaciones();
        $notificacion->emisor = JWTAuth::user()->id;
        $notificacion->receptor = JWTAuth::user()->id;
        $notificacion->proyecto_id = $proyecto->id;
        $notificacion->tipo_solicitud = $solicitud->id;
        $notificacion->fecha = Carbon::now()->toDateString();
        $notificacion->save();
        // for ($i = 0; $i < sizeof($receptores); $i++) {
        //     $usuario = User::Buscar($receptores[$i])->firstOrFail();
        //     $notificacion = new Notificaciones();
        //     $notificacion->emisor = JWTAuth::user()->id;
        //     $notificacion->receptor = $usuario->id;
        //     $notificacion->proyecto_id = $proyecto->id;
        //     $notificacion->tipo_solicitud = $solicitud->id;
        //     $notificacion->fecha = Carbon::now()->toDateString();
        //     $notificacion->save(); 
        // }
        return response()->json(['mensaje' => 'Proyecto registrado'], 200);
    }

    public function actualizar_proyecto(EditarProyectoRequest $request, $folio)
    {
    }

    public function enviar_solicitud($folio)
    {
        $proyecto = Proyectos::Buscar($folio)->firstOrFail();
        if ($proyecto->enviado)
            return response()->json(['message' => 'El proyecto ya fue notificado al asesor'], 400);
        $proyecto->enviado = true;
        $proyecto->save();

        $solicitud = TiposSolicitud::where('nombre_', 'Registro de proyecto')->firstOrFail();

        $notificacion = new Notificaciones();
        $notificacion->emisor = JWTAuth::user()->id;
        $notificacion->receptor = $proyecto->asesor;
        $notificacion->proyecto_id = $proyecto->id;
        $notificacion->tipo_solicitud = $solicitud->id;
        $notificacion->fecha = Carbon::now()->toDateString();
        $notificacion->save();
        return response()->json(['message' => 'Solicitud enviada'], 200);
    }
    public function agregar_integrante(Request $request, $folio)
    {
        $usuario = JWTAuth::user();
        $proyecto = Proyectos::where('folio', $folio)->firstOrFail();
        $foro = $proyecto->foro()->firstOrFail();
        if ($proyecto->integrantes()->count() >= $foro->lim_alumnos)
            return response()->json(['message' => 'No puedes agregar más integrantes al proyecto'], 400);
        $nuevo_integrante = User::Buscar($request->num_control)->firstOrFail();
        if (!$nuevo_integrante->hasProject())
            return response()->json(['message' => 'El alumno que desea agregar ya cuenta con un proyecto'], 400);
        $nuevo_integrante->proyectos()->attach($proyecto);
        $notificacion = new Notificaciones();
        $notificacion->emisor = $usuario->id;
        $notificacion->receptor = $nuevo_integrante->id;
        $notificacion->proyecto_id = $proyecto->id;
        $notificacion->tipo_solicitud = TiposSolicitud::where('nombre_', 'REGISTRO DE PROYECTO')->firstOrFail()->id;
        $notificacion->fecha = Carbon::now()->toDateString();
        $notificacion->save();
        // $asesor = $usuario->miSolicitud()->where([
        //     ['proyecto_id', $proyecto->id],
        //     ['receptor', $proyecto->asesor]
        // ])->firstOrFail();
        // $asesor->respuesta = 0;
        // $asesor->save();
        // $asesor = Notificaciones::where('proyecto_id');
        return response()->json(['mensaje' => 'Integrante añadido'], 200);
    }
    public function eliminar_integrante(Request $request, $folio)
    {
        $usuario = JWTAuth::user();
        $proyecto = Proyectos::where('folio', $folio)->firstOrFail();
        $integrante_eliminar = User::Buscar($request->num_control)->firstOrFail();

        // aqui falla porque si quieren eliminar al que solicito no lo encuentra porque el no tienen niguna notificacion
        // $notificacion = $integrante_eliminar->misNotificaciones()->where('proyecto_id', $proyecto->id)->firstOrFail();
        $notificacion = $integrante_eliminar->misNotificaciones()->where('proyecto_id', $proyecto->id)->first();
        // if($integrante_eliminar->miSolicitud()->where('proyecto_id',$proyecto->id))

        if (!is_null($notificacion)) {
            if ($notificacion->respuesta)
                return response()->json(['message' => 'No puedes eliminar al integrante cuando ya ha aceptado la solicitud.'], 400);
            $notificacion->delete();
        }
        $integrante_eliminar->proyectos()->detach($proyecto);
        // $integrante_eliminar->misNotificaciones()->where('proyecto_id', $proyecto->id)->firstOrFail()->delete();

        // aqui falla porque si quieren poner 0 en la resp del asesor no lo encuentra porque el usuario logueado no tiene solicitud

        // $asesor = $proyecto->notificacion()->where([            
        //     ['receptor', $proyecto->asesor],
        //     ['tipo_solicitud',TiposSolicitud::where('nombre_','REGISTRO DE PROYECTO')->firstOrFail()->id]
        // ])->firstOrFail();


        // $asesor = $usuario->miSolicitud()->where([
        //     ['proyecto_id', $proyecto->id],
        //     ['receptor', $proyecto->asesor]
        // ])->firstOrFail();
        // $asesor->respuesta = 0;
        // $asesor->save();
        return response()->json(['mensaje' => 'Integrante eliminado'], 200);
    }
    public function actualizar_info(Request $request, $user_id)
    {
        // JWTAuth::toUser($request->token);         
        // $userAuth = JWTAuth::user();
        // dd(JWTAuth::parseToken()->authenticate());
        $token = JWTAuth::getToken();
        // dd($token);
        $user = JWTAuth::toUser(JWTAuth::getToken());
        dd($user->id);
        // dd(JWTAuth::user());
        // dd(JWTAuth::toUser($request->token));
        $user = User::findOrFail($user_id);
        // $user->email = $request->email;
        // $user->fill($request->only('email'));
        $user->fill($request->only('prefijo'));
        $this->authorize($user);
        $user->save();
    }

    public function get_registrar_solicitud(Request $request)
    {
        $usuario = JWTAuth::user();
        $proyectoEnCurso = $usuario->proyectoActual();
        if ($proyectoEnCurso->isEmpty())
            return response()->json(['message' => 'No tienes ningun proyecto en curso'], 404);
        $solicitudes = TiposSolicitud::where('nombre_', '!=', 'REGISTRO DE PROYECTO')->get();
        $docentes = User::whereHas('roles', function ($query) {
            // select('num_control')->
            $query->where('nombre_', 'Docente');
        })->get();


        $docentes = $docentes->filter(function ($docente) use ($proyectoEnCurso) {
            return $docente->id !== $proyectoEnCurso->first()->asesor;
        })->values();
        return response()->json(['solicitudes' => $solicitudes, 'docentes' => $docentes, 'proyecto' => $proyectoEnCurso->first()], 200);
    }
    public function registrar_solicitud(SolicitudRequest $request)
    {
        $usuario = JWTAuth::user();
        $proyectoEnCurso = $usuario->proyectoActual();
        $administrador = User::whereHas('roles', function (Builder $query) {
            $query->where('nombre_', 'Administrador');
        })->firstOrFail();

        $solicitudesPendientes = $usuario->miSolicitud()->whereHas('tipo_solicitud', function (Builder $query) use ($request) {
            $query->where('nombre_', $request->tipo_solicitud);
        })->where('respuesta', null)->count();
        if ($solicitudesPendientes > 0)
            return response()->json(['message' => 'Solicitud pendiente'], 400);

        // return response()->json($solicitudesPendientes, 200);

        $integrantes = $proyectoEnCurso->first()->integrantes()->where('user_id', '!=', $usuario->id)->get()->pluck('id')->toArray();

        array_push($integrantes, $proyectoEnCurso->first()->asesor);
        // array_push($integrantes,$administrador->id);

        $solicitud = TiposSolicitud::where('nombre_', $request->tipo_solicitud)->firstOrFail();

        $nuevo_titulo = null;
        $titulo_anterior = null;
        $nuevo_asesor = null;
        $anterior_asesor = null;
        switch ($solicitud->nombre_) {
            case 'CANCELACION DEL PROYECTO':
                break;
            case 'CAMBIO DE TITULO DEL PROYECTO':
                $request->validate(['nuevo_titulo' => 'required']);
                $nuevo_titulo = $request->nuevo_titulo;
                break;
            case 'DAR DE BAJA A UN INTEGRANTE':
                break;
            case 'CAMBIO DE ASESOR':
                $request->validate(['nuevo_asesor' => 'required|exists:users,num_control']);
                $nuevo_asesor = User::Buscar($request->nuevo_asesor)->firstOrFail()->id;
                break;
        }
        $notificaciones = [];
        // return response()->json($notificaciones, 200);
        foreach ($integrantes as $integrante) {
            $notificaciones[] = [
                'emisor' => $usuario->id,
                'receptor' => $integrante,
                'administrador' => 0,
                'proyecto_id' => $proyectoEnCurso->first()->id,
                'tipo_solicitud' => $solicitud->id,
                'nuevo_asesor' => $nuevo_asesor,
                'nuevo_titulo' => $nuevo_titulo,
                'motivo' => $request->motivo,
                'fecha' => Carbon::now()->toDateString()
            ];
        }
        $notificaciones[] = [
            'emisor' => $usuario->id,
            'receptor' => $administrador->id,
            'administrador' => 1,
            'proyecto_id' => $proyectoEnCurso->first()->id,
            'tipo_solicitud' => $solicitud->id,
            'nuevo_asesor' => $nuevo_asesor,
            'nuevo_titulo' => $request->nuevo_titulo,
            'motivo' => $request->motivo,
            'fecha' => Carbon::now()->toDateString()
        ];

        Notificaciones::insert($notificaciones);
        // return response()->json($notificaciones, 200);
        // $notificacion = new Notificaciones();

        // $notificacion->emisor = $usuario->id;
        // $notificacion->receptor = $proyectoEnCurso->first()->asesor;
        // $notificacion->proyecto_id = $proyectoEnCurso->first()->id;
        // $notificacion->tipo_solicitud = $solicitud->id;
        // $notificacion->motivo = $request->motivo;
        // $notificacion->save();
        return response()->json(['message' => 'Solicitud registrada'], 200);
    }
}
