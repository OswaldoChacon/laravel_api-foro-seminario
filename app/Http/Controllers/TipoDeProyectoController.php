<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;


use App\Http\Requests\TipoDeProyectoRequest;
use App\TipoDeProyecto;

class TipoDeProyectoController extends Controller
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
    public function store(TipoDeProyectoRequest $request, TipoDeProyecto $tipoDeProyecto)
    {
        $this->authorize('create', TipoDeProyecto::class);
        $tipoDeProyecto->fill($request->all())->save();
        return response()->json(['message' => 'Registro creado'], 201);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(TipoDeProyecto $tipoDeProyecto)
    {
        return response()->json($tipoDeProyecto, 200);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(TipoDeProyectoRequest $request, TipoDeProyecto $tipoDeProyecto)
    {
        $tipoDeProyecto->update($request->all());
        return response()->json(['message' => 'Registro actualizado'], 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(TipoDeProyecto $tipoDeProyecto)
    {
        // $this->authorize('delete',$tipoDeProyecto);
        $tipoDeProyecto->delete();
        return response()->json(['message' => 'Registro eliminado'], 200);
    }
}
