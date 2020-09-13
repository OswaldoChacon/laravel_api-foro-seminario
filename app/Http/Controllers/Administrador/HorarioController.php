<?php

namespace App\Http\Controllers\Administrador;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\GenerarHorario\Problema;
use Illuminate\Support\Facades\DB;
use App\Foros;
use App\User;
use App\Fechas_Foros;
use App\HorarioJurado;


class HorarioController extends Controller
{   
   
    // public function asignar_jurado(){
    //     $proyectos = Foros::Where('acceso',true)->firstOrFail()->proyectos()->where('participa',1)->get();
    //     $docentes = Roles::where('nombre', 'Docente')->first()->users()->get();
    //     return response()->json(['docentes'=>$docentes,'proyectos'=>$proyectos], 200);
    // }
  

    public function jurado(Request $request)
    {
        $juradoQuery = User::query();
        $juradoQuery->select('id', 'num_control', 'prefijo', 'nombre', 'apellidoP', 'apellidoM')->whereHas('jurado_proyecto.foro', function ($query) {
            $query->where('participa', 1)->where('acceso', 1);
        });        
        // $request->filtro = 'Asignados';
        if ($request->filtro === 'Pendientes')
            $juradoQuery->doesntHave('horarios');
        if ($request->filtro === 'Asignados')
            $juradoQuery->has('horarios');

        $foro = Foros::where('acceso', true)->firstOrFail();
        // if (is_null($foro))
        //     return response()->json(['mensaje'=>'No hay foro activo'], 400);
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


        // select('id', 'num_control', DB::raw("CONCAT(prefijo,' ',nombre,' ',apellidoP,' ',apellidoM) AS nombreCompleto")) //->with('horarios')->get()->pluck('horarios.posicion')->toArray();
        // ->

        $jurado = $juradoQuery->with('horarios:docente_id,posicion')->withCount('horarios')->paginate(7);

        // $juradoData = $jurado->getCollection();
        // $juradoDataFilter = $juradoData->filter(function($value){            
        //             return count($value->horarios) > 0;
        // });        

        // $jurado->setCollection($juradoDataFilter);        
        return response()->json(['jurado' => $jurado, 'fechas' => $fechas], 200);
    }
    public function agregar_horarioJurado_all(Request $request, $num_control)
    {
        $docente = User::Buscar($num_control)->firstOrFail();
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
        $docente = User::Buscar($num_control)->firstOrFail();
        $fecha = Fechas_Foros::where('fecha', $request->fecha)->firstOrFail();
        $docente->horarios()->where('fechas_foros_id', $fecha->id)->delete();
        return response()->json(['mensaje' => 'Horarios eliminados'], 200);
    }
    public function agregar_horarioJurado(Request $request, $num_control)
    {
        $docente = User::Buscar($num_control)->firstOrFail();
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
        $user = User::Buscar($num_control)->firstOrFail();
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
