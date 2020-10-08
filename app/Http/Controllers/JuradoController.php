<?php

namespace App\Http\Controllers;

use App\Foro;
use App\User;
use App\Proyecto;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class JuradoController extends Controller
{
    //
    public function jurado(Request $request)
    {
        $juradoQuery = User::query();
        $juradoQuery->select('id', 'num_control', 'prefijo', 'nombre', 'apellidoP', 'apellidoM')->whereHas('jurado_proyecto.foro', function ($query) {
            $query->where('participa', 1)->Activo(true);
        });
        if ($request->filtro === 'Pendientes')
            $juradoQuery->doesntHave('horarios');
        if ($request->filtro === 'Asignados')
            $juradoQuery->has('horarios');
        $foro = Foro::Activo(true)->first();
        if (is_null($foro))
            return response()->json(['message' => 'No hay ningún foro activo.'], 404);
        $posicionET = 0;
        $fechas = $foro->fechas()->get();
        foreach ($fechas as $fecha) {
            $recesos = $fecha->recesos()->select('posicion')->get()->pluck('posicion')->toArray();
            $intervalos = $fecha->horarioIntervalos($foro->duracion, 1, $recesos);
            foreach ($intervalos as $key => $hora) {
                $hora->posicion = $posicionET;
                $hora->selected = false;
                if (in_array($posicionET, $recesos))
                    unset($intervalos[$key]);
                $posicionET++;
            }
            $intervalos = array_values($intervalos);
            $fecha->intervalos = $intervalos;
            $fecha->checked = false;
        }
        $jurado = $juradoQuery->with('horarios:user_id,posicion')->withCount(['horarios', 'jurado_proyecto'])->paginate(7);
        return response()->json(['jurado' => $jurado, 'fechas' => $fechas], 200);
    }
    public function asignarJurado(Request $request, $folio)
    {
        $proyecto = Proyecto::Where('folio', $folio)->first();
        if (is_null($proyecto))
            return response()->json(['message' => 'Proyecto no encontrado'], 404);
        if (!$proyecto->aceptado)
            return response()->json(['message' => 'El proyecto no puede participar porque aún no ha sido aceptado.'], 400);
        $foro = $proyecto->foro;
        if (!$foro->activo)
            return response()->json(['message' => 'El foro no esta en curso para poder asignar jurado'], 400);
        if ($proyecto->jurado()->count() + 1 > $foro->num_maestros)
            return response()->json(['message' => 'Cantidad de docentes excedido'], 400);
        $docente = User::Buscar($request->num_control)->first();
        if (!$docente->hasRole('Docente'))
            return response()->json(['message' => 'El usuario que deseas agregar no tiene el rol indicado para ser maestro de taller'], 400);
        if (is_null($docente))
            return response()->json(['message' => 'Docente no encontrado'], 404);
        $docente->jurado_proyecto()->attach($proyecto);
        return response()->json(['mensaje' => 'Docente agregado al jurado'], 200);
    }

    public function eliminarJurado(Request $request, $folio)
    {
        $jurado = User::Buscar($request->num_control)->first();
        if (is_null($jurado))
            return response()->json(['message' => 'Docente no encontrado'], 404);
        $proyecto = Proyecto::Where('folio', $folio)->first();
        if (is_null($proyecto))
            return response()->json(['message' => 'Proyecto no encontrado'], 404);
        if ($proyecto->asesor == $jurado)
            return response()->json(['message' => 'No se puede quitar al asesor como parte del jurado'], 422);
        $jurado->jurado_proyecto()->detach($proyecto);
        return response()->json(['mensaje' => 'Docente excluido del jurado'], 200);
    }
}
