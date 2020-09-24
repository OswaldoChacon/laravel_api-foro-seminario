<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use  JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use App\User;

class AuthController extends Controller
{
    //
    public function login(Request $request)
    {
        $input = $request->only('num_control', 'password');
        $jwt_token = null;
        if (!$jwt_token = JWTAuth::attempt($input)) {
            return  response()->json([
                'titulo' => 'Credenciales invalidas',
                'message' => 'Correo o contraseña no válidos.',
            ], 404);
        }
        $usuario = User::Buscar($request->num_control)->firstOrFail();
        if(!$usuario->acceso) {
            $usuario->acceso = 1;
            $usuario->save();
        }
                
        if (!$usuario->hasAnyRole($usuario->roles())) {
            return response()->json(['titulo' => 'Acceso denegado', 'message' => 'No tiene ningún rol asignado'], 403);
        }
        return  response()->json([
            'token' => $jwt_token,
            'profile' => $usuario
            // JWTAuth::user()
        ]);
    }
}
