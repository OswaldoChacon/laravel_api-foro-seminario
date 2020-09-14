<?php

namespace App\Http\Controllers\Administrador;

use App\User;
use App\Roles;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\Usuario\EditarUsuarioRequest;
use App\Http\Requests\Usuario\RegistrarUsuarioRequest;

class UsuariosController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $usersTable = User::query();
        if ($request->rol !== 'Todos')
            $usersTable->whereHas('roles', function ($query) use ($request) {
                $query->where('nombre_', $request->rol);
            });
        if ($request->num_control)
            $usersTable->where('num_control', 'like', '%' . $request->num_control . '%');
        $usuarios = $usersTable->paginate(7);
        $roles = Roles::all();
        foreach ($usuarios as $usuario) {
            $usuario->nombreCompleto = $usuario->getNombre();
            foreach ($roles as $rol) {
                $rol->is = $usuario->hasRole($rol->nombre_);
            }
            $usuario->roles = $roles->toArray();
        }
        return response()->json($usuarios, 200);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(RegistrarUsuarioRequest $request)
    {
        $usuario = new User();
        $usuario->fill($request->all());
        // $usuario->password = Str::random(10);
        $usuario->enviarEmailConfirmacion();
        $usuario->save();
        return response()->json(['message' => 'Usuario registrado'], 200);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(RegistrarUsuarioRequest $request, $num_control)
    {
        $usuario = User::Buscar($num_control)->first();
        // if ($usuario->email !== $request->email) {
        //     $usuario->fill($request->all());
        //     $usuario->acceso = 0;
        //     $usuario->enviarEmailConfirmacion();
        // }
        $usuario->fill($request->all());
        $usuario->save();
        return response()->json(['message' => 'Usuario actualizado'], 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($num_control)
    {
        $usuario = User::Buscar($num_control)->with('proyectos')->firstOrFail();
        if ($usuario->proyectos->count() > 0 || $usuario->asesor()->count() > 0 || $usuario->foros_user()->count() > 0)
            return response()->json(['message' => 'No se puede eliminar el registro'], 400);
        $usuario->delete();
        return response()->json(['message' => 'Usuario eliminado'], 200);
    }

    public function agregar_rolUsuario(Request $request, $num_control)
    {
        $request->validate([
            'rol' => 'required|exists:roles,nombre_',            
        ]);        
        // $usuario = User::Buscar($num_control)->firstOrFail();
        $usuario = User::Buscar($num_control)->firstOrFail(['*'], 'First book not found');
        // if(is_null($usuario))
        //     return response()->json(['message'=>'Usuario no encontrado'], 404);
        if ($usuario->hasRole('Alumno'))
            return response()->json(['message' => 'Un alumno no puede tener más de un rol'], 400);
        if (($usuario->hasRole('Docente') || $usuario->hasRole('Administrador')) && $request->rol === 'Alumno')
            return response()->json(['message' => 'Un docente no puede tener el rol de alumno'], 400);
        $rol = Roles::where('nombre_', $request->rol)->first();
        $usuario->roles()->attach($rol);
        return response()->json(['message' => 'Rol agregado'], 200);
    }
    public function eliminar_rolUsuario(Request $request, $num_control)
    {
        $user = User::Buscar($num_control)->firstOrFail();
        $rol = Roles::where('nombre_', $request->rol)->firstOrFail();
        $user->roles()->detach($rol);
        return response()->json(['message' => 'Rol eliminado'], 200);
    }

    public function docentes()
    {
        $docentes = User::DatosBasicos()->whereHas('roles', function ($query) {
            $query->where('roles.nombre_', 'Docente');
        })->get();
        foreach ($docentes as $docente) {
            // $docente->maestro = 
        }
    }
}
