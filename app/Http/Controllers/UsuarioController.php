<?php

namespace App\Http\Controllers;

use App\Rol;
use JWTAuth;
use App\Foro;
use App\User;
use Carbon\Carbon;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Database\Eloquent\Builder;
use App\Http\Requests\Usuario\RegistrarUsuarioRequest;

class UsuarioController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $usuariosTable = User::query();
        if ($request->rol !== 'Todos' && $request->rol !== 'Sin rol')
            $usuariosTable->UsuariosConRol($request->rol);        
        if ($request->rol == 'Usuarios sin rol')
            $usuariosTable->doesntHave('roles');
        if ($request->num_control)
            $usuariosTable->where('num_control', 'like', '%' . $request->num_control . '%');            
        $usuarios = $usuariosTable->paginate(7);
        foreach ($usuarios as $usuario) {
            $usuario->append('roles');
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
        $usuarioLogueado = JWTAuth::user();
        $usuario = new User();
        $usuario->fill($request->all());
        // $password = Str::random(10);
        $password = $request->num_control;
        $usuario->password = bcrypt($password);
        $usuario->save();
        // $usuario->enviarEmail($password, 'nuevo');
        if ($request->rol === 'Docente' && $usuarioLogueado->hasRole('Taller')) {
            $rol = Rol::where('nombre_', 'Alumno')->firstOrFail();
            $usuario->roles()->attach($rol);
        }
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
    public function update(RegistrarUsuarioRequest $request, User $usuario)
    {
        $usuario = User::Buscar($usuario->num_control)->first();
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
    public function destroy(User $usuario)
    {        
        if ($usuario->proyectos->count() > 0 || $usuario->asesor()->count() > 0 || $usuario->foros_users()->count() > 0)
            return response()->json(['message' => 'No se puede eliminar el registro'], 400);
        $usuario->delete();
        return response()->json(['message' => 'Usuario eliminado'], 200);
    }

    public function agregarRol(Request $request)
    {
        $request->validate([
            'rol' => 'required|exists:roles,nombre_',
            'num_control' => 'required|exists:users,num_control'
        ]);
        $usuario = User::Buscar($request->num_control)->first();
        if ($usuario->hasRole($request->rol))
            return response()->json(['message' => 'El usuario ya tiene agregado el rol asignado'], 400);
        if ($usuario->hasRole('Alumno'))
            return response()->json(['message' => 'Un alumno no puede tener más de un rol'], 400);
        if (($usuario->hasRole('Docente') || $usuario->hasRole('Administrador')) && $request->rol === 'Alumno')
            return response()->json(['message' => 'Un docente no puede tener el rol de alumno'], 400);
        $rol = Rol::where('nombre_', $request->rol)->first();
        $usuario->roles()->attach($rol);
        return response()->json(['message' => 'Rol agregado'], 200);
    }
    public function eliminarRol(Request $request, $num_control)
    {
        $request->validate([
            'rol' => 'required|exists:roles,nombre_',
        ]);
        $usuario = User::Buscar($num_control)->first();
        if (is_null($usuario))
            return response()->json(['message' => 'Usuario no encontrado'], 404);
        $rol = Rol::where('nombre_', $request->rol)->firstOrFail();
        $usuario->roles()->detach($rol);
        return response()->json(['message' => 'Rol eliminado'], 200);
    }
    public function docentes()
    {
        $docentes = User::UsuariosConRol('Docente')->get();
        return response()->json($docentes, 200);
    }
    public function listaAlumnos()
    {
        $usuarioLogueado = JWTAuth::user();
        $miProyecto = $usuarioLogueado->proyectos()->whereHas('foro', function (Builder $query) {
            $query->Activo(true);
        })->first();
        if (is_null($miProyecto))
            return response()->noContent();
        $miEquipo = $miProyecto->integrantes()->DatosBasicos()->where('user_id', '!=', $usuarioLogueado->id)->get();
        $foro = Foro::Activo(true)->first();
        if (Carbon::now()->toDateString() > $foro->fecha_limite || $miProyecto->enviado || $miProyecto->aceptado)
            return response()->json($miEquipo, 200);
        $alumnos = User::DatosBasicos()->UsuariosConRol('Alumno')->where('num_control', '!=', $usuarioLogueado->num_control)->ConDatosCompletos()->SinProyectos()->get();
        $alumnos = $alumnos->merge($miEquipo);
        foreach ($alumnos as $alumno) {
            $alumno->myTeam = $miEquipo->contains('num_control', $alumno->num_control);
        }
        $alumnosOrdenados = $alumnos->sortByDesc('myTeam')->values();
        return response()->json($alumnosOrdenados, 200);
    }
}
