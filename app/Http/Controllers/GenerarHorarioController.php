<?php

namespace App\Http\Controllers;

use App\Foro;
use App\Proyecto;
use App\GenerarHorario\Main;
use App\GenerarHorario\Problema;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Http\Requests\GenerarHorarioRequest;
use Illuminate\Pagination\LengthAwarePaginator;

class GenerarHorarioController extends Controller
{
    //
    public function generarHorario(GenerarHorarioRequest $request)
    {
        $proyectos = Proyecto::where('participa', true)->get();
        $foro = Foro::Activo(true)->first();
        if (is_null($foro))
            return response()->json(['message' => 'No puedes generar el horario. No hay ningún foro activo'], 400);
        $receso = $foro->fechas()->with('recesos')->get()->pluck('recesos')->flatten()->map(function ($receso) {
            return $receso->posicion;
        });
        // $receso = DB::table('recesos')->select('recesos.posicion')
        // ->join('fechas_foros', 'recesos.fecha_foro_id', '=', 'fechas_foros.id')
        // ->join('foros', 'fechas_foros.foro_id', '=', 'foros.id')
        // ->where('foros.activo', 1)->get()->toArray();        
        $proyectos_maestros = DB::table('jurados')->select('proyectos.folio', 'proyectos.titulo', DB::raw('group_concat( Distinct users.prefijo," ",users.nombre," ",users.apellidoP," ",users.apellidoM) as maestros'))
            ->join('users', 'jurados.user_id', '=', 'users.id')
            ->join('proyectos', 'jurados.proyecto_id', '=', 'proyectos.id')
            ->where('proyectos.participa', 1)
            ->groupBy('proyectos.titulo')
            ->get()->each(function ($query) {
                $query->maestros = explode(",", $query->maestros);
            });
        $maestro_et = DB::table('horarios')
            ->select(DB::raw('group_concat(distinct users.prefijo," ",users.nombre," ",users.apellidoP," ",users.apellidoM) as nombre'), DB::raw('count(hora) as cantidad'), DB::raw('group_concat(horarios.posicion) as horas'))
            ->join('users', 'horarios.user_id', '=', 'users.id')
            ->join('fechas_foros', 'horarios.fecha_foro_id', '=', 'fechas_foros.id')
            ->join('foros', 'fechas_foros.foro_id', '=', 'foros.id')
            ->where('foros.activo', 1)
            ->groupBy('horarios.user_id')
            ->orderBy('cantidad')->get()->each(function ($query) {
                $query->horas = array_filter(explode(",", $query->horas), function ($value) {
                    return ($value !== null && $value !== false && $value !== '');
                });
            });

        $horarios = DB::table('fechas_foros')
            ->select('hora_inicio as inicio', 'hora_termino as termino', 'fecha', 'fechas_foros.id')
            ->join('foros', 'fechas_foros.foro_id', '=', 'foros.id')
            ->where('foros.activo', 1)
            ->get();
        $min = DB::table('foros')->select('duracion as minutos')->where('activo', '=', 1)->get();
        $minutos = $min[0]->minutos;
        $longitud = count($horarios);
        $temp = " ";
        $temp2 = " ";
        $intervalosContainer = array();
        $testTable = array();
        foreach ($horarios as $item) {
            $intervalo = array();
            while ($item->inicio <= $item->termino) {
                $newDate = strtotime('+0 hour', strtotime($item->inicio));
                $newDate = strtotime('+' . $minutos . 'minute', $newDate);
                $newDate = date('H:i:s', $newDate);
                $temp = $item->fecha . " " . $item->inicio . " - " . $newDate;
                $item->inicio = $newDate;
                if ($newDate > $item->termino) {
                } else {
                    array_push($intervalo, $temp);
                }
            }
            $testTable[] = $intervalo[sizeof($intervalo) - 1];
            array_push($intervalosContainer, $intervalo);
        }
        $intervalosUnion = array();
        foreach ($intervalosContainer as $intervaloTotal) {
            foreach ($intervaloTotal as $itemIntervaloTotal) {
                $intervalosUnion[] = $itemIntervaloTotal;
            }
        }
        $salones = $foro->num_aulas;

        $cantidadMaestro_Jurado = DB::select('SELECT jurados.* FROM jurados inner join proyectos on jurados.proyecto_id=proyectos.id INNER join foros on proyectos.foro_id=foros.id where foros.activo=1 and proyectos.participa=1 group by jurados.user_id');
        $horarioDocentes = DB::select('SELECT horarios.* FROM `horarios` inner join fechas_foros on horarios.fecha_foro_id=fechas_foros.id inner join foros on fechas_foros.foro_id=foros.id inner join jurados on horarios.user_id=jurados.user_id inner join proyectos on jurados.proyecto_id=proyectos.id where foros.activo=1 and proyectos.participa=1 group by user_id');
        $cantidadDeET = count($intervalosUnion) * $salones;
        if (sizeof($horarioDocentes) != sizeof($cantidadMaestro_Jurado) || $cantidadDeET < sizeof($proyectos_maestros)) {
            return response()->json(['message' => 'No hay suficientes espacios de tiempo para asignarle a los proyectos o algún maestro no se le ha asignado su horario'], 400);
            // return response()->noContent();
        }
        $main = new Main($proyectos_maestros, $maestro_et, $intervalosUnion, $request->alpha, $request->beta, $request->Q, $request->evaporation, $request->iterations, $request->ants, $request->estancado,  $request->t_minDenominador, $salones, $receso);

        // maestros con cantidad de proyectos como jurado
        $cantidadProyectosMA = DB::table('jurados')->select(DB::raw('count(user_id) as cantidad, group_concat(distinct users.prefijo," ",users.nombre," ",users.apellidoP," ",users.apellidoM) as nombre'))
            //DB::raw('group_concat(distinct docentes.prefijo," ",docentes.nombre," ",docentes.paterno," ",docentes.maternos) as nombre')
            ->join('users', 'jurados.user_id', '=', 'users.id')
            ->join('proyectos', 'jurados.proyecto_id', '=', 'proyectos.id')
            ->where('proyectos.participa', 1)
            ->groupBy('user_id')
            ->orderBy('cantidad')->get();

        // maestros con cantidad de ET
        $cantidadETMaestros = DB::select('select horarios.user_id, count(hora) as cantidad from horarios,users,fechas_foros,foros where horarios.user_id = users.id and horarios.fecha_foro_id = fechas_foros.id and fechas_foros.foro_id = foros.id and foros.activo = 1 group by horarios.user_id order by cantidad asc');
        $maestro_foro = $foro->num_maestros;
        if ($main->problema->eventos[0]->sizeComun == 0) {
            return response()->json(['message' => 'Algún proyecto no tiene espacios en común'], 400);
            // return response()->noContent();
        }

        foreach ($main->problema->eventos as $evento) {
            $maestro_evento = sizeof($evento->maestroList);
            if ($maestro_evento < $maestro_foro) {
                return response()->json(['message' => 'Algún proyecto le faltan miembros del jurado'], 400);
                // return response()->noContent();
            }
        }




        $main->start();
        $matrizSolucion = $main->matrizSolucion;
        $resultado_aux = array();
        $resultadoItem = array();
        $resultado = array();
        $resul = array();
        foreach ($matrizSolucion as $key => $items) {
            for ($i = 0; $i < sizeof($items); $i++) {
                $resul[$i] = [];
            }
            foreach ($items as $keyItems => $item) {
                unset($aux);
                $aux = array_filter(explode(",", $item), function ($value) {
                    return ($value !== null && $value !== false && $value !== '');
                });
                $resul[$keyItems] = $aux;
            }
            $resultado_aux[$key] = $resul;
            unset($resul);
        }
        $indice = 0;
        $tituloLlave = array();
        foreach ($resultado_aux as $key => $item) {
            $tituloLlave = array();
            foreach ($item as $keyItem => $itemItems) {
                if (sizeof($itemItems) > 1) {
                    $temporalLlave = $itemItems[0];
                    unset($itemItems[0]);
                    $tituloLlave[$temporalLlave] = $itemItems;
                } else {
                    $tituloLlave[$keyItem] = $itemItems;
                }
            }
            if ($key == $testTable[$indice]) {
                $resultadoItem[str_replace($horarios[$indice]->fecha, '', $key)] = $tituloLlave;
                $resultado[$horarios[$indice]->fecha] = $resultadoItem;
                $indice += 1;
                $resultadoItem = array();
            } else {
                $resultadoItem[str_replace($horarios[$indice]->fecha, '', $key)] = $tituloLlave;
            }
        }
        return response()->json($resultado, 200);
    }


    // hormiga
    public function proyectosMaestros()
    {
        $foro = Foro::Activo(true)->first();
        if (is_null($foro))
            return response()->json(['message' => 'No hay ningún foro activo.'], 404);
        $proyectosTable = Proyecto::query();
        $proyectos = $foro->proyectos()->where('participa', 1)->with('jurado:users.id,prefijo,nombre,apellidoP,apellidoM')->get(['id', 'folio', 'titulo']);
        foreach ($proyectos as $proyecto) {
            $proyecto->append('EspaciosDeTiempoEnComun');
        }
        $proyectos = collect($proyectos->sortBy('EspaciosDeTiempoEnComun')->values()->all());
        $page = 1;
        $perPage = 7;

        $proyectos = new LengthAwarePaginator(
            $proyectos->forPage($page, $perPage),
            $proyectos->count(),
            $perPage,
            $page,
            ['path' => url('api/proyectos_maestros')]
        );
        return response()->json($proyectos, 200);
        // return response()->json($proyectos, 200);
    }
}
