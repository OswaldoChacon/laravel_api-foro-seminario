<?php

namespace App\Http\Controllers;



use App\Http\Requests\Usuario\EditarUsuarioRequest;
use App\Http\Requests\Usuario\RegistrarUsuarioRequest;
use Illuminate\Http\Request;

use App\User;
use App\Roles;
use App\Foros;
use App\Proyectos;


use JWTAuth;

class OficinaController extends Controller
{
    //
    public function __construct()
    {
        // $this->middleware('jwtAuth');
    }
    
    public function agregar_foroDocente(Request $request, $slug)
    {
        $foro = Foros::Where([
            ['slug', $slug],
            ['acceso', true]
        ])->firstOrFail();
        $user = User::Buscar($request->num_control)->firstOrFail();
        if (is_null($foro))
            return response()->json(['error' => 'Error al identificar el foro activo'], 404);
        if (is_null($user))
            return response()->json(['error' => 'Error al identificar el docente '], 404);
        $user->foros_user()->attach($foro);
        return response()->json(['message' => 'Maestro registrado'], 200);
    }
  
   
}
