<?php

namespace App\Http\Controllers;

use JWTAuth;
use App\Mail\ForgotPassword;
use Illuminate\Http\Request;
use App\Http\Requests\CambiarPasswordRequest;


class UsuariosController extends Controller
{
    //
    public function cambiar_contrasena(CambiarPasswordRequest $request)
    {                  
        $usuarioLogueado = JWTAuth::user();
        $usuarioLogueado->password = bcrypt($request->nuevo_password);
        $usuarioLogueado->save();        
        return response()->json(['message'=>'Contraseña actualizada'], 200);
    }
    public function forgot_password(Request $request)
    {
        $request->validate([
            'email'=>'exists:users,email'
        ]);
        // Mail::to($request->email)->send(new ForgotPassword);

    }
}

