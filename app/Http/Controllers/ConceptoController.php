<?php

namespace App\Http\Controllers;

use App\Concepto;
use Illuminate\Http\Request;

class ConceptoController extends Controller
{
    public function conceptos(Request $request, $id){
        $conceptoTable = Concepto::query();
        if ($request->nombre){
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
    public function store(Request $request)
    {
        $Concepto = new Concepto;
        $Concepto->fill($request->all())->save();
        return response()->json(['message' => 'Concepto guardado'], 200);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update($id, Request $request){
        $Concepto = Concepto::find($id);
        $Concepto->conceptos = $request->conceptos;
        $Concepto->ponderacion = $request->ponderacion;
        $Concepto->save();
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
