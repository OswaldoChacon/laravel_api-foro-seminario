<?php

namespace App\Http\Controllers\Administrador;

use App\Http\Controllers\Controller;
use App\Http\Requests\Solicitud\RegistrarSolicitudRequest;
use App\TipoDeSolicitud;

class SolicitudController extends Controller
{
    //
    public function __construct()
    {
    }
    public function index()
    {
        $solicitudes = TipoDeSolicitud::all();
        return response()->json($solicitudes, 200);
    }
    public function store(RegistrarSolicitudRequest $request)
    {
        $solicitud = new TipoDeSolicitud();
        $solicitud->fill($request->all());
        $solicitud->save();        
        return response()->json(['message'=>'Registro agregado'], 200);
    }
    public function show()
    {
    }
    public function update(RegistrarSolicitudRequest $request, TipoDeSolicitud $solicitude)
    {
        $solicitud = TipoDeSolicitud::where('nombre_', $solicitude->nombre_)->first();
        $solicitud->nombre_ = $request->nombre_;
        $solicitud->save();
        return response()->json(['message'=>'Registro actualizado'], 200);
    }
    public function destroy($solicitud)
    {
        $solicitud = TipoDeSolicitud::where('nombre_', $solicitud)->firstOrFail();
        if($solicitud->notificaciones->count())
            return response()->json(['message' => 'No se puede eliminar el registro'], 400);
        $solicitud->delete();        
        return response()->json(['message'=>'Registro eliminado'], 200);
    }
}
