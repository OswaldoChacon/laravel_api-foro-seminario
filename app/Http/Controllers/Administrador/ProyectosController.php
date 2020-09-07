<?php

namespace App\Http\Controllers\Administrador;

use App\User;
use App\Foros;
use App\Proyectos;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;

class ProyectosController extends Controller
{
    //
    public function proyectos_foro(Request $request, $slug)
    {
        // $proyectosTable = Proyectos::query();
        // $aceptado = $request->filtro === 'Aceptados' ? true : false;
        // $foro = Foros::Buscar($slug)->firstOrFail();
        // if ($request->folio)
        //     $proyectosTable->where('folio', 'like', '%' . $request->folio . '%');
        // $proyectos = $proyectosTable->with(['jurado' => function ($query) {
        //     $query->select('num_control');
        // }])->where('aceptado', $aceptado)->paginate(7);

        // $docentes = User::select('num_control', DB::raw("CONCAT(prefijo,' ',nombre,' ',apellidoP,' ',apellidoM) AS nombre"))->whereHas('roles', function ($query) {
        //     $query->where('roles.nombre_', 'Docente');
        // })->get();

        // foreach ($docentes as $docente) {
        //     $docente['jurado'] = false;
        // }
        // return response()->json(['proyectos' => $proyectos, 'docentes' => $docentes], 200);
    }   

    public function proyecto_participa(Request $request, $folio)
    {
        $proyecto = Proyectos::Where('folio', $folio)->firstOrFail();
        $foro = $proyecto->foro()->first();
        if (!$foro->acceso)
            return response()->json(['message' => 'El foro no esta en curso para poder actualizar el proyecto'], 422);
        $proyecto->participa = $request->participa;
        $proyecto->save();
        return response()->json(['mensaje' => 'Proyecto actualizado'], 200);
    }

    public function asignar_jurado(Request $request, $folio)
    {
        // pendiente validacion        
        $proyecto = Proyectos::Where('folio', $folio)->firstOrFail();
        $foro = $proyecto->foro()->first();
        if (!$foro->acceso)
            return response()->json(['message' => 'El foro no esta en curso para poder asignar jurado'], 422);
        if ($proyecto->jurado()->count() + 1 > $foro->num_maestros)
            return response()->json(['message' => 'Cantidad de maestros excedido'], 422);
        $jurado = User::Buscar($request->num_control)->firstOrFail();
        $jurado->jurado_proyecto()->attach($proyecto);
        return response()->json(['mensaje' => 'Maestro agregado al jurado'], 200);
    }

    public function eliminar_jurado(Request $request, $folio)
    {
        $jurado = User::Buscar($request->num_control)->firstOrFail();
        $proyecto = Proyectos::Where('folio', $folio)->firstOrFail();
        if ($proyecto->asesor == $jurado->id)
            return response()->json(['message' => 'No se puede quitar al asesor como parte del jurado'], 422);
        $jurado->jurado_proyecto()->detach($proyecto);
        return response()->json(['mensaje' => 'Maestro excluido del jurado'], 200);
    }
}
