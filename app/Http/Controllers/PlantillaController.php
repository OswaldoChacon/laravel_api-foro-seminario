<?php

namespace App\Http\Controllers;

use App\Grupo;
use App\Concepto;
use App\Http\Requests\PlantillaRequest;
use App\Plantilla;
use Illuminate\Http\Request;

class PlantillaController extends Controller
{
    public function index(Request $request)
    {
        $PlantillasTable = Plantilla::query();
        if ($request->nombre)
            $PlantillasTable->where('nombre', 'like', '%' . $request->nombre . '%');
        $Plantillas = $PlantillasTable->paginate(7);
        return response()->json($Plantillas, 200);
    }

    public function store(PlantillaRequest $request)
    {
        $plantilla = new Plantilla;
        $plantilla->fill($request->all())->save();
        return response()->json(['message' => 'Plantilla creada'], 201);
    }

    public function update(PlantillaRequest $request, Plantilla $plantilla)
    {
        // $plantilla = Plantilla::find($id);
        $plantilla->nombre = $request->nombre;
        $plantilla->save();
        return response()->json(['message' => 'Plantilla actualizada'], 200);
    }

    public function destroy(Plantilla $plantilla)
    {
        $plantilla->delete();
        return response()->json(['message' => 'Registro eliminado'], 200);
    }
}
