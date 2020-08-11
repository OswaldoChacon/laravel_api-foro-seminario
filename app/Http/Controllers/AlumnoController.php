<?php

namespace App\Http\Controllers;

use JWTAuth;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Builder;
use App\Http\Requests\ProyectoRequest;
use Illuminate\Http\Request;
use App\Proyectos;
use App\Foros;
use App\LineasDeInvestigacion;
use App\Notificaciones;
use App\TiposProyectos;
use App\User;

class AlumnoController extends Controller
{
    //
    public function __construct()
    {
        // $this->middleware('jwtAuth');
    }

    public function foro_actual()
    {
        if (!JWTAuth::user()->hasProject())
            return response()->json(['message' => 'Tienes un proyecto en curso'], 400);
        $foro = Foros::select('no_foro', 'nombre', 'periodo', 'anio', 'lim_alumnos')->where('acceso', true)->first();
        $foro->lim_alumnos -= 1;
        $lineas = LineasDeInvestigacion::all();
        $tipos_proyectos = TiposProyectos::all();
        $docentes = User::select('num_control', DB::raw("CONCAT(prefijo,' ',nombre,' ',apellidoP,' ',apellidoM) AS nombre"))->whereHas('roles', function ($query) {
            $query->where('roles.nombre', 'Docente');
        })->get();
        $alumnos = User::select('num_control', DB::raw("CONCAT(nombre,' ',apellidoP,' ',apellidoM) AS nombre"))->whereHas('roles', function ($query) {
            $query->where('roles.nombre', 'Alumno');
        })->doesntHave('proyectos')->where('num_control', '!=', JWTAuth::user()->num_control)->get();
        return response()->json(['foro' => $foro, 'lineas' => $lineas, 'tipos' => $tipos_proyectos, 'docentes' => $docentes, 'alumnos' => $alumnos], 200);
    }
    public function lista_alumnos()
    {
        $usuario = User::where('num_control', 'prueba')->firstOrFail();
        $miProyecto = $usuario->proyectos()->whereHas('foro', function (Builder $query) {
            $query->where('acceso', true);
        })->firstOrFail();
        $miEquipo = $miProyecto->integrantes()->where('user_id', '!=', $usuario->id)->get();
        // return response()->json($miEquipo, 200);
        // $alumnos = User::select('num_control', DB::raw("CONCAT(nombre,' ',apellidoP) AS nombre"))->whereHas('roles', function ($query) {
        //     $query->where('roles.nombre', 'Alumno');
        // })->doesntHave('proyectos')->where('num_control', '!=', $usuario->num_control)->get();
        $alumnos = User::select('num_control', DB::raw("CONCAT(nombre,' ',apellidoP) AS nombre"))->whereHas('roles', function ($query) {
            $query->where('roles.nombre', 'Alumno');
        })->where('num_control', '!=', $usuario->num_control)->get();
        foreach ($alumnos as $alumno) {            
            $alumno->myTeam = $miEquipo->contains('num_control',$alumno->num_control);
        }
        $alumnosOrdenados = $alumnos->sortByDesc('myTeam')->values();        
        $alumnosOrdenados->all();
        // })->doesntHave('proyectos')->where('num_control','!=',JWTAuth::user()->num_control)->get();
        return response()->json($alumnosOrdenados, 200);
    }
    public function registrar_proyecto(ProyectoRequest $request)
    {
        $user = JWTAuth::user();
        if (!JWTAuth::user()->hasProject())
            return response()->json(['message' => 'Tienes un proyecto en curso'], 400);
        $foro = Foros::where('acceso', true)->firstOrFail();
        if (!$foro->acceso)
            return response()->json(['message' => 'Foro no activo'], 400);
        $receptores = array();

        array_push($receptores, $request->asesor);
        for ($i = 0; $i < sizeof($request->alumnos); $i++) {
            if ($request->alumnos[$i] != null) {
                if (!in_array($request->alumnos[$i], $receptores)) {
                    if (!User::where('num_control', $request->alumnos[$i])->firstOrFail()->hasProject())
                        return response()->json(['message' => 'Uno de tus integrantes ya cuenta con un proyecto registrado'], 422);
                    array_push($receptores, $request->alumnos[$i]);
                } else {
                    return response()->json(['message' => 'Has elegido al mismo integrante en más de una ocasión.'], 422);
                }
            }
        }
        $proyecto = new Proyectos();
        $folio = $proyecto->folio($foro);
        $proyecto->fill($request->all());
        $proyecto->asesor = User::where('num_control', $request->asesor)->firstOrFail()->id;
        $proyecto->lineadeinvestigacion_id = LineasDeInvestigacion::where('clave', $request->linea)->firstOrFail()->id;
        $proyecto->tipos_proyectos_id = TiposProyectos::where('clave', $request->tipo)->firstOrFail()->id;
        $proyecto->foros_id = $foro->id;
        $proyecto->folio = $folio;
        $proyecto->save();

        JWTAuth::user()->proyectos()->attach($proyecto);
        for ($i = 0; $i < sizeof($receptores); $i++) {
            $usuario = User::where('num_control', $receptores[$i])->firstOrFail();
            $notificacion = new Notificaciones();
            $notificacion->emisor = JWTAuth::user()->id;
            $notificacion->receptor = $usuario->id;
            $notificacion->proyecto_id = $proyecto->id;
            $notificacion->save();
            if ($usuario->id != $proyecto->asesor)
                $usuario->proyectos()->attach($proyecto);
        }
        return response()->json(['mensaje' => 'Proyecto registrado'], 200);
    }

    public function agregar_integrante(Request $request, $folio)
    {
        $usuarioLogueado = User::where('num_control','prueba')->firstOrFail();
        $proyecto = Proyectos::where('folio', $folio)->firstOrFail();
        $foro = $proyecto->foro()->firstOrFail();
        if ($proyecto->integrantes()->count() >= $foro->lim_alumnos)
        return response()->json(['message' => 'No puedes agregar más integrantes al proyecto'], 400);
        $nuevo_integrante = User::where('num_control', $request->num_control)->firstOrFail();        
        $nuevo_integrante->proyectos()->attach($proyecto);
        $notificacion = new Notificaciones();
        $notificacion->emisor = $usuarioLogueado->id;
        $notificacion->receptor = $nuevo_integrante->id;
        $notificacion->proyecto_id = $proyecto->id;
        $notificacion->save();        
        $asesor = $usuarioLogueado->miSolicitud()->where([
            ['proyecto_id',$proyecto->id],
            ['receptor',$proyecto->asesor]
        ])->firstOrFail();
        $asesor->respuesta = 0;
        $asesor->save();
        // $asesor = Notificaciones::where('proyecto_id');
        return response()->json(['mensaje' => 'Integrante añadido'], 200);
    }
    public function eliminar_integrante(Request $request, $folio)
    {
        $proyecto = Proyectos::where('folio', $folio)->firstOrFail();
        $usuario = User::where('num_control','prueba')->firstOrFail();
        $integrante_eliminar = User::where('num_control', $request->num_control)->firstOrFail();        
        $integrante_eliminar->proyectos()->detach($proyecto);
        $integrante_eliminar->misNotificaciones()->where('proyecto_id',$proyecto->id)->firstOrFail()->delete();
        $asesor = $usuario->miSolicitud()->where([
            ['proyecto_id',$proyecto->id],
            ['receptor',$proyecto->asesor]
        ])->firstOrFail();
        $asesor->respuesta = 0;
        $asesor->save();
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
}
