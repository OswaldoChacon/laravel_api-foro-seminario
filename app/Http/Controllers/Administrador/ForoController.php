<?php

namespace App\Http\Controllers\Administrador;

use JWTAuth;
use App\Foro;

use App\User;
use App\Proyecto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Database\Eloquent\Builder;
use App\Http\Requests\Foro\ConfigForoRequest;
use App\Http\Requests\Foro\RegistrarForoRequest;

class ForoController extends Controller
{
    //  
    public function index(Request $request)
    {
        $forosTable = Foro::query();
        $forosTable->select('activo', 'anio', 'periodo', 'no_foro', 'nombre', 'slug', 'fecha_limite');
        if ($request->no_foro)
            $forosTable->where('no_foro', 'like', '%' . $request->no_foro . '%');
        $foros = $forosTable->paginate(7);
        foreach ($foros as $foro) {
            $foro->canActivate = $foro->canActivate();
        }
        return response()->json($foros, 200);
    }
    public function store(RegistrarForoRequest $request)
    {
        $usuario = JWTAuth::user();        
        $foro = new Foro();
        $foro->fill($request->all());
        $foro->slug = "foro-" . $request->no_foro;
        $foro->prefijo = $foro->getPrefijo($request->anio, $request->periodo);
        $foro->user()->associate($usuario);        
        $foro->save();
        return response()->json(['message' => 'Foro registrado'], 200);
    }
    public function show($slug)
    {
        // agregar el intime tal vez para hacer la verificacion en el frontend
        $foro = Foro::select('id','slug','duracion','lim_alumnos','num_aulas','num_maestros','activo')->with(['fechas'])->Buscar($slug)->first();
        if (is_null($foro))
            return response()->json(['message' => 'Foro no encontrado'], 404);
        $posicionET = 0;
        // pasarlo a foro o a fechas, cualquierda de dos, validar mañana
        foreach ($foro->fechas as $fecha) {
            $recesos = $fecha->recesos()->select('posicion')->get()->pluck('posicion')->toArray();
            $fecha['intervalos'] = $fecha->horarioIntervalos($foro->duracion, 1, $recesos);
            foreach ($fecha['intervalos'] as $hora) {
                $hora->posicion = $posicionET;
                $hora->break = in_array($posicionET, $recesos);
                $posicionET++;
            }
            $fecha->length = $posicionET;
        }        
        $foro->append('docentes');
        return response()->json($foro, 200);
    }
    public function update(RegistrarForoRequest $request, Foro $foro)
    {
        $foro = Foro::Buscar($foro->slug)->first();
        if(is_null($foro))
            return response()->json(['message'=>'Foro no encontrado'], 404);
        $foro->fill($request->all());
        $foro->prefijo = $foro->getPrefijo($request->anio,$request->periodo);
        $foro->slug = "foro-" . $request->no_foro;
        $foro->save();
        return response()->json(['message' => 'Foro actualizado'], 200);
    }
    public function destroy($slug)
    {
        $foro = Foro::Buscar($slug)->with('proyectos')->first();
        if(is_null($foro))
            return response()->json(['message'=>'Foro no encontrado'], 404);
        if($foro->activo)
            return response()->json(['message' => 'No puedes eliminar un foro activo, asegurate que sea el registro deseado'], 400);
        if ($foro->proyectos->count())
            return response()->json(['message' => 'No se puede eliminar el registro'], 400);
        $foro->delete();
        return response()->json(['message' => 'Foro eliminado'], 200);
    }
    public function configurar_foro(ConfigForoRequest $request, $slug)
    {
        $foro = Foro::Buscar($slug)->first();
        if(is_null($foro))
            return response()->json(['message'=>'Foro no encontrado'], 404);
        if (!$foro->activo)
            return response()->json(['message' => 'Foro inactivo'], 400);
        if (!$foro->inTime())
            return response()->json(['message' => 'Foro fuera de tiempo'], 400);
        $foro->lim_alumnos = $request->lim_alumnos;
        $foro->num_aulas = $request->num_aulas;
        $foro->duracion = $request->duracion;
        $foro->num_maestros = $request->num_maestros;
        $foro->save();
        return response()->json(['message' => 'Config. Registrada'], 200);
    }
    public function activar_foro(Request $request, $slug)
    {
        $request->validate(['activo' => 'required|boolean']);
        $foro = Foro::Buscar($slug)->first();
        if(is_null($foro))
            return response()->json(['message'=>'Foro no encontrado'], 404);
        $foros = Foro::Where([
            ['activo', true],
            ['slug', '!=', $slug]
        ])->get();
        if ($request->activo && !$foros->isEmpty())
            return response()->json(['message' => 'No se permite tener dos foros activos'], 400);
        if ($request->activo) {
            if (!$foro->canActivate())
                return response()->json(['message' => 'No puedes activar/desactivar un foro fuera de tiempo'], 400);
        }
        $foro->activo = $request->activo;
        $foro->save();
        $message = $request->activo ? 'Foro activado' : 'Foro desactivado';
        return response()->json(['message' => $message], 200);
    }
    public function agregar_maestro(Request $request, $slug)
    {
        $foro = Foro::Where('slug', $slug)->first();
        if(is_null($foro))
            return response()->json(['message'=>'Foro no encontrado'], 404);
        if (!$foro->activo)
            return response()->json(['message' => 'No puedes agregar maestros a un foro inactivo'], 400);
        if (!$foro->inTime())
            return response()->json(['message' => 'Foro fuera de tiempo'], 400);
        $maestro = User::Buscar($request->num_control)->first();
        if(is_null($maestro))
            return response()->json(['message'=>'Maestro no encontrado'], 404);
        if (!$maestro->hasRole('Docente'))
            return response()->json(['message' => 'El usuario que deseas agregar no tiene el rol indicado para ser maestro de taller'], 400);
        if ($request->agregar)
            $maestro->foros_users()->attach($foro);
        if (!$request->agregar)
            $maestro->foros_users()->detach($foro);
        $message = $request->agregar ? 'Maestro agregado' : 'Maestro eliminado';
        return response()->json(['message' => $message], 200);
    }
    public function proyectos(Request $request, $slug)
    {
        $proyectosTable = Proyecto::query()->select('id','aceptado','folio','participa','titulo');
        $aceptado = $request->filtro === 'Aceptados' ? true : false;
        $foro = Foro::Buscar($slug)->first();
        if(is_null($foro))
            return response()->json(['message'=>'Foro no encontrado'], 404);
        if ($request->folio)
            $proyectosTable->where('folio', 'like', '%' . $request->folio . '%');
        $proyectos = $proyectosTable->with(['jurado' => function ($query) {
            $query->select('num_control');
        }])->foro($slug)->where('aceptado', $aceptado)->paginate(7);
        $docentes = User::DatosBasicos()->UsuariosConRol('Docente')->get();        
        foreach ($docentes as $docente) {
            $docente['jurado'] = false;
        }
        return response()->json(['proyectos' => $proyectos, 'docentes' => $docentes], 200);
    }
}
