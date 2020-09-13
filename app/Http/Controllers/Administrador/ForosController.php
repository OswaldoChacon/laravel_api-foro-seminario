<?php

namespace App\Http\Controllers\Administrador;

use JWTAuth;
use App\User;

use App\Foros;
use App\Proyectos;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Database\Eloquent\Builder;
use App\Http\Requests\Foro\ConfigForoRequest;
use App\Http\Requests\Foro\EditarForoRequest;
use App\Http\Requests\Foro\RegistroForoRequest;

class ForosController extends Controller
{
    //
    public function __construct()
    {
    }
    public function index(Request $request)
    {
        $forosTable = Foros::query();
        $forosTable->select('acceso', 'anio', 'periodo', 'no_foro', 'nombre', 'slug');
        if ($request->no_foro)
            $forosTable->where('no_foro', 'like', '%' . $request->no_foro . '%');
        $foros = $forosTable->paginate(7);
        foreach ($foros as $foro) {
            $foro->canActivate = $foro->canActivate();
        }
        return response()->json($foros, 200);
    }
    public function store(RegistroForoRequest $request)
    {
        $usuario = JWTAuth::user();
        $prefijo = str_split($request->anio);
        $prefijo = $prefijo[2] . $prefijo[3];
        if ($request->periodo == "Agosto-Diciembre") {
            $prefijo = $prefijo . "02-";
        } else {
            $prefijo = $prefijo . "01-";
        }
        $foro = new Foros();
        $foro->fill($request->all());
        $foro->slug = "foro-" . $request->no_foro;
        $foro->prefijo = $prefijo;
        $foro->user_id = $usuario->id;
        $foro->save();
        return response()->json(['message' => 'Foro registrado'], 200);
    }
    public function show($slug)
    {
        $foro = Foros::with(['fechas'])->Buscar($slug)->firstOrFail();
        // if (!$foro->acceso)
        //     return response()->json(['message' => 'Foro no activo'], 422);
        $posicionET = 0;
        foreach ($foro->fechas as $fecha) {
            $recesos = $fecha->receso()->select('posicion')->get()->pluck('posicion')->toArray();
            $fecha['intervalos'] = $fecha->horarioIntervalos($foro->duracion, 1, $recesos);
            foreach ($fecha['intervalos'] as $hora) {
                $hora->posicion = $posicionET;
                $hora->break = in_array($posicionET, $recesos);
                $posicionET++;
            }
            $fecha->length = $posicionET;
        }
        if ($foro->acceso) {
            $docentes = User::DatosBasicos()->whereHas('roles', function ($query) {
                $query->where('roles.nombre_', 'Docente');
            })->get();
            foreach ($docentes as $docente) {
                $docente['taller'] = $docente->foros_user()->where('slug', $slug)->count() > 0 ? true : false;
            }
            // $docentes=$docente->orderBy('taller');
            // dd($docentes);
            $foro->docentes = $docentes;
        }
        return response()->json($foro, 200);
    }
    public function update(RegistroForoRequest $request, $slug)
    {
        $foro = Foros::Buscar($slug)->firstOrFail();
        $foro->fill($request->all());
        $foro->slug = "foro-" . $request->no_foro;
        $foro->save();
        return response()->json(['message' => 'Foro actualizado'], 200);
    }
    public function destroy($slug)
    {
        $foro = Foros::Buscar($slug)->with('proyectos')->firstOrFail();
        if ($foro->proyectos->count())
            return response()->json(['message' => 'No se puede eliminar el registro'], 400);
        $foro->delete();
        return response()->json(['message' => 'Foro eliminado'], 200);
    }

    public function configurar_foro(ConfigForoRequest $request, $slug)
    {
        $foro = Foros::Buscar($slug)->firstOrFail();
        if (!$foro->acceso)
            return response()->json(['message' => 'Foro no activo'], 500);
        $foro->lim_alumnos = $request->lim_alumnos;
        $foro->num_aulas = $request->num_aulas;
        $foro->duracion = $request->duracion;
        $foro->num_maestros = $request->num_maestros;
        $foro->save();
        return response()->json(['message' => 'Config. Registrada'], 200);
    }
    public function activar_foro(Request $request, $slug)
    {
        $request->validate(['acceso' => 'required|boolean']);
        $foros = Foros::Where('acceso', true)->get();
        if ($request->acceso && !$foros->isEmpty())
            return response()->json(['message' => 'No se permite tener dos foros activos'], 400);
        if (!$request->acceso) {
            $foro = Foros::Buscar($slug)->firstOrFail();
            $foro->acceso = $request->acceso;
            $foro->save();
            $message = $request->acceso ? 'Foro activado' : 'Foro desactivado';
            return response()->json(['message' => $message], 200);
        }
    }
    public function agregar_maestro(Request $request, $slug)
    {
        $foro = Foros::Where('slug', $slug)->firstOrFail();
        if (!$foro->acceso)
            return response()->json(['message' => 'No puedes agregar maestros a un foro inactivo'], 400);
        $maestro = User::Buscar($request->num_control)->firstOrFail();
        if(!$maestro->hasRole('Docente'))
            return response()->json(['message' => 'El usuario que deseas agregar no tiene el rol necesario'], 400);
        if($request->agregar)
            $maestro->foros_user()->attach($foro);
        if(!$request->agregar)
            $maestro->foros_user()->detach($foro);
        $message = $request->agregar ? 'Maestro agregado':'Maestro eliminado';
        return response()->json(['message' => $message], 200);
    }
    public function proyectos(Request $request, $slug)
    {
        $proyectosTable = Proyectos::query();
        $aceptado = $request->filtro === 'Aceptados' ? true : false;
        $foro = Foros::Buscar($slug)->firstOrFail();
        if ($request->folio)
            $proyectosTable->where('folio', 'like', '%' . $request->folio . '%');
        $proyectos = $proyectosTable->with(['jurado' => function ($query) {
            $query->select('num_control');
        }])->where('aceptado', $aceptado)->paginate(7);

        $docentes = User::select('num_control', DB::raw("CONCAT(prefijo,' ',nombre,' ',apellidoP,' ',apellidoM) AS nombre"))->whereHas('roles', function ($query) {
            $query->where('roles.nombre_', 'Docente');
        })->get();

        foreach ($docentes as $docente) {
            $docente['jurado'] = false;
        }
        return response()->json(['proyectos' => $proyectos, 'docentes' => $docentes], 200);
    }
}
