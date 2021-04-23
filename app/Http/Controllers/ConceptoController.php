<?php

namespace App\Http\Controllers;

use App\Concepto;
use App\Grupo;
use App\Http\Requests\ConceptoRequest;
use Illuminate\Http\Request;

class ConceptoController extends Controller
{
    public function index(Request $request, $id)
    {
        $conceptoTable = Concepto::query();
        if ($request->nombre) {
            $conceptoTable->where('conceptos', 'like', '%' . $request->nombre . '%')
                ->where('grupo_id', $id);
        }
        $conceptoTable = Concepto::where('grupo_id', $id);
        $conceptos = $conceptoTable->paginate(7);
        return response()->json(['conceptos' => $conceptos, 'grupo_id' => $id], 200);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(ConceptoRequest $request, Grupo $grupo)
    {
        $concepto = new Concepto;
        $concepto->fill($request->all());
        $concepto->grupo()->associate($grupo)
            ->save();
        return response()->json(['message' => 'Concepto guardado'], 201);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Grupo $grupo, Concepto $concepto, ConceptoRequest $request)
    {
        // $Concepto = Concepto::find($id);
        // $Concepto->conceptos = $request->conceptos;
        // $Concepto->ponderacion = $request->ponderacion;
        // $Concepto->save();
        $concepto->update($request->all());
        return response()->json(['message' => 'Concepto actualizado'], 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        Concepto::find($id)->delete();
        return response()->json(['message' => 'Registro eliminado'], 200);
    }
}
