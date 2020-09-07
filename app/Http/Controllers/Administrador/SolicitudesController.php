<?php

namespace App\Http\Controllers\Administrador;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use App\TiposSolicitud;

class SolicitudesController extends Controller
{
    //
    public function __construct()
    {
    }
    public function index()
    {
        $solicitudes = TiposSolicitud::all();
        return response()->json($solicitudes, 200);
    }
    public function store(Request $request)
    {
        $solicitud = new TiposSolicitud();
        $solicitud->fill($request->all());
        $solicitud->save();        
        return response()->json(['message'=>'Registro agregado'], 200);
    }
    public function show()
    {
    }
    public function update(Request $request, $solicitud)
    {
        $solicitud = TiposSolicitud::where('nombre_', $solicitud)->firstOrFail();
        $solicitud->nombre_ = $request->nombre_;
        $solicitud->save();
        return response()->json(['message'=>'Registro actualizado'], 200);
    }
    public function destroy($solicitud)
    {
        $solicitud = TiposSolicitud::where('nombre_', $solicitud)->firstOrFail();
        if($solicitud->proyectos->count())
            return response()->json(['message' => 'No se puede eliminar el registro'], 400);
        $solicitud->delete();        
        return response()->json(['message'=>'Registro eliminado'], 200);
    }
}
