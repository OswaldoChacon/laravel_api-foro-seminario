<?php

namespace App\Http\Controllers;


use App\Http\Requests\Foro\ForoRequest;
use App\Http\Requests\Linea\EditarLineaRequest;
use App\Http\Requests\Linea\RegistroLineaRequest;
use App\Http\Requests\Tipos\EditarTiposRequest;
use App\Http\Requests\Tipos\RegistrarTiposRequest;
use App\Http\Requests\Usuario\EditarUsuarioRequest;
use App\Http\Requests\Usuario\RegistrarUsuarioRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\User;
use App\Roles;
use App\Foros;
use App\Proyectos;
use App\Http\Requests\Foro\ConfigForoRequest;
use App\Http\Requests\Foro\EditarForoRequest;
use App\LineasDeInvestigacion;
use App\TiposProyectos;



use Exception;
use JWTAuth;

class OficinaController extends Controller
{
    //
    public function __construct()
    {
        // $this->middleware('jwtAuth');
    }

    public function roles(Request $request)
    {
        $roles = Roles::all();
        $roles[sizeof($roles)] = array('nombre' => 'Todos');
        return response()->json($roles, 200);
    }
    public function agregar_rol(Request $request)
    {
        $rol = new Roles();
        $rol->fill($request->all());
        $rol->save();
        return response()->json(['message' => 'Rol agregado'], 200);
    }
    public function actualizar_rol(Request $request, $rol)
    {
        $rol = Roles::where('nombre', $rol)->firstOrFail();
        $rol->nombre = $request->nombre;
        $rol->save();
        return response()->json(['message' => 'Rol actualizado'], 200);
    }
    public function eliminar_rol($rol)
    {
        $rol = Roles::where('nombre', $rol)->firstOrFail();
        $rol->delete();
        return response()->json(['message' => 'Rol eliminado'], 200);
    }

    public function usuarios(Request $request)
    {
        $usersTable = User::query();
        if ($request->rol !== 'Todos')
            $usersTable->whereHas('roles', function ($query) use ($request) {
                $query->where('nombre', $request->rol);
            });
        if ($request->num_control)
            $usersTable->where('num_control','like', '%'.$request->num_control.'%');
        $usuarios = $usersTable->paginate(7);
        // if (!$request->num_control  && !$request->rol)
            // $usuarios = User::paginate(7);
        $roles = Roles::all();
        foreach ($usuarios as $usuario) {
            $usuario->roles = $roles;
            $usuario->nombreCompleto = $usuario->getNombre();
            foreach ($usuario->roles as $rol) {
                $rol['is'] = $usuario->hasRole($rol->nombre);
            }
        }
        return response()->json($usuarios, 200);
    }
    public function registrar_usuario(RegistrarUsuarioRequest $request)
    {
        $usuario = new User();
        $usuario->fill($request->all());
        // $usuario->password = Str::random(10);
        $usuario->enviarEmailConfirmacion();
        $usuario->save();
        return response()->json(['message' => 'Usuario registrado'], 200);
    }
    public function actualizar_usuario($num_control, EditarUsuarioRequest $request)
    {
        $usuario = User::Where('num_control', $num_control)->firstOrFail();
        if ($usuario->email !== $request->email) {
            $usuario->fill($request->all());
            $usuario->acceso = 0;
            $usuario->enviarEmailConfirmacion();
        }
        $usuario->save();
        return response()->json(['message' => 'Usuario actualizado'], 200);
    }
    public function eliminar_usuario($num_control)
    {
        $usuario = User::where('num_control', $num_control)->with('proyectos')->firstOrFail();
        if ($usuario->proyectos->count())
            return response()->json(['message' => 'No se puede eliminar el registro'], 400);
        $usuario->delete();
        return response()->json(['message' => 'Usuario eliminado'], 200);
    }
    public function agregar_rolUsuario(Request $request, $num_control)
    {
        $user = User::where('num_control', $num_control)->firstOrFail();
        $rol = Roles::where('nombre', $request->rol)->firstOrFail();
        $user->roles()->attach($rol);
        return response()->json(['message' => 'Rol agregado'], 200);
    }
    public function eliminar_rolUsuario(Request $request, $num_control)
    {
        $user = User::where('num_control', $num_control)->firstOrFail();
        $rol = Roles::where('nombre', $request->rol)->firstOrFail();
        $user->roles()->detach($rol);
        return response()->json(['message' => 'Rol eliminado'], 200);
    }
    public function lineas()
    {
        $lineas = LineasDeInvestigacion::all();
        return response()->json($lineas, 200);
    }
    public function registrar_linea(RegistroLineaRequest $request)
    {
        $linea = new LineasDeInvestigacion();
        $linea->fill($request->all());
        $linea->save();
        return response()->json(['message' => 'Linea registrada'], 200);
    }

