<?php

namespace App\Http\Controllers;

use App\Grupo;
use App\Concepto;
use App\Plantilla;
use Illuminate\Http\Request;

class PlantillaEvaluacionController extends Controller
{
    public function index(Request $request){
        $PlantillasTable = Plantilla::query();
        if ($request->nombre){
            $PlantillasTable->where('nombre', 'like', '%' . $request->nombre . '%');
        }
        $Plantillas = $PlantillasTable->paginate(7);
        return response()->json($Plantillas, 200);
    }

    public function store(Request $request){
        $plantilla = new Plantilla;
        $plantilla->fill($request->all())->save();
        return response()->json(['message' => 'plantilla creada'], 200);
    }

    public function update($id, Request $request){
        $plantilla = Plantilla::find($id);
        $plantilla->nombre = $request->nombre;
        $plantilla->save();
        return response()->json(['message' => 'plantilla actualizado'], 200);
    }

    public function destroy($id)
    { 
        $plantilla = Plantilla::find($id)->delete();
        return response()->json(['message' => 'Registro eliminado'], 200);     
    }
}
