<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\Rol\RolRequest;
use Illuminate\Http\Request;
use App\Rol;

class RolController extends Controller
{
    //
    public function index()
    {
        $Rol = Rol::all();
        return response()->json($Rol, 200);
    }
    public function store(RolRequest $request, Rol $rol)
    {
        $rol->fill($request->all())->save();
        return response()->json(['message' => 'Rol agregado'], 200);
    }

    public function show(Rol $rol)
    {
        return response()->json($rol, 200);
    }

    public function update(RolRequest $request, Rol $rol)
    {
        $rol->update($request->all());
        return response()->json(['message' => 'Rol actualizado'], 200);
    }

    public function destroy(Rol $rol)
    {        
        // $this->authorize('delete', $rol);
        $rol->delete();
        return response()->json(['message' => 'Rol eliminado'], 200);
    }
}
