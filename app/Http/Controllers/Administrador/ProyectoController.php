<?php

namespace App\Http\Controllers\Administrador;

use App\User;
use App\Foro;
use App\Proyecto;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class ProyectoController extends Controller
{
    public function proyecto_participa(Request $request, $folio)
    {
        $proyecto = Proyecto::Where('folio', $folio)->firstOrFail();
        if (!$proyecto->aceptado)
            return response()->json(['message' => 'El proyecto no puede participar porque aún no ha sido aceptado.'], 400);
        $foro = $proyecto->foro()->first();
        if (!$foro->activo)
            return response()->json(['message' => 'El foro no esta en curso para poder actualizar el proyecto'], 422);
        $proyecto->participa = $request->participa;
        $proyecto->save();
        return response()->json(['mensaje' => 'Proyecto actualizado'], 200);
    }

    public function permitir_cambios(Request $request, $folio)
    {
        $request->validate([
            'cambios' => 'required|boolean'
        ]);
        $proyecto = Proyecto::Buscar($folio)->firstOrFail();
        $foro = $proyecto->foro()->first();
        $hoy = Carbon::now()->toDateString();
        if ($hoy > $foro->fecha_limite)
            return response()->json(['message' => 'No se puede editar el proyecto, fuera de tiempo'], 400);
        $proyecto->permitir_cambios = $request->cambios;
        $proyecto->save();
        $message = $request->cambios ? 'Proyecto habilitado ha cambios' : 'Proyecto deshabilitado ha cambios';
        return response()->json(['message' => $message], 200);
    }

    public function asignar_jurado(Request $request, $folio)
    {        
        $proyecto = Proyecto::Where('folio', $folio)->first();
        if (is_null($proyecto))
            return response()->json(['message' => 'Proyecto no encontrado'], 404);
        if (!$proyecto->aceptado)
            return response()->json(['message' => 'El proyecto no puede participar porque aún no ha sido aceptado.'], 400);
        $foro = $proyecto->foro()->first();
        if (!$foro->activo)
            return response()->json(['message' => 'El foro no esta en curso para poder asignar jurado'], 400);
        if ($proyecto->jurado()->count() + 1 > $foro->num_maestros)
            return response()->json(['message' => 'Cantidad de docentes excedido'], 400);
        $docente = User::Buscar($request->num_control)->first();
        if (is_null($docente))
            return response()->json(['message' => 'Docente no encontrado'], 404);
        $docente->jurado_proyecto()->attach($proyecto);
        return response()->json(['mensaje' => 'Docente agregado al jurado'], 200);
    }

    public function eliminar_jurado(Request $request, $folio)
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
