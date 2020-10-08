<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\Rol\RegistrarRolRequest;
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
    public function store(RegistrarRolRequest $request)
    {        
        $rol = new Rol();
        $rol->fill($request->all());
        $rol->save();
        return response()->json(['message' => 'Rol agregado'], 200);
    }
    public function show()
    {
    }
    public function update(RegistrarRolRequest $request, Rol $role)
    {
        $rol = Rol::where('nombre_', $role->nombre_)->first();                
        $rol->nombre_ = $request->nombre_;
        $rol->save();
        return response()->json(['message' => 'Rol actualizado'], 200);
    }
    public function destroy(Rol $role)
    {
        if($role->users()->count())
            return response()->json(['message' => 'No se puede eliminar el registro'], 400);
        $role->delete();
        return response()->json(['message' => 'Rol eliminado'], 200);
    }
}
