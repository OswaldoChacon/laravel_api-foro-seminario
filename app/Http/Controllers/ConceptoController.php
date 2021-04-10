<?php

namespace App\Http\Controllers;

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
}
