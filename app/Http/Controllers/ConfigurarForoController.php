<?php

namespace App\Http\Controllers;

use App\Foro;
use App\User;
use App\Horario;
use App\Proyecto;
use Illuminate\Http\Request;
use App\Http\Requests\Foro\ConfigForoRequest;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class ConfigurarForoController extends Controller
{
    // public function configurarForo(ConfigForoRequest $request, $slug)
    public function configurarForo(ConfigForoRequest $request, Foro $foro)
    {
        // politicas
        try {
            DB::beginTransaction();
            // if (!$foro->activo)
            //     return response()->json(['message' => 'Foro inactivo'], 400);
            // if (!$foro->inTime())
            //     return response()->json(['message' => 'Foro fuera de tiempo'], 400);
            $foro->lim_alumnos = $request->lim_alumnos;
            $foro->num_aulas = $request->num_aulas;
            $foro->duracion = $request->duracion;
            $foro->num_maestros = $request->num_maestros;
            $foro->save();
            DB::commit();
            return response()->json(['message' => 'Config. Registrada'], 200);
        } catch (\Exception $exception) {
            DB::rollBack();
            return response()->json(['message' => 'Algo mal ha ocurrido'], 200);
        }
    }
    public function agregarMaestro(Request $request, $slug)
    {
        $foro = Foro::Where('slug', $slug)->first();
        if (is_null($foro))
            return response()->json(['message' => 'Foro no encontrado'], 404);
        // if (!$foro->activo)
        //     return response()->json(['message' => 'No puedes agregar maestros a un foro inactivo'], 400);
        // if (!$foro->inTime())
        //     return response()->json(['message' => 'Foro fuera de tiempo'], 400);
        $maestro = User::Buscar($request->num_control)->first();
        if (is_null($maestro))
            return response()->json(['message' => 'Maestro no encontrado'], 404);
        if (!$maestro->hasRole('Docente'))
            return response()->json(['message' => 'El usuario que deseas agregar no tiene el rol indicado para ser maestro de taller'], 400);
        if ($request->agregar)
            $maestro->foros_users()->attach($foro);
        if (!$request->agregar)
            $maestro->foros_users()->detach($foro);
        $message = $request->agregar ? 'Maestro agregado' : 'Maestro eliminado';
        return response()->json(['message' => $message], 200);
    }
    public function activarForo(Request $request, $slug)
    {
        $request->validate(['activo' => 'required|boolean']);
        $foro = Foro::Buscar($slug)->first();
        if (is_null($foro))
            return response()->json(['message' => 'Foro no encontrado'], 404);
        $foros = Foro::Where([
            ['activo', true],
            ['slug', '!=', $slug]
        ])->get();
        if ($request->activo && !$foros->isEmpty())
            return response()->json(['message' => 'No se permite tener dos foros activos'], 400);
        // if ($request->activo) {
        //     if (!$foro->canActivate())
        //         return response()->json(['message' => 'No puedes activar/desactivar un foro fuera de tiempo'], 400);
        // }
        $foro->activo = $request->activo;
        $foro->save();
        $foroReceso = Horario::first();
        if (!is_null($foroReceso)) {
            $foroReceso = $foroReceso->fechaforo()->first()->foro;
            if ($foro->no_foro !== $foroReceso->no_foro)
                Horario::truncate();
        }
        $message = $request->activo ? 'Foro activado' : 'Foro desactivado';
        return response()->json(['message' => $message], 200);
    }
    public function proyectos(Request $request, $slug)
    {
        $proyectosTable = Proyecto::query()->select('id', 'aceptado', 'folio', 'participa', 'titulo');
        $aceptado = $request->filtro === 'Aceptados' ? true : false;
        $foro = Foro::Buscar($slug)->first();
        if (is_null($foro))
            return response()->json(['message' => 'Foro no encontrado'], 404);
        if ($request->folio)
            $proyectosTable->where('folio', 'like', '%' . $request->folio . '%');
        if ($request->jurado === 'Asignados')
            $proyectosTable->has('jurado', $foro->num_maestros);
        else if ($request->jurado === 'Pendientes')
            $proyectosTable->has('jurado', '<', $foro->num_maestros);
        $proyectos = $proyectosTable->with(['jurado' => function ($query) {
            $query->select('num_control');
        }])->foro($slug)->where('aceptado', $aceptado)->get();
        foreach ($proyectos as $proyecto) {
            $proyecto->append('EspaciosDeTiempoEnComun');
        }
        $proyectos = collect($proyectos->sortBy('EspaciosDeTiempoEnComun'));
        $docentes = User::DatosBasicos()->UsuariosConRol('Docente')->get();
        foreach ($docentes as $docente) {
            $docente['jurado'] = false;
        }
        $page = $request->page;
        $perPage = 7;
        $proyectos = new LengthAwarePaginator(
            $proyectos->forPage($page, $perPage)->values()->all(),
            $proyectos->count(),
            $perPage,
            $page,
            ['path' => url('api/proyectos_maestros')]
        );
        return response()->json(['proyectos' => $proyectos, 'docentes' => $docentes], 200);
    }
}