    public function actualizar_linea(EditarLineaRequest $request, $clave)
    {
        $linea = LineasDeInvestigacion::Where('clave', $clave)->firstOrFail();
        $linea->fill($request->all());
        $linea->save();
        return response()->json(['message' => 'Linea actualizada'], 200);
    }
    public function eliminar_linea($clave)
    {
        $linea = LineasDeInvestigacion::where('clave', $clave)->with('proyectos')->firstOrFail();
        if ($linea->proyectos->count())
            return response()->json(['message' => 'No se puede eliminar el registro'], 400);
        $linea->delete();
        return response()->json(['message' => 'Linea eliminada'], 200);
    }
    public function tiposProyecto()
    {
        $tipos = TiposProyectos::all();
        return response()->json($tipos, 200);
    }
    public function registrar_tipoProyecto(RegistrarTiposRequest $request)
    {
        $tipo = new TiposProyectos();
        $tipo->fill($request->all())->save();
        return response()->json(['message' => 'Tipo de proyecto registrado'], 200);
    }
    public function actualizar_tipoProyecto(EditarTiposRequest $request, $clave)
    {
        $tipo = TiposProyectos::where('clave', $clave)->firstOrFail();
        $tipo->fill($request->all())->save();
        return response()->json(['message' => 'Tipo de proyecto actualizado'], 200);
    }
    public function eliminar_tipoProyecto($clave)
    {
        $tipo_proyecto = TiposProyectos::where('clave', $clave)->with('proyectos')->firstOrFail();
        if ($tipo_proyecto->proyectos->count())
            return response()->json(['message' => 'No se puede eliminar el registro'], 400);
        $tipo_proyecto->delete();
        return response()->json(['message' => 'Tipo de proyecto eliminado'], 200);
    }
    public function foros(Request $request)
    {
        $forosTable = Foros::query();
        $forosTable->select('acceso','anio','periodo','no_foro','nombre','slug');
        if ($request->no_foro)
            $forosTable->where('no_foro', 'like','%'.$request->no_foro.'%');
        $foros = $forosTable->paginate(7);
        foreach($foros as $foro)
        {
            $foro->canActivate = $foro->canActivate();
        }
        // if (!$request->no_foro)
            // $foros = Foros::paginate(2);
        return response()->json($foros, 200);
    }
    public function registrar_foro(ForoRequest $request)
    {
        $prefijo = str_split($request->anio);
        $prefijo = $prefijo[2] . $prefijo[3];
        if ($request->periodo == "Agosto-Diciembre") {
            $prefijo = $prefijo . "02-";
        } else {
            $prefijo = $prefijo . "01-";
        }
        $foro = new Foros();
        $foro->fill($request->all());
        $foro->slug = "foro-" . $request->no_foro;
        $foro->prefijo = $prefijo;
        $foro->user_id = 18;
        $foro->save();
        return response()->json(['message' => 'Foro registrado'], 200);
    }
    public function actualizar_foro(EditarForoRequest $request, $slug)
    {
        $foro = Foros::Where('slug', $slug)->firstOrFail();
        $foro->fill($request->all());
        $foro->slug = "foro-" . $request->no_foro;
        $foro->save();
        return response()->json(['message' => 'Foro actualizado'], 200);
    }
    public function eliminar_foro($slug)
    {
        $foro = Foros::Where('slug', $slug)->with('proyectos')->firstOrFail();
        if ($foro->proyectos->count())
            return response()->json(['message' => 'No se puede eliminar el registro'], 400);
        $foro->delete();
        return response()->json(['message' => 'Foro eliminado'], 200);
    }
    public function obtener_foro($slug)
    {
        $foro = Foros::with('fechas')->where('slug', $slug)->firstOrFail();
        if (!$foro->acceso)
            return response()->json(['message' => 'Foro no activo'], 401);
        $posicionET = 0;
        foreach ($foro->fechas as $fecha) {
            $recesos = $fecha->receso()->select('posicion')->get()->pluck('posicion')->toArray();
            $fecha['intervalos'] = $fecha->horarioIntervalos($foro->duracion, 1);
            foreach ($fecha['intervalos'] as $hora) {
                $hora->posicion = $posicionET;
                $hora->break = in_array($posicionET, $recesos);
                $posicionET++;
            }
            $fecha->length = $posicionET;
        }
        return response()->json($foro, 200);
    }
    public function configurar_foro(ConfigForoRequest $request, $slug)
    {
        $foro = Foros::Where('slug', $slug)->firstOrFail();
        if (!$foro->acceso)
            return response()->json(['message' => 'Foro no activo'], 500);
        $foro->lim_alumnos = $request->lim_alumnos;
        $foro->num_aulas = $request->num_aulas;
        $foro->duracion = $request->duracion;
        $foro->num_maestros = $request->num_maestros;
        $foro->save();
        return response()->json(['message' => 'Config. Registrada'], 200);
        //    $tipos = TiposProyectos::all();
        //    $users = User::all();
        //    return response()->json(array(
        //        'users'=>$users,
        //        'tipos'=>$tipos
        //    ), 200);
    }
    public function activar_foro(Request $request, $slug)
    {
        $request->validate(['acceso' => 'required|boolean']);
        $foros = Foros::Where('acceso', true)->get();
        if($request->acceso && !$foros->isEmpty())
            return response()->json(['message' => 'No se permite tener dos foros activos'], 200);    
        if (!$request->acceso) {
            $foro = Foros::Where('slug', $slug)->firstOrFail();
            $foro->acceso = $request->acceso;
            $foro->save();
            $message = $request->acceso ? 'Foro activado' : 'Foro desactivado';
            return response()->json(['message' => $message], 200);
        }
        
    }

