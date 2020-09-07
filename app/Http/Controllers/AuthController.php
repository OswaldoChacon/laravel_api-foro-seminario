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
        $user = User::Buscar($request->num_control)->firstOrFail();
        $user->nombreCompleto = $user->getNombre();
        if(!$user->hasAnyRole($user->roles())){
            return response()->json(['titulo'=>'Acceso denegado','message'=>'No tiene ningún rol asignado'], 403);  
        }
        return  response()->json([            
            'token' => $jwt_token,
            'profile'=> $user
            // JWTAuth::user()
        ]);
    }
}
