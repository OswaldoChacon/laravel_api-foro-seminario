<?php

namespace App\Http\Controllers;

use JWTAuth;
use App\User;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Http\Requests\CambiarPasswordRequest;
use App\Http\Requests\Usuario\RegistrarUsuarioRequest;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $input = $request->only(['email', 'password']);
        $jwt_token = null;
        if (!$jwt_token = JWTAuth::attempt(array_merge($input,['acceso'=>1]))) {
            return  response()->json([
                'titulo' => 'Credenciales invalidas',
                'message' => 'Correo o contraseña no válidos.',
            ], 404);
        }
        $usuario = User::where('email', $request->email)->first();
        if (!$usuario->hasAnyRole($usuario->roles())) {
            return response()->json(['titulo' => 'Acceso denegado', 'message' => 'No tiene ningún rol asignado'], 403);
        }
        if (!$usuario->acceso) {
            $usuario->acceso = 1;
            $usuario->save();
        }
        return  response()->json([
            'token' => $jwt_token,
            'profile' => $usuario
        ]);
    }
    public function restablecerPassword(Request $request)
    {
        $request->validate([
            'email' => 'email|exists:users,email'
        ]);
        $usuario = User::where('email', $request->email)->first();
        $password = Str::random(10);
        $usuario->password = bcrypt($password);
        $usuario->save();
        $usuario->enviarEmail($password, 'forgot_password');
        return response()->json(['message' => 'Contraseña restablecida. Verifica tu correo.'], 200);
    }
    public function datosPersonales()
    {
        $usuarioLogueado = JWTAuth::user();
        return response()->json($usuarioLogueado, 200);
    }
    public function cambiarContrasena(CambiarPasswordRequest $request)
    {
        $usuarioLogueado = JWTAuth::user();
        $usuarioLogueado->password = bcrypt($request->nuevo_password);
        $usuarioLogueado->save();
        return response()->json(['message' => 'Contraseña actualizada'], 200);
    }
    public function actualizarDatos(RegistrarUsuarioRequest $request, User $usuario)
    {
        $request->validate([
            'nombre' => 'required',
            'apellidoP' => 'required',
            'apellidoM' => 'required'
        ]);
        $usuarioLogueado = JWTAuth::user();
        $usuarioUpdate = User::Buscar($usuarioLogueado->num_control)->first();
        if (!$usuarioLogueado->can('actualizar_datos', $usuarioUpdate))
            return response()->json(['message' => 'No estas autorizado para realizar estos cambios'], 403);
        if ($usuarioLogueado->hasRole('Alumno'))
            $request->num_control = $usuarioLogueado->num_control;

        $usuarioLogueado->fill($request->all());
        $usuarioLogueado->num_control = $request->num_control;
        $usuarioLogueado->save();
        return response()->json(['message' => 'Datos actualizados'], 200);
    }
}
