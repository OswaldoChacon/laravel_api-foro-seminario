<?php

namespace App\Http\Controllers;

use App\Grupo;
use App\Http\Requests\GrupoRequest;
use App\Plantilla;
use Illuminate\Http\Request;

class GrupoController extends Controller
{

    public function index(Request $request, $id)
    {
        $GrupoTable = Grupo::query();
        if ($request->nombre) {
            $GrupoTable->where('nombre', 'like', '%' . $request->nombre . '%')
                ->where('plantilla_id', $id);
        }
        $GrupoTable = Grupo::where('plantilla_id', $id);
        $grupos = $GrupoTable->paginate(7);
        return response()->json(['grupos' => $grupos, 'plantilla_id' => $id], 200);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(GrupoRequest $request, Plantilla $plantilla)
    {
        $grupo = new Grupo;
        $grupo->fill($request->all());
        $grupo->plantilla()->associate($plantilla)->save();
        return response()->json(['message' => 'Grupo creado'], 200);
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
    public function update(GrupoRequest $request, Plantilla $plantilla, Grupo $grupo)
    {
        $grupo->update($request->all());
        return response()->json(['message' => 'Grupo actualizado'], 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Grupo $grupo)
    {
        $grupo->delete();
        return response()->json(['message' => 'Registro eliminado'], 200);
    }
}
