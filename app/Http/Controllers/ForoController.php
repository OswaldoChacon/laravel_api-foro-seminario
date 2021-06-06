<?php

namespace App\Http\Controllers;

use JWTAuth;
use App\Foro;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\Foro\ForoRequest;
use Illuminate\Support\Facades\DB;

class ForoController extends Controller
{
    //  
    public function index(Request $request)
    {
        $forosTable = Foro::query();
        $forosTable->select('activo', 'anio', 'periodo', 'no_foro', 'nombre', 'slug', 'fecha_limite');
        if ($request->no_foro)
            $forosTable->where('no_foro', 'like', '%' . $request->no_foro . '%');
        $foros = $forosTable->paginate(7);
        foreach ($foros as $foro) {
            $foro->canActivate = $foro->canActivate();
        }
        return response()->json($foros, 200);
    }
    public function store(ForoRequest $request, Foro $foro)
    {
        try {
            DB::beginTransaction();
            $usuarioLogueado = JWTAuth::user();
            $foro->fill($request->all());
            $foro->slug = "foro-" . $request->no_foro;
            $foro->prefijo = $foro->getPrefijo($request->anio, $request->periodo);
            $foro->user()->associate($usuarioLogueado);
            $foro->save();
            DB::commit();
            return response()->json(['message' => 'Foro registrado'], 201);
        } catch (\Exception $e) {
            DB::rollback();
            return response()
                ->json([
                    'message' => 'Algo mal ha ocurrido'
                ], 400);
        }
    }
    public function show(Foro $foro)
    {
        $foro->select('id', 'slug', 'duracion', 'lim_alumnos', 'num_aulas', 'num_maestros', 'activo')->with(['fechas']);
        $posicionET = 0;
        foreach ($foro->fechas as $fecha) {
            $recesos = $fecha->recesos()->select('posicion')->get()->pluck('posicion')->toArray();
            $fecha['intervalos'] = $fecha->horarioIntervalos($foro->duracion, 1, $recesos);
            foreach ($fecha['intervalos'] as $hora) {
                $hora->posicion = $posicionET;
                $hora->break = in_array($posicionET, $recesos);
                $posicionET++;
            }
            $fecha->length = $posicionET;
        }
        $foro->append('docentes');
        return response()->json($foro, 200);
    }
    public function update(ForoRequest $request, Foro $foro)
    {
        try {
            DB::beginTransaction();
            $foro->fill($request->all());
            $foro->prefijo = $foro->getPrefijo($request->anio, $request->periodo);
            $foro->slug = "foro-" . $request->no_foro;
            $foro->save();
            DB::commit();
            return response()->json(['message' => 'Foro actualizado'], 200);
        } catch (\Exception $exception) {
            DB::rollback();
            return response()
                ->json([
                    'message' => 'Algo mal ha ocurrido'
                ], 400);
        }
    }
    public function destroy(Foro $foro)
    {
        // Politica        
        $this->authorize('delete', $foro);
        $foro->delete();
        return response()->json(['message' => 'Foro eliminado'], 200);
    }
    public function foroActual()
    {
        // politicas
        $usuarioLogueado = JWTAuth::user();
        if (!$usuarioLogueado->validarDatosCompletos())
            return response()->json(['message' => 'Debes completar tus datos para registrar un proyecto'], 400);
        if (!$usuarioLogueado->hasProject())
            return response()->json(['message' => 'No puedes registrar más proyectos. Ya tienes uno registrado o aprobado.'], 400);
        $foro = Foro::select('no_foro', 'nombre', 'periodo', 'anio', 'lim_alumnos', 'fecha_limite')->Activo(true)->first();
        if (is_null($foro))
            return response()->json(['message' => 'No hay ningún foro activo'], 404);
        $hoy = Carbon::now()->toDateString();
        if ($hoy > $foro->fecha_limite)
            return response()->json(['message' => 'Estas fuera de tiempo para registrar un proyecto'], 400);
        $foro->lim_alumnos -= 1;
        return response()->json($foro, 200);
    }
}
