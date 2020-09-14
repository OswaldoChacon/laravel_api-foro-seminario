<?php

namespace App\Http\Controllers\Administrador;

use App\Http\Controllers\Controller;

use App\Http\Requests\Tipos\EditarTiposRequest;
use App\Http\Requests\Tipos\RegistrarTiposRequest;
use Illuminate\Http\Request;
use App\TiposProyectos;

class TiposProyectoController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $tipos = TiposProyectos::all();
        return response()->json($tipos, 200);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(RegistrarTiposRequest $request)
    {
        $tipo = new TiposProyectos();
        $tipo->fill($request->all())->save();
        return response()->json(['message' => 'Tipo de proyecto registrado'], 200);
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
    public function update(RegistrarTiposRequest $request, $clave)
    {
        $tipo = TiposProyectos::where('clave', $clave)->first();
        $tipo->fill($request->all())->save();
        return response()->json(['message' => 'Tipo de proyecto actualizado'], 200);     
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($clave)
    {
        $tipo_proyecto = TiposProyectos::where('clave', $clave)->with('proyectos')->firstOrFail();
        if ($tipo_proyecto->proyectos->count())
            return response()->json(['message' => 'No se puede eliminar el registro'], 400);
        $tipo_proyecto->delete();
        return response()->json(['message' => 'Tipo de proyecto eliminado'], 200);     
    }
}
