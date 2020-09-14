<?php

namespace App\Http\Controllers\Administrador;

use App\Http\Controllers\Controller;

use App\Http\Requests\Linea\RegistrarLineaRequest;
use App\LineasDeInvestigacion;

class LineasController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
        $lineas = LineasDeInvestigacion::all();
        return response()->json($lineas, 200);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(RegistrarLineaRequest $request)
    {
        //
        $linea = new LineasDeInvestigacion();
        $linea->fill($request->all());
        $linea->save();
        return response()->json(['message' => 'Linea registrada'], 200);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(RegistrarLineaRequest $request, $clave)
    {
        //
        $linea = LineasDeInvestigacion::Where('clave', $clave)->firstOrFail();
        $linea->fill($request->all());
        $linea->save();
        return response()->json(['message' => 'Linea actualizada'], 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($clave)
    {
        //
        $linea = LineasDeInvestigacion::where('clave', $clave)->with('proyectos')->firstOrFail();
        if ($linea->proyectos->count())
            return response()->json(['message' => 'No se puede eliminar el registro'], 400);
        $linea->delete();
        return response()->json(['message' => 'Linea eliminada'], 200);
    }
}
