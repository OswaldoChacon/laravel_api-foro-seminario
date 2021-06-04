<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\Solicitud\TipoDeSolicitudRequest;
use App\TipoDeSolicitud;

class TipoDeSolicitudController extends Controller
{
    public function index()
    {
        $solicitudes = TipoDeSolicitud::all();
        return response()->json($solicitudes, 200);
    }

    public function store(TipoDeSolicitudRequest $request, TipoDeSolicitud $solicitud)
    {
        $solicitud->fill($request->all())->save();
        return response()->json(['message' => 'Registro agregado'], 201);
    }

    public function show(TipoDeSolicitud $solicitud)
    {
        return response()->json($solicitud, 200);
    }

    public function update(TipoDeSolicitudRequest $request, TipoDeSolicitud $solicitud)
    {        
        $solicitud->update($request->all());
        return response()->json(['message' => 'Registro actualizado'], 200);
    }

    public function destroy(TipoDeSolicitud $solicitud)
    {
        // if ($solicitude->notificaciones->count())
        //     return response()->json(['message' => 'No se puede eliminar el registro'], 400);
        $solicitud->delete();
        return response()->json(['message' => 'Registro eliminado'], 200);
    }
}