    public function agregar_foroDocente(Request $request, $slug)
    {
        $foro = Foros::Where([
            ['slug', $slug],
            ['acceso', true]
        ])->firstOrFail();
        $user = User::Where('num_control', $request->num_control)->firstOrFail();
        if (is_null($foro))
            return response()->json(['error' => 'Error al identificar el foro activo'], 404);
        if (is_null($user))
            return response()->json(['error' => 'Error al identificar el docente '], 404);
        $user->foros_user()->attach($foro);
        return response()->json(['message' => 'Maestro registrado'], 200);
    }
    public function asignar_jurado(Request $request, $folio)
    {
        // pendiente validacion        
        $proyecto = Proyectos::Where('folio', $folio)->firstOrFail();
        $foro = $proyecto->foro()->first();
        if ($proyecto->jurado()->count() + 1 > $foro->num_maestros)
            return response()->json(['Error' => 'Cantidad de maestros excedido'], 422);
        $jurado = User::Where('num_control', $request->num_control)->firstOrFail();
        $jurado->jurado_proyecto()->attach($proyecto);
        return response()->json(['message' => 'Maestro agregado al jurado'], 200);
    }
    public function eliminar_jurado(Request $request, $folio)
    {
        $jurado = User::Where('num_control', $request->num_control)->firstOrFail();
        $proyecto = Proyectos::Where('folio', $folio)->firstOrFail();
        if ($proyecto->asesor == $jurado->id)
            return response()->json(['Error' => 'No se puede quitar al asesor como parte del jurado'], 422);
        $jurado->jurado_proyecto()->detach($proyecto);
        return response()->json(['message' => 'Maestro excluido del jurado'], 200);
    }
}
