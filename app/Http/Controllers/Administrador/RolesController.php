<?php

namespace App\Http\Controllers\Administrador;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use App\Roles;

class RolesController extends Controller
{
    //
    public function index()
    {
        $roles = Roles::all();        
        return response()->json($roles, 200);
    }
    public function store(Request $request)
    {
        $rol = new Roles();
        $rol->fill($request->all());
        $rol->save();
        return response()->json(['message' => 'Rol agregado'], 200);
    }
    public function show()
    {
    }
    public function update(Request $request, $rol)
    {
        $rol = Roles::where('nombre_', $rol)->firstOrFail();
        $rol->nombre_ = $request->nombre_;
        $rol->save();
        return response()->json(['message' => 'Rol actualizado'], 200);
    }
    public function destroy($rol)
    {
        $rol = Roles::where('nombre_', $rol)->firstOrFail();
        if($rol->users()->count())
            return response()->json(['message' => 'No se puede eliminar el registro'], 400);
        $rol->delete();
        return response()->json(['message' => 'Rol eliminado'], 200);
    }
}
