<?php

namespace App\Http\Controllers\Administrador;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\GenerarHorario\Problema;
use Illuminate\Support\Facades\DB;
use App\Foro;
use App\User;
use App\FechaForo;
use App\Horario;


class HorarioController extends Controller
{

    public function jurado(Request $request)
    {
        $juradoQuery = User::query();
        $juradoQuery->select('id', 'num_control', 'prefijo', 'nombre', 'apellidoP', 'apellidoM')->whereHas('jurado_proyecto.foro', function ($query) {
            $query->where('participa', 1)->Activo();
        });
        if ($request->filtro === 'Pendientes')
            $juradoQuery->doesntHave('horarios');
        if ($request->filtro === 'Asignados')
            $juradoQuery->has('horarios');
        $foro = Foro::Activo()->first();
        if (is_null($foro))
            return response()->json(['message' => 'Foro no encontrado'], 404);
        $posicionET = 0;
        $fechas = $foro->fechas()->get();
        foreach ($fechas as $fecha) {
            $recesos = $fecha->recesos()->select('posicion')->get()->pluck('posicion')->toArray();
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
        $jurado = $juradoQuery->with('horarios:user_id,posicion')->withCount('horarios')->paginate(7);
        return response()->json(['jurado' => $jurado, 'fechas' => $fechas], 200);
    }
    public function agregar_horarioJurado_all(Request $request, $num_control)
    {
        $docente = User::Buscar($num_control)->firstOrFail();
        $fecha = FechaForo::Where('fecha', $request->fecha['fecha'])->first();
        $docente->horarios()->where('fecha_foro_id', $fecha->id)->delete();
        $foro = $fecha->foro()->first();
        if (!$foro->activo)
            return response()->json(['message' => 'Foro inactivo'], 400);
        if (!$foro->inTime())
            return response()->json(['message' => 'Foro fuera de tiempo'], 400);
        $horarios = array();
        foreach ($request->fecha['intervalos'] as $intervalo) {
            $horario = new Horario();
            $horario->user()->associate($docente);
            $horario->fechaForo()->associate($fecha);
            $horario->hora = $intervalo['hora'];
            $horario->posicion = $intervalo['posicion'];
            $horario->save();
        }
        return response()->json(['mensaje' => 'Horarios agregados'], 200);
    }
    public function eliminar_horarioJurado_all(Request $request, $num_control)
    {
        $docente = User::Buscar($num_control)->firstOrFail();
        $fecha = FechaForo::where('fecha', $request->fecha)->firstOrFail();
        $docente->horarios()->where('fecha_foro_id', $fecha->id)->delete();
        return response()->json(['mensaje' => 'Horarios eliminados'], 200);
    }
    public function agregar_horarioJurado(Request $request, $num_control)
    {
        $request->validate([
            'fecha' => 'exists:fechas_foros,fecha'
        ]);
        $docente = User::Buscar($num_control)->first();
        if (is_null($docente))
            return response()->json(['message' => 'Docente no encontrado'], 404);
        $fecha = FechaForo::Where('fecha', $request->fecha)->first();
        $horario = new Horario();
        $horario->fill($request->all());
        $horario->user()->associate($docente);
        $horario->fechaForo()->associate($fecha);
        $horario->save();
        return response()->json(['mensaje' => 'Horario agregado'], 200);
    }
    public function eliminar_horarioJurado(Request $request, $num_control)
    {
        $request->validate([
            'fecha' => 'exists:fechas_foros,fecha'
        ]);
        $docente = User::Buscar($num_control)->first();
        if (is_null($docente))
            return response()->json(['message' => 'Docente no encontrado'], 404);
        $fecha = FechaForo::Where('fecha', $request->fecha)->firstOrFail();
        $horario = Horario::Where([
            ['posicion', $request->posicion],
            ['docente_id', $docente->id],
            ['fechas_foros_id', $fecha->id]
        ])->firstOrFail();
        $horario->delete();
        return response()->json(['mensaje' => 'Horario eliminado'], 200);
    }
    public function proyectosHorarioMaestros()
    {
        $foro = Foro::Activo()->first();
        if (is_null($foro))
            return response()->json(['message' => 'Foro no encontrado'], 404);
        $horarios =    $horarios = $foro->fechas()->orderBy('fecha')->get();
        $min =  $foro->duracion;
        $minutos =  $foro->duracion;
        $longitud = count($horarios);
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
