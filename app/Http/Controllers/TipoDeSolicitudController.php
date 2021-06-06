<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\TipoDeSolicitudRequest;
use App\TipoDeSolicitud;

class TipoDeSolicitudController extends Controller
{
    public function index()
    {
        $solicitudes = TipoDeSolicitud::all();
        return response()->json($solicitudes, 200);
    }

    public function store(TipoDeSolicitudRequest $request, TipoDeSolicitud $tipoDeSolicitud)
    {
        $tipoDeSolicitud->fill($request->all())->save();
        return response()->json(['message' => 'Registro agregado'], 201);
    }

    public function show(TipoDeSolicitud $tipoDeSolicitud)
    {
        return response()->json($tipoDeSolicitud, 200);
    }

    public function update(TipoDeSolicitudRequest $request, TipoDeSolicitud $tipoDeSolicitud)
    {
        $tipoDeSolicitud->update($request->all());
        return response()->json(['message' => 'Registro actualizado'], 200);
    }

    public function destroy(TipoDeSolicitud $tipoDeSolicitud)
    {

        $tipoDeSolicitud->delete();
        return response()->json(['message' => 'Registro eliminado'], 200);
    }
}