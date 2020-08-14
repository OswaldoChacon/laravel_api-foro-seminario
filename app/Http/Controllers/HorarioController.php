<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\GenerarHorario\Problema;
use Illuminate\Support\Facades\DB;
use App\Http\Requests\Fecha\RegistroFechaRequest;
use App\Http\Requests\Fecha\EditarFechaRequest;
use App\Foros;
use App\User;
use App\Fechas_Foros;
use App\HorarioJurado;
use App\HorarioBreak;
use App\Http\Requests\Fecha\BreakRequest;
use App\Jurados;
use App\Proyectos;
use App\Roles;

class HorarioController extends Controller
{
    public function agregar_fechaForo(RegistroFechaRequest $request, $slug)
    {
        $fecha = new Fechas_Foros();
        $foro = Foros::Where('slug', $slug)->firstOrFail();
        if (!$foro->acceso)
            return response()->json(['mensaje' => 'Foro no activo'], 500);
        $fecha->fill($request->all());
        $fecha->foros_id = $foro->id;
        $fecha->save();
        return response()->json(['mensaje' => 'Fecha registrada'], 200);
    }
    public function obtener_fechaForo($fecha)
    {
        $fecha = Fechas_Foros::Where('fecha', $fecha)->firstOrFail();
        return response()->json($fecha, 200);
    }
    public function actualizar_fechaForo(EditarFechaRequest $request, $fecha)
    {
        $fecha = Fechas_Foros::Where('fecha', $fecha)->firstOrFail();
        $fecha->fill($request->all());
        $fecha->save();
        return response()->json(['Success' => 'Fecha actualizada']);
    }
    public function eliminar_fechaForo($fecha)
    {
        $fecha = Fechas_Foros::Where('fecha', $fecha)->firstOrFail();
        // dd($fecha->has('receso')->delete(),$fecha->receso()->get()->flatten());        
        $fecha->delete();
        return response()->json(['Success' => 'Fecha eliminada']);
    }
    public function agregar_break(BreakRequest $request, $fecha)
    {
        $fecha = Fechas_Foros::Where('fecha', $fecha)->firstOrFail();
        $foro = $fecha->foro()->first();
        if (!$foro->acceso)
            return response()->json(['message' => 'Foro no activo'], 422);
        $receso = new HorarioBreak();
        $receso->fill($request->all());
        $receso->fechas_foros_id = $fecha->id;
        DB::table('horario_jurado')->where('posicion', $request->posicion)->delete();
        $receso->save();
        return response()->json(['mensaje' => 'Receso agregado'], 200);
    }
    public function eliminar_break(BreakRequest $request, $fecha)
    {
        $fecha = Fechas_Foros::Where('fecha', $fecha)->firstOrFail();
        $foro = $fecha->foro()->first();
        if (!$foro->acceso)
            return response()->json(['message' => 'Foro no activo'], 422);
        $receso = HorarioBreak::Where([
            ['fechas_foros_id', $fecha->id],
            ['posicion', $request->posicion]
        ])->firstOrFail();
        $receso->delete();
        return response()->json(['mensaje' => 'Break eliminado'], 200);
    }
    public function proyectos_foro(Request $request, $slug)
    {        
        $proyectosTable = Proyectos::query();
        $foro = Foros::Where('slug', $slug)->firstOrFail();
        if ($request->folio)
            $proyectosTable->where('folio', 'like', '%' . $request->folio . '%');


        $proyectos = $proyectosTable->with(['jurado' => function ($query) {
            $query->select('num_control');
        }])->where('aceptado', 1)->paginate(7);        
        // $proyectos = $foro->proyectos()->select('id', 'folio', 'titulo', 'participa')->with(['jurado' => function ($query) {
        //     $query->select('num_control');
        // }])->where('aceptado', 1)->paginate(8);

        $docentes = User::select('num_control', DB::raw("CONCAT(prefijo,' ',nombre,' ',apellidoP,' ',apellidoM) AS nombre"))->whereHas('roles', function ($query) {
            $query->where('roles.nombre_', 'Docente');
        })->get();
        foreach ($docentes as $docente) {
            $docente['jurado'] = false;
        }
        return response()->json(['proyectos' => $proyectos, 'docentes' => $docentes], 200);
    }
    public function proyecto_participa(Request $request, $folio)
    {
        $proyecto = Proyectos::Where('folio', $folio)->firstOrFail();
        $foro = $proyecto->foro()->first();
        if (!$foro->acceso)
            return response()->json(['message' => 'El foro no esta en curso para poder actualizar el proyecto'], 422);
        $proyecto->participa = $request->participa;
        $proyecto->save();
        return response()->json(['mensaje' => 'Proyecto actualizado'], 200);
    }
    // public function asignar_jurado(){
    //     $proyectos = Foros::Where('acceso',true)->firstOrFail()->proyectos()->where('participa',1)->get();
    //     $docentes = Roles::where('nombre', 'Docente')->first()->users()->get();
    //     return response()->json(['docentes'=>$docentes,'proyectos'=>$proyectos], 200);
    // }
    public function jurado()
    {
        $foro = Foros::where('acceso', true)->firstOrFail();
        // if (!is_null($foro))
        $posicionET = 0;
        $fechas = $foro->fechas()->get();
        foreach ($fechas as $fecha) {
            $recesos = $fecha->receso()->select('posicion')->get()->pluck('posicion')->toArray();
            $intervalos = $fecha->horarioIntervalos($foro->duracion, 1, $recesos);
            foreach ($intervalos as $key => $hora) {
                $hora->posicion = $posicionET;
                $hora->selected = false;                
                if (in_array($posicionET, $recesos))
                    unset($intervalos[$key]);
                $posicionET++;
            }
            $intervalos = array_values($intervalos);
            $fecha->intervalos = $intervalos;            
            $fecha->checked = false;
        }
        // $jurado = $foro->proyectos()->where('participa', 1)->with('jurado')->paginate(2)->pluck('jurado')->flatten()->unique('num_control');
        // $juradoTable = User::query();
        // $juradoTable->select('id', 'num_control', DB::raw("CONCAT(prefijo,' ',nombre,' ',apellidoP,' ',apellidoM) AS nombreCompleto"));
        // $jurado = $juradoTable->whereHas('jurado_proyecto.foro', function ($query) {
        //         $query->where('participa', 1)->where('acceso', 1);
        //     })->with('horarios:docente_id,posicion')
        //     ->paginate(7)->filter(function($value){
        //         return count($value->horarios) > 0;
        //     });

        // Paginator::make()        
        
        // $jurado
       
        // $jurado = $juradoTable->paginate(7);
        // $jurado->paginate(7);


        $jurado = User::select('id', 'num_control', DB::raw("CONCAT(prefijo,' ',nombre,' ',apellidoP,' ',apellidoM) AS nombreCompleto")) //->with('horarios')->get()->pluck('horarios.posicion')->toArray();
            ->whereHas('jurado_proyecto.foro', function ($query) {
                $query->where('participa', 1)->where('acceso', 1);
            })->with('horarios:docente_id,posicion')->withCount('horarios')            
            ->paginate(7);

        // $juradoData = $jurado->getCollection();
        // $juradoDataFilter = $juradoData->filter(function($value){            
        //             return count($value->horarios) > 0;
        // });        
        
        // $jurado->setCollection($juradoDataFilter);        
        return response()->json(['jurado' => $jurado, 'fechas' => $fechas], 200);
    }

    
    public function agregar_horarioJurado_all(Request $request, $num_control)
    {
        $docente = User::where('num_control', $num_control)->firstOrFail();
        $fecha = Fechas_Foros::Where('fecha', $request->fecha['fecha'])->firstOrFail();
        $docente->horarios()->where('fechas_foros_id', $fecha->id)->delete();
        $foro = $fecha->foro()->first();
        if (!$foro->acceso)
            return response()->json(['message' => 'Foro no activo'], 401);
        foreach ($request->fecha['intervalos'] as $intervalo) {
            $horariojurado = new HorarioJurado();
            $horariojurado->docente_id = $docente->id;
            $horariojurado->fechas_foros_id = $fecha->id;
            $horariojurado->hora = $intervalo['hora'];
            $horariojurado->posicion = $intervalo['posicion'];
            $horariojurado->fill($request->all());
            $horariojurado->save();
        }
        return response()->json(['mensaje' => 'Horarios agregados'], 200);
    }
    public function eliminar_horarioJurado_all(Request $request, $num_control)
    {
        $docente = User::where('num_control', $num_control)->firstOrFail();
        $fecha = Fechas_Foros::where('fecha', $request->fecha)->firstOrFail();
        $docente->horarios()->where('fechas_foros_id', $fecha->id)->delete();
        return response()->json(['mensaje' => 'Horarios eliminados'], 200);
    }
    public function agregar_horarioJurado(Request $request, $num_control)
    {
        $docente = User::Where('num_control', $num_control)->firstOrFail();
        $fecha = Fechas_Foros::Where('fecha', $request->fecha)->firstOrFail();
        $horariojurado = new HorarioJurado();
        $horariojurado->fill($request->all());
        $horariojurado->docente_id = $docente->id;
        $horariojurado->fechas_foros_id = $fecha->id;
        $horariojurado->save();
        return response()->json(['mensaje' => 'Horario agregado'], 200);
    }
    public function eliminar_horarioJurado(Request $request, $num_control)
    {
        $user = User::Where('num_control', $num_control)->firstOrFail();
        $fecha = Fechas_Foros::Where('fecha', $request->fecha)->firstOrFail();
        $horariojurado = HorarioJurado::Where([
            ['posicion', $request->posicion],
            ['docente_id', $user->id],
            ['fechas_foros_id', $fecha->id]
        ])->firstOrFail();
        $horariojurado->delete();
        return response()->json(['mensaje' => 'Horario eliminado'], 200);
    }
    //
    public function proyectosHorarioMaestros()
    {
        $foro = Foros::where('acceso', 1)->first();
        $horarios =    $horarios = $foro->fechas()->orderBy('fecha')->get();
        $min =  $foro->duracion;
        $minutos =  $foro->duracion;
        $longitud = count($horarios);
        // dd($longitud);
        $temp = " ";
        $temp2 = " ";
        $intervalosContainer = array();
        $testTable = array();
        $intervalosUnion = array();
        foreach ($horarios as $item) {
            $intervalosContainer[$item->fecha] = [];
        }
        $indice = 0;
        foreach ($horarios as $item) {
            $intervalo = array();
            while ($item->hora_inicio <= $item->hora_termino) {
                $intervalo[$indice] = [];
                $newDate = strtotime('+0 hour', strtotime($item->hora_inicio));
                $newDate = strtotime('+' . $minutos . 'minute', $newDate);
                $newDate = date('H:i', $newDate);
                $temp = date('H:i', strtotime($item->hora_inicio)) . " - " . $newDate;
                $item->hora_inicio = $newDate;
                if ($newDate <= $item->hora_termino) {
                    $intervalo[$indice] = $temp;
                    $intervalosContainer[$item->fecha] = $intervalo;
                }
                $indice++;
            }
        }
        foreach ($intervalosContainer as $intervaloTotal) {
            foreach ($intervaloTotal as $itemIntervaloTotal) {
                $intervalosUnion[] = $itemIntervaloTotal;
            }
        }
        $proyectos_maestros =  DB::table('jurados')->select('proyectos.folio as id_proyecto', 'proyectos.titulo', DB::raw('group_concat( Distinct users.prefijo," ",users.nombre," ",users.apellidoP," ",users.apellidoM) as maestros'))
            ->join('users', 'jurados.docente_id', '=', 'users.id')
            ->join('proyectos', 'jurados.proyecto_id', '=', 'proyectos.id')
            ->join('foros', 'proyectos.foros_id', '=', 'foros.id')
            ->where('proyectos.participa', 1)
            ->where('acceso', 1)
            ->groupBy('proyectos.titulo')
            ->get()->each(function ($query) {
                $query->maestros = explode(",", $query->maestros);
            });
        $maestro_et = DB::table('horario_jurado')
            ->select(DB::raw('group_concat(distinct users.prefijo," ",users.nombre," ",users.apellidoP," ",users.apellidoM) as nombre'), DB::raw('count(hora) as cantidad'), DB::raw('group_concat(horario_jurado.posicion) as horas'))
            ->join('users', 'horario_jurado.docente_id', '=', 'users.id')
            ->join('fechas_foros', 'horario_jurado.fechas_foros_id', '=', 'fechas_foros.id')
            ->join('foros', 'fechas_foros.foros_id', '=', 'foros.id')
            // ->join('proyectos','foros.id','=','proyectos.foros_id')
            ->where('foros.acceso', 1)
            // ->where('proyectos.participa',1)
            ->groupBy('docente_id')
            ->orderBy('cantidad')->get()->each(function ($query) {
                $query->horas = array_filter(explode(",", $query->horas), function ($value) {
                    return ($value !== null && $value !== false && $value !== '');
                });
            });
        $problema = new Problema($proyectos_maestros, $maestro_et, []);
        $proyectos = $problema->eventos;
        // dd($maestro_et);
        $cantidadProyectosMA = DB::table('jurados')->select(DB::raw('count(docente_id) as cantidad, group_concat(distinct users.prefijo," ",users.nombre," ",users.apellidoP," ",users.apellidoM) as nombre'))
            //DB::raw('group_concat(distinct docentes.prefijo," ",docentes.nombre," ",docentes.paterno," ",docentes.maternos) as nombre')
            ->join('users', 'jurados.docente_id', '=', 'users.id')
            ->join('proyectos', 'jurados.proyecto_id', '=', 'proyectos.id')
            ->where('proyectos.participa', 1)
            ->groupBy('docente_id')
            ->orderBy('cantidad')->get();
        $receso = $foro->fechas()->with('receso')->get()->pluck('receso')->flatten()->count();
        $espacios_de_tiempo = sizeof($intervalosUnion) - $receso;
        $aulas = ($foro->num_aulas * $espacios_de_tiempo);
        return view('oficina.horario.proyectos', compact('foro', 'proyectos', 'intervalosContainer', 'cantidadProyectosMA', 'aulas'));
    }
}
