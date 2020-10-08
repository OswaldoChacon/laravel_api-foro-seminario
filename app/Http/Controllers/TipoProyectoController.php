<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;


use App\Http\Requests\Tipos\RegistrarTiposRequest;
use App\TipoDeProyecto;

class TipoProyectoController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $tiposDeProyecto = TipoDeProyecto::all();
        return response()->json($tiposDeProyecto, 200);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(RegistrarTiposRequest $request)
    {
        $tipoDeProyecto = new TipoDeProyecto();
        $tipoDeProyecto->fill($request->all())->save();
        return response()->json(['message' => 'Registro agregado'], 200);
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
    public function update(RegistrarTiposRequest $request, TipoDeProyecto $tiposProyecto)
    {
        $tipoDeProyecto = TipoDeProyecto::where('clave', $tiposProyecto->clave)->first();
        if(is_null($tipoDeProyecto))
            return response()->json(['message' => 'Tipo de proyecto no encontrado'], 404);
        $tipoDeProyecto->fill($request->all())->save();
        return response()->json(['message' => 'Registro actualizado'], 200);     
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(TipoDeProyecto $tiposProyecto)
    {        
        if ($tiposProyecto->proyectos->count())
            return response()->json(['message' => 'No se puede eliminar el registro'], 400);
        $tiposProyecto->delete();
        return response()->json(['message' => 'Registro eliminado'], 200);     
    }
}
