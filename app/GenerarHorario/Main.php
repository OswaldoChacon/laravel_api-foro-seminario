<?php

namespace App\GenerarHorario;

use App\GenerarHorario\Problema;
use App\GenerarHorario\Ant;

class Main
{
    public $alpha; // = 1.0;
    public $beta; // = 2.0;
    public $q; // = 50.0;
    public $rho; // = 0.1; //Evaporacion    
    public $numberIteration; // = 1; //limite de iteraciones
    public $numberOfAnts; // = 1; //hormigas usadas
    public $estancado; // = 30;
    public $t_max; // = 1 / rho;        
    public $t_minDenominador; // = 5;
    public $t_min; // = t_max / t_minDenominador;        


    public $ants = []; //List<Ant> ants = new ArrayList<>(); //hormigas creadas    
    public $cList = []; //List<Integer> cList = new ArrayList<>(); //timeslots para asignar    
    public $breaks = []; //List<Integer> breaks = new ArrayList<>(); //timeslots para especificar receso


    // Random random = new Random(); //numero random    
    public $matrizPheromoneT = array();  //matriz de asignacion de evento a Ts
    public $pheromone;
    public $Ni_et = []; //List<Double> Ni_et = new ArrayList<>();
    public $probabilidad = []; //List<Double> probabilidad = new ArrayList<>();
    public $problema; //Problema problema;
    public $eventos; //int eventos;
    public $salones; //int salones;
    public $timeslot; //int timeslot;    
    public $currentLocalBest; //Ant currentLocalBest;
    public $currentGlobalBest; //Ant currentGlobalBest;    
    public $encontroGlobal = false; //boolean encontroGlobal = false;
    public $contadorGlobal = 0; //int contadorGlobal = 0;
    public $eventosProgramados = []; //List<Eventos> eventosProgramados = new ArrayList<>();
    public $eventosProgramados2 = []; //List<Eventos> eventosProgramados2 = new ArrayList<>()
    public $eventosProgramados3 = array();

    //nuevo codigo
    public $mejoresHormigas = array();

    public $posicionEventosProgramados = array();
    public $posicionEventosProgramados2 = array();

    public $posicionAiMover = array();
    public $eventosMover = array();
    public $posicionAi = array();


    //prueba chaqueta
    public $ViolacionesSuavesTotal = array();
    public function __construct($eventosConMaestros, $maestros_et, $espaciosDeTiempo, $alpha, $beta, $q, $evaporation, $iterations, $ants, $estancado, $t_minDenominador, $num_aulas, $receso)
    {
        $this->problema = new Problema($eventosConMaestros, $maestros_et, $espaciosDeTiempo);
        $this->alpha = $alpha;
        $this->beta = $beta;
        $this->q = $q;
        $this->rho = $evaporation;
        $this->numberIteration = $iterations;
        $this->numberOfAnts = $ants;
        $this->estancado = $estancado;
        $this->t_max = 1 / $this->rho;
        $this->t_minDenominador = $t_minDenominador;
        $this->t_min = $this->t_max / $t_minDenominador;
        $this->salones = $num_aulas;
        foreach ($receso as $itemReceso) {
            $this->breaks[] = $itemReceso;
        }
        $this->pheromone = 0.0;
        $this->eventos = sizeof($this->problema->eventos);
        $this->timeslot = sizeof($this->problema->timeslots);
        $this->matrizPheromoneT = array(); //new Double[eventos][timeslot];
        $this->matrizSolucion = array(); //new String[timeslot][salones];        

        for ($i = 0; $i < $this->eventos; $i++) {
            for ($j = 0; $j < $this->timeslot; $j++) {
                $this->matrizPheromoneT[$i][$j] = $this->t_max;
            }
        }
        for ($i = 0; $i < $this->numberOfAnts; $i++) {
            $this->ants[] = new Ant($i, $this->problema);
        }

        $this->cList = $this->problema->timeslots;        
        for ($j = 0; $j < sizeof($this->cList); $j++) {
            $this->ViolacionesSuavesTotal[] = 0;
        }
        // dd($this->ViolacionesSuavesTotal);        
        foreach ($this->ants as $ant) {
            for ($j = 0; $j < sizeof($this->cList); $j++) {
                $ant->cListAlready[] = false;
                $ant->Vi[] = 0;
                $ant->ViolacionesDuras[] = 0;
                $ant->Ai[] = null;
            }
            // foreach($receso as $break){      
            if ($this->breaks != null) {
                foreach ($this->breaks as $break) {
                    $ant->cListAlready[$break] = true;
                }
            }
        }
    }
    public function start()
    {
        global $test;
        global $currentIndex;
        global $total;
        for ($i = 0; $i < sizeof($this->cList); $i++) {
            $this->Ni_et[] = 0.0;
            $this->probabilidad[] = 0.0;
        }
        for ($i = 0; $i < $this->numberIteration; $i++) {
            $this->mejoresHormigas = array();
            $this->reset();
            foreach ($this->ants as $ant) {
                for ($k = 0; $k < sizeof($this->problema->eventos); $k++) {
                    $this->CalcularProbabilidades($k, $ant);
                    $numberDouble = (float) rand() / (float) getrandmax();
                    $total = 0;
                    for ($l = 0; $l < sizeof($this->cList); $l++) {
                        $total += $this->probabilidad[$l];
                        if ($total >= $numberDouble) {
                            $currentIndex = $l;
                            $ant->Ai[] = $l; // ants.get(j).Ai.add(l);                                                                                                                
                            break;
                        }
                    }
                    $this->penalizar($ant);
                    $this->penalizarMaestro($ant, $k, "$currentIndex");
                }
                $this->penalizarEmpalmeMaestro($ant);
            }
            $this->mejorHormigaLocal();
            // dd(json_encode($this->ants));
            $this->busquedaLocal();
            // if($i == 50)
            //     dd($this->currentLocalBest->ViolacionesDuras,$i);
            // dd(json_encode($this->currentGlobalBest->Vi));
            $this->penalizarEmpalmeMaestro($this->currentLocalBest);
            $this->contarViolacionesSuaves($this->currentLocalBest);
            $this->mejorHormigaGlobal();
            $this->updathePheromoneTrails();
            $this->reiniciarTmaxAndTmin();
        }
        $this->contarViolacionesSuaves($this->currentGlobalBest);
        $this->penalizarEmpalmeMaestro($this->currentGlobalBest);
        $this->imprimirSolucion($this->currentGlobalBest);

        $this->matrizSolucion = array_combine($this->problema->timeslotsHoras, $this->matrizSolucion);
        // dd(json_encode($this->currentGlobalBest->Vi));
        // dd($this->currentGlobalBest->Ai);
        $this->disminuirViolacionesGlobal();
        if ($this->currentGlobalBest->intViolacionesDuras > 0) {
            // return response()->json()->noContent();
            dd("error global", $this->currentGlobalBest->ViolacionesDuras, $this->currentGlobalBest->Ai);
        }

    }
    public function imprimirSolucion($ant)
    {
        for ($k = 0; $k < $this->timeslot; $k++) {
            for ($i = 1; $i <= $this->salones; $i++) {
                $this->matrizSolucion[$k][$i] = null;
            }
        }
        for ($k = 0; $k < sizeof($ant->Vi); $k++) {
            $this->matrizSolucion[$k][0] = $ant->Vi[$k];
        }

        for ($k = 0; $k < sizeof($ant->Ai); $k++) {
            for ($i = 1; $i <= $this->salones; $i++) {
                //Verifico si el "salon" esta vacio para poderlo asignar, si no lo pasó al siguiente, esto solo en la matriz de solución
                if ($this->matrizSolucion[$ant->Ai[$k]][$i] == null) {
                    // dd($this->problema->eventos[$k]);
                    $this->matrizSolucion[$ant->Ai[$k]][$i] = $this->problema->eventos[$k]->id;
                    for ($j = 0; $j < sizeof($this->problema->eventos[$k]->maestroList); $j++) {
                        $this->matrizSolucion[$ant->Ai[$k]][$i] .= "," . $this->problema->eventos[$k]->maestroList[$j]->nombre;
                        // $this->matrizSolucion[$ant->Ai[$k]][$this->problema->eventos[$k]->name] = $this->problema->eventos[$k]->maestroList[$j]->nombre;                        
                    }
                    break;
                }
            }
        }
    }

    public function reiniciarTmaxAndTmin()
    {
        if ($this->encontroGlobal == false) {
            $this->contadorGlobal++;
        } else {
            $this->contadorGlobal = 0;
            // System.out.println("Se encontró un nuevo mejor global");
        }
        if ($this->contadorGlobal == $this->estancado) {
            // System.out.println("No se ha encontrado un mejor global, la busqueda se ha estancado, la matriz de feromona se ha reiniciado");
            for ($i = 0; $i < $this->eventos; $i++) {
                for ($j = 0; $j < $this->timeslot; $j++) {
                    $this->matrizPheromoneT[$i][$j] = $this->t_max;
                }
            }
        }
    }
    public function updathePheromoneTrails()
    {
        for ($i = 0; $i < $this->eventos; $i++) {
            for ($j = 0; $j < $this->timeslot; $j++) {
                $this->matrizPheromoneT[$i][$j] *= (1 - $this->rho);
            }
        }
        for ($j = 0; $j < sizeof($this->currentGlobalBest->Ai); $j++) {
            $this->matrizPheromoneT[$j][$this->currentGlobalBest->Ai[$j]] += $this->currentGlobalBest->recorrido;
        }

        if ($this->encontroGlobal) {
            for ($i = 0; $i < $this->eventos; $i++) {
                for ($j = 0; $j < $this->timeslot; $j++) {
                    if ($this->matrizPheromoneT[$i][$j] < $this->t_min) {
                        $this->matrizPheromoneT[$i][$j] = $this->t_min;
                    } else if ($this->matrizPheromoneT[$i][$j] > $this->t_max) {
                        $this->matrizPheromoneT[$i][$j] = $this->t_max;
                    } else {
                        $this->matrizPheromoneT[$i][$j] = $this->matrizPheromoneT[$i][$j];
                    }
                }
            }
        }
    }

    public function mejorHormigaGlobal()
    {
        if ($this->currentGlobalBest == null) {
            $this->currentGlobalBest = $this->currentLocalBest;
            $this->encontroGlobal = true;
            $this->t_max = 1 / $this->rho * $this->currentGlobalBest->recorrido;
            $this->t_min = $this->t_max / $this->t_minDenominador;
        } else if ($this->currentLocalBest->violaciones < $this->currentGlobalBest->violaciones) {
            $this->currentGlobalBest = $this->currentLocalBest;
            $this->encontroGlobal = true;
            $this->t_max = 1 / $this->rho * $this->currentGlobalBest->recorrido;
            $this->t_min = $this->t_max / $this->t_minDenominador;
        } else {
            $this->encontroGlobal = false;
        }
    }

    public function busquedaLocal()
    {
        //timeslot al cual el evento se va mover, es decir, timeslot actual +1
        global $timeslotMove;
        $timeslotMove = 0;
        //actual espacio de tiempo el cual pertenece
        global $currentTimeslot;
        $currentTimeslot = 0;
        //contador para saber cuantos eventos hay en el espacio de tiempo al cual se va mover
        global $contadorNextTS;
        $contadorNextTS = 0;
        //indice para poder reemplazar el valor en la lista Ai
        global $indiceAsignacionEvento;
        $indiceAsignacionEvento = 0;
        //variable de prueba
        global $contadorTest;
        $contadorTest = 0;
        //Variable para contar en el while
        global $z;
        $z = 0;

        global $indiceN2Evento;
        $indiceN2Evento = 0;

        global $nextTS;
        $nextTS = false;

        // dd($this->currentLocalBest);
        while ($this->currentLocalBest->intViolacionesDuras > 0) {
            for ($i = 0; $i < sizeof($this->currentLocalBest->ViolacionesDuras); $i++) {
                $this->eventosProgramados = array();
                $this->posicionAi = array();
                $this->eventosMover = array();
                $this->posicionAiMover = array();
                if ($this->currentLocalBest->ViolacionesDuras[$i] > 0) {
                    for ($j = 0; $j < sizeof($this->currentLocalBest->Ai); $j++) {
                        if ($this->currentLocalBest->Ai[$j] == $i) {
                            $this->eventosProgramados[] = $this->problema->eventos[$j];
                            $this->posicionAi[] = $j;
                        }
                    }
                    for ($l = 0; $l < sizeof($this->eventosProgramados); $l++) {
                        for ($m = $l + 1; $m < sizeof($this->eventosProgramados); $m++) {
                            for ($n = 0; $n < sizeof($this->eventosProgramados[$l]->maestroList); $n++) {
                                if (in_array($this->eventosProgramados[$l]->maestroList[$n], $this->eventosProgramados[$m]->maestroList)) {
                                    $this->eventosMover[] = $this->eventosProgramados[$l];
                                    $this->posicionAiMover[] = $this->posicionAi[$l];
                                    $m = sizeof($this->eventosProgramados) + 1;
                                    $n = sizeof($this->eventosProgramados[$l]->maestroList);
                                    break;
                                }
                            }
                        }
                    }
                    $this->eventosProgramados2 = array();
                    for ($m = 0; $m < sizeof($this->currentLocalBest->Ai); $m++) {
                        if ($this->currentLocalBest->Ai[$m] == $i) {
                            if (!in_array($this->problema->eventos[$m], $this->eventosMover)) {
                                $this->eventosProgramados2[] = $this->problema->eventos[$m];
                            }
                        }
                    }
                    for ($a = 0; $a < sizeof($this->eventosMover); $a++) {
                        $indiceAsignacionEvento = $this->posicionAiMover[$a];
                        $currentTimeslot = $i;
                        $timeslotMove = $currentTimeslot;
                        $nextTS = false;
                        $z = 0;
                        while ($nextTS == false) {
                            $z++;
                            if ($z > ($this->timeslot - sizeof($this->breaks) + 1)) {
                                for ($k = 0; $k < $this->timeslot; $k++) {
                                    $this->eventosProgramados = array();
                                    $this->posicionEventosProgramados = array();
                                    if ($k != $i && !in_array($k, $this->breaks)) {
                                        // $this->eventosProgramados2 = array();
                                        // for ($m = 0; $m < sizeof($this->currentLocalBest->Ai); $m++) {
                                        //     if ($this->currentLocalBest->Ai[$m] == $i) {
                                        //         if (!in_array($this->problema->eventos[$m], $this->eventosMover)) {
                                        //             $this->eventosProgramados2[] = $this->problema->eventos[$m];
                                        //         }
                                        //     }
                                        // }
                                        for ($j = 0; $j < sizeof($this->currentLocalBest->Ai); $j++) {
                                            if ($this->currentLocalBest->Ai[$j] == $k) {
                                                $this->eventosProgramados[] = $this->problema->eventos[$j];
                                                $this->posicionEventosProgramados[] = $j;
                                            }
                                        }
                                        $contadorTest = 0;
                                        for ($j = 0; $j < sizeof($this->eventosProgramados); $j++) {
                                            $contadorTest = 0;
                                            for ($m = 0; $m < sizeof($this->eventosProgramados2); $m++) {
                                                for ($l = 0; $l < sizeof($this->eventosProgramados[$j]->maestroList); $l++) {
                                                    if (in_array($this->eventosProgramados[$j]->maestroList[$l], $this->eventosProgramados2[$m]->maestroList)) {
                                                        $contadorTest++;
                                                        $m = sizeof($this->eventosProgramados2) + 1;
                                                        break;
                                                    }
                                                }
                                            }
                                            if ($contadorTest == 0) {
                                                $contadorTest2 = 0;
                                                for ($m = 0; $m < sizeof($this->eventosProgramados); $m++) {
                                                    for ($l = 0; $l < sizeof($this->eventosProgramados[$m]->maestroList); $l++) {
                                                        if ($this->eventosProgramados[$j] != $this->eventosProgramados[$m]) {
                                                            if (in_array($this->eventosProgramados[$m]->maestroList[$l], $this->eventosMover[$a]->maestroList)) {
                                                                $contadorTest2++;
                                                                $m = sizeof($this->eventosProgramados) + 1;
                                                                break;
                                                            }
                                                        }
                                                    }
                                                }
                                                if ($contadorTest2 == 0) {
                                                    $this->currentLocalBest->Ai[$this->posicionEventosProgramados[$j]] = $i;
                                                    $this->currentLocalBest->Ai[$this->posicionAiMover[$a]] = $k;
                                                    $this->penalizarEmpalmeMaestro($this->currentLocalBest);
                                                    $j = sizeof($this->eventosProgramados) + 1;
                                                    $nextTS = true;
                                                    $k = ($this->timeslot) + 1;


                                                    $this->eventosProgramados2 = array();
                                                    for ($m = 0; $m < sizeof($this->currentLocalBest->Ai); $m++) {
                                                        if ($this->currentLocalBest->Ai[$m] == $i) {
                                                            if (!in_array($this->problema->eventos[$m], $this->eventosMover)) {
                                                                $this->eventosProgramados2[] = $this->problema->eventos[$m];
                                                            }
                                                        }
                                                    }
                                                    break;
                                                }
                                            }
                                        }
                                    }
                                }
                                if ($this->currentLocalBest->ViolacionesDuras[$i] > 0 && !$nextTS) {
                                    $this->mejorHormigaLocal();
                                    $nextTS = true;
                                    $a = sizeof($this->eventosMover);
                                    $i = sizeof($this->currentLocalBest->ViolacionesDuras);
                                    // if($this->mejoresHormigas)                                   
                                }
                                // $this->currentLocalBest->intViolacionesDuras = 0;-------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
                            } else {
                                $contadorNextTS = 0;
                                $timeslotMove++;
                                if (!in_array($timeslotMove, $this->breaks)) {
                                    if ($timeslotMove == $this->timeslot)
                                        $timeslotMove = 0;
                                    $this->eventosProgramados = array();
                                    for ($j = 0; $j < sizeof($this->currentLocalBest->Ai); $j++) {
                                        if ($this->currentLocalBest->Ai[$j] == $timeslotMove) {
                                            $this->eventosProgramados[] = $this->problema->eventos[$j];
                                            $contadorNextTS++;
                                        }
                                    }
                                    if ($contadorNextTS < $this->salones) {
                                        $contadorTest = 0;
                                        for ($j = 0; $j < sizeof($this->eventosProgramados); $j++) {
                                            for ($k = 0; $k < sizeof($this->eventosMover[$a]->maestroList); $k++) {
                                                if (in_array($this->eventosProgramados[$j]->maestroList[$k], $this->eventosMover[$a]->maestroList)) {
                                                    $contadorTest++;
                                                }
                                            }
                                        }
                                        if ($contadorTest == 0) {
                                            $this->currentLocalBest->Ai[$indiceAsignacionEvento] = $timeslotMove;
                                            $this->penalizarEmpalmeMaestro($this->currentLocalBest);
                                            $nextTS = true;
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }

        $this->penalizarEmpalmeMaestro($this->currentLocalBest);
        $this->contarViolacionesSuaves($this->currentLocalBest);
        if ($this->currentLocalBest->intViolacionesDuras > 0) {
            return response()->json(['message'=>'error al generar horario'], 400);
            dd("error 436");
        } else {
            $this->eventosProgramados = array();
            $this->posicionAi = array();
            $this->eventosMover = array();
            $this->posicionAiMover = array();
            // $this->eventosProgramados2 = array();
            $contadorViolacionesSuaves = 0;
            // while ($contadorViolacionesSuaves == 30) {
                $contadorViolacionesSuaves++;
                for ($i = 0; $i < sizeof($this->currentLocalBest->Vi); $i++) {
                    if ($this->currentLocalBest->Vi[$i] > 0) {
                        $this->eventosProgramados = array();
                        $this->posicionAi = array();
                        for ($j = 0; $j < sizeof($this->currentLocalBest->Ai); $j++) {
                            if ($this->currentLocalBest->Ai[$j] == $i) {
                                // $this->eventosProgramados[] = $this->problema->eventos[$j];
                                $this->posicionAi[] = $j;
                            }
                        }
                        // dd($i,$this->currentLocalBest->Ai,$this->eventosProgramados,$this->posicionAi,$this->problema->eventos);
                        for ($t = 0; $t < sizeof($this->eventosProgramados); $t++) {
                            if ($this->eventosProgramados[$t] != $this->problema->eventos[$this->posicionAi[$t]]) {
                                dd("diferente 459", "l: " . $l, "t: " . $t, "i: " . $i, $this->currentLocalBest->Ai, $this->eventosProgramados, $this->posicionAi, $this->problema->eventos);
                            }
                        }
                        for ($l = 0; $l < sizeof($this->posicionAi); $l++) {
                            $this->eventosProgramados = array();
                            for ($t = 0; $t < sizeof($this->posicionAi); $t++) {
                                $this->eventosProgramados[] = $this->problema->eventos[$this->posicionAi[$t]];
                            }
                            if (!in_array($i, $this->eventosProgramados[$l]->espaciosComun)) {
                                // $indiceAsignacionEvento = $this->posicionAi[$l];
                                $currentTimeslot = $i;
                                $timeslotMove = $currentTimeslot;
                                $nextTS = false;
                                $z = 0;
                                while ($nextTS == false) {
                                    $z++;
                                    // dd(($this->timeslot - sizeof($this->breaks)));
                                    if ($z > ($this->timeslot - sizeof($this->breaks) + 1)) {
                                        // $this->eventosProgramados2 = array();
                                        for ($m = 0; $m < sizeof($this->currentLocalBest->Ai); $m++) {
                                            if ($this->currentLocalBest->Ai[$m] == $i) {
                                                // if (!in_array($this->problema->eventos[$m], $this->eventosMover)) {
                                                if ($this->problema->eventos[$m] != $this->eventosProgramados[$l]) {
                                                    $this->eventosProgramados2[] = $this->problema->eventos[$m];
                                                }
                                            }
                                        }
                                        for ($k = 0; $k < $this->timeslot; $k++) {
                                            $this->eventosProgramados3 = array();
                                            $this->posicionEventosProgramados = array();
                                            if ($k != $i && !in_array($k, $this->breaks)) {

                                                for ($j = 0; $j < sizeof($this->currentLocalBest->Ai); $j++) {
                                                    if ($this->currentLocalBest->Ai[$j] == $k) {
                                                        $this->eventosProgramados3[] = $this->problema->eventos[$j];
                                                        $this->posicionEventosProgramados[] = $j;
                                                    }
                                                }
                                                $contadorTest = 0;
                                                for ($j = 0; $j < sizeof($this->eventosProgramados3); $j++) {
                                                    $contadorTest = 0;
                                                    for ($m = 0; $m < sizeof($this->eventosProgramados2); $m++) {
                                                        for ($p = 0; $p < sizeof($this->eventosProgramados3[$j]->maestroList); $p++) {
                                                            if (in_array($this->eventosProgramados3[$j]->maestroList[$p], $this->eventosProgramados2[$m]->maestroList)) {
                                                                $contadorTest++;
                                                                $m = sizeof($this->eventosProgramados2) + 1;
                                                                break;
                                                            }
                                                        }
                                                    }
                                                    if ($contadorTest == 0) {
                                                        $contadorTest2 = 0;
                                                        for ($m = 0; $m < sizeof($this->eventosProgramados3); $m++) {
                                                            for ($p = 0; $p < sizeof($this->eventosProgramados3[$m]->maestroList); $p++) {
                                                                if ($this->eventosProgramados3[$j] != $this->eventosProgramados3[$m]) {
                                                                    if (in_array($this->eventosProgramados3[$m]->maestroList[$p], $this->eventosProgramados[$l]->maestroList)) {
                                                                        $contadorTest2++;
                                                                        $m = sizeof($this->eventosProgramados3) + 1;
                                                                        break;
                                                                    }
                                                                }
                                                            }
                                                        }
                                                        if ($contadorTest2 == 0) {
                                                            // if(in_array($k,$this->eventosProgramados[$l]->espaciosComun))
                                                            if (in_array($k, $this->eventosProgramados[$l]->espaciosComun) && in_array($i, $this->eventosProgramados3[$j]->espaciosComun)) 
                                                            {
                                                                // dd($i,$k,$this->posicionEventosProgramados[$j],);
                                                                $this->currentLocalBest->Ai[$this->posicionEventosProgramados[$j]] = $i;
                                                                $this->currentLocalBest->Ai[$this->posicionAi[$l]] = $k;
                                                                // $this->penalizarMaestro2($this->currentLocalBest,$i);
                                                                // $this->penalizarMaestro2($this->currentLocalBest,$k);
                                                                $j = sizeof($this->eventosProgramados3) + 1;
                                                                $nextTS = true;
                                                                $k = ($this->timeslot) + 1;
                                                                break;
                                                            }
                                                        }
                                                    }
                                                }
                                            }
                                        }
                                        $nextTS = true;
                                    } else {
                                        $contadorNextTS = 0;
                                        $timeslotMove++;
                                        if ($timeslotMove == $this->timeslot)
                                            $timeslotMove = 0;
                                        if (!in_array($timeslotMove, $this->breaks)) {
                                            $this->eventosProgramados3 = array();
                                            for ($j = 0; $j < sizeof($this->currentLocalBest->Ai); $j++) {
                                                if ($this->currentLocalBest->Ai[$j] == $timeslotMove) {
                                                    // if($this->problema->eventos[$j] != $this->eventosProgramados[$l]){
                                                    $this->eventosProgramados3[] = $this->problema->eventos[$j];
                                                    $contadorNextTS++;
                                                    // }

                                                }
                                            }
                                            if ($contadorNextTS < $this->salones) {
                                                $contadorTest = 0;
                                                for ($j = 0; $j < sizeof($this->eventosProgramados3); $j++) {
                                                    for ($k = 0; $k < sizeof($this->eventosProgramados3[$j]->maestroList); $k++) {
                                                        if (in_array($this->eventosProgramados3[$j]->maestroList[$k], $this->eventosProgramados[$l]->maestroList)) {
                                                            $contadorTest++;
                                                            break;
                                                        }
                                                    }
                                                }
                                                if ($contadorTest == 0) {
                                                    if (in_array($timeslotMove, $this->eventosProgramados[$l]->espaciosComun)) {
                                                        if (!$this->eventosProgramados[$l] != $this->problema->eventos[$this->posicionAi[$l]]) {

                                                            $backup = $this->currentLocalBest->Ai;
                                                            $this->currentLocalBest->Ai[$this->posicionAi[$l]] = $timeslotMove;
                                                            $nextTS = true;
                                                            $this->penalizarEmpalmeMaestro($this->currentLocalBest);
                                                            if ($this->currentLocalBest->intViolacionesDuras > 0) {
                                                                dd($this->currentLocalBest->ViolacionesDuras, "error 568 local", "movido a: " . $timeslotMove, "de: " . $i, $this->currentLocalBest->Vi, $this->currentLocalBest->Ai, "backup", $backup, "eventosprogramados3", $this->eventosProgramados3, $contadorNextTS, $contadorTest, "eventosprogramados l", $this->eventosProgramados[$l], $this->posicionAi[$l], "eventosprogramados", $this->eventosProgramados, $this->posicionAi);
                                                            }
                                                        }
                                                        $this->penalizarEmpalmeMaestro($this->currentLocalBest);
                                                        if ($this->currentLocalBest->intViolacionesDuras > 0) {
                                                            dd($this->currentLocalBest->ViolacionesDuras, "error 562 local", $timeslotMove, $i, $this->currentLocalBest->Vi, $this->currentLocalBest->Ai, $this->eventosProgramados3, $contadorNextTS, $contadorTest, $this->eventosProgramados[$l], $this->posicionAi[$l], $this->eventosProgramados, $this->posicionAi);
                                                        }



                                                        // dd("cambio");
                                                    }
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                        }

                        // for ($a = 0; $a < sizeof($this->eventosMover); $a++) {

                        // }
                    }
                }
            
        }
    }
    public function disminuirViolacionesGlobal()
    {

        $this->eventosProgramados = array();
        $this->posicionAi = array();
        $this->eventosMover = array();
        $this->posicionAiMover = array();        
        $contadorViolacionesSuaves = 0;
        // while ($contadorViolacionesSuaves == 30) {
            $contadorViolacionesSuaves++;
            for ($i = 0; $i < sizeof($this->currentGlobalBest->Vi); $i++) {
                if ($this->currentGlobalBest->Vi[$i] > 0) {
                    $this->eventosProgramados = array();
                    $this->posicionAi = array();
                    for ($j = 0; $j < sizeof($this->currentGlobalBest->Ai); $j++) {
                        if ($this->currentGlobalBest->Ai[$j] == $i) {
                            // $this->eventosProgramados[] = $this->problema->eventos[$j];
                            $this->posicionAi[] = $j;
                        }
                    }
                    // dd($i,$this->currentLocalBest->Ai,$this->eventosProgramados,$this->posicionAi,$this->problema->eventos);
                    for ($t = 0; $t < sizeof($this->eventosProgramados); $t++) {
                        if ($this->eventosProgramados[$t] != $this->problema->eventos[$this->posicionAi[$t]]) {
                            dd("diferente 459", "t: " . $t, "i: " . $i, $this->currentGlobalBest->Ai, $this->eventosProgramados, $this->posicionAi, $this->problema->eventos);
                        }
                    }
                    for ($l = 0; $l < sizeof($this->posicionAi); $l++) {
                        $this->eventosProgramados = array();
                        for ($t = 0; $t < sizeof($this->posicionAi); $t++) {
                            $this->eventosProgramados[] = $this->problema->eventos[$this->posicionAi[$t]];
                        }
                        if (!in_array($i, $this->eventosProgramados[$l]->espaciosComun)) {
                            // $indiceAsignacionEvento = $this->posicionAi[$l];
                            $currentTimeslot = $i;
                            $timeslotMove = $currentTimeslot;
                            $nextTS = false;
                            $z = 0;
                            while ($nextTS == false) {
                                $z++;
                                // dd(($this->timeslot - sizeof($this->breaks)));
                                if ($z > ($this->timeslot - sizeof($this->breaks) + 1)) {
                                    // $this->eventosProgramados2 = array();
                                    for ($m = 0; $m < sizeof($this->currentGlobalBest->Ai); $m++) {
                                        if ($this->currentGlobalBest->Ai[$m] == $i) {
                                            // if (!in_array($this->problema->eventos[$m], $this->eventosMover)) {
                                            if ($this->problema->eventos[$m] != $this->eventosProgramados[$l]) {
                                                $this->eventosProgramados2[] = $this->problema->eventos[$m];
                                            }
                                        }
                                    }
                                    for ($k = 0; $k < $this->timeslot; $k++) {
                                        $this->eventosProgramados3 = array();
                                        $this->posicionEventosProgramados = array();
                                        if ($k != $i && !in_array($k, $this->breaks)) {

                                            for ($j = 0; $j < sizeof($this->currentGlobalBest->Ai); $j++) {
                                                if ($this->currentGlobalBest->Ai[$j] == $k) {
                                                    $this->eventosProgramados3[] = $this->problema->eventos[$j];
                                                    $this->posicionEventosProgramados[] = $j;
                                                }
                                            }
                                            $contadorTest = 0;
                                            for ($j = 0; $j < sizeof($this->eventosProgramados3); $j++) {
                                                $contadorTest = 0;
                                                for ($m = 0; $m < sizeof($this->eventosProgramados2); $m++) {
                                                    for ($p = 0; $p < sizeof($this->eventosProgramados3[$j]->maestroList); $p++) {
                                                        if (in_array($this->eventosProgramados3[$j]->maestroList[$p], $this->eventosProgramados2[$m]->maestroList)) {
                                                            $contadorTest++;
                                                            $m = sizeof($this->eventosProgramados2) + 1;
                                                            break;
                                                        }
                                                    }
                                                }
                                                if ($contadorTest == 0) {
                                                    $contadorTest2 = 0;
                                                    for ($m = 0; $m < sizeof($this->eventosProgramados3); $m++) {
                                                        for ($p = 0; $p < sizeof($this->eventosProgramados3[$m]->maestroList); $p++) {
                                                            if ($this->eventosProgramados3[$j] != $this->eventosProgramados3[$m]) {
                                                                if (in_array($this->eventosProgramados3[$m]->maestroList[$p], $this->eventosProgramados[$l]->maestroList)) {
                                                                    $contadorTest2++;
                                                                    $m = sizeof($this->eventosProgramados3) + 1;
                                                                    break;
                                                                }
                                                            }
                                                        }
                                                    }
                                                    if ($contadorTest2 == 0) {
                                                        // if(in_array($k,$this->eventosProgramados[$l]->espaciosComun))
                                                        
                                                        if (in_array($k, $this->eventosProgramados[$l]->espaciosComun) && in_array($i, $this->eventosProgramados3[$j]->espaciosComun)) 
                                                        {
                                                            // dd($i,$k,$this->posicionEventosProgramados[$j],);
                                                            $this->currentGlobalBest->Ai[$this->posicionEventosProgramados[$j]] = $i;
                                                            $this->currentGlobalBest->Ai[$this->posicionAi[$l]] = $k;
                                                            
                                                            $j = sizeof($this->eventosProgramados3) + 1;
                                                            $nextTS = true;
                                                            $k = ($this->timeslot) + 1;
                                                            break;
                                                        }
                                                    }
                                                }
                                            }
                                        }
                                    }
                                    $nextTS = true;
                                } else {
                                    $contadorNextTS = 0;
                                    $timeslotMove++;
                                    if ($timeslotMove == $this->timeslot)
                                        $timeslotMove = 0;
                                    if (!in_array($timeslotMove, $this->breaks)) {
                                        $this->eventosProgramados3 = array();
                                        for ($j = 0; $j < sizeof($this->currentGlobalBest->Ai); $j++) {
                                            if ($this->currentGlobalBest->Ai[$j] == $timeslotMove) {
                                                // if($this->problema->eventos[$j] != $this->eventosProgramados[$l]){
                                                $this->eventosProgramados3[] = $this->problema->eventos[$j];
                                                $contadorNextTS++;
                                                // }

                                            }
                                        }
                                        if ($contadorNextTS < $this->salones) {
                                            $contadorTest = 0;
                                            for ($j = 0; $j < sizeof($this->eventosProgramados3); $j++) {
                                                for ($k = 0; $k < sizeof($this->eventosProgramados3[$j]->maestroList); $k++) {
                                                    if (in_array($this->eventosProgramados3[$j]->maestroList[$k], $this->eventosProgramados[$l]->maestroList)) {
                                                        $contadorTest++;
                                                        break;
                                                    }
                                                }
                                            }
                                            if ($contadorTest == 0) {
                                                if (in_array($timeslotMove, $this->eventosProgramados[$l]->espaciosComun)) {
                                                    if (!$this->eventosProgramados[$l] != $this->problema->eventos[$this->posicionAi[$l]]) {

                                                        $backup = $this->currentGlobalBest->Ai;
                                                        $this->currentGlobalBest->Ai[$this->posicionAi[$l]] = $timeslotMove;
                                                        $nextTS = true;
                                                        $this->penalizarEmpalmeMaestro($this->currentGlobalBest);
                                                        if ($this->currentGlobalBest->intViolacionesDuras > 0) {
                                                            dd($this->currentGlobalBest->ViolacionesDuras, "error 568 local", "movido a: " . $timeslotMove, "de: " . $i, $this->currentGlobalBest->Vi, $this->currentGlobalBest->Ai, "backup", $backup, "eventosprogramados3", $this->eventosProgramados3, $contadorNextTS, $contadorTest, "eventosprogramados l", $this->eventosProgramados[$l], $this->posicionAi[$l], "eventosprogramados", $this->eventosProgramados, $this->posicionAi);
                                                        }
                                                    }
                                                    $this->penalizarEmpalmeMaestro($this->currentGlobalBest);
                                                    if ($this->currentGlobalBest->intViolacionesDuras > 0) {
                                                        dd($this->currentGlobalBest->ViolacionesDuras, "error 562 local", $timeslotMove, $i, $this->currentGlobalBest->Vi, $this->currentGlobalBest->Ai, $this->eventosProgramados3, $contadorNextTS, $contadorTest, $this->eventosProgramados[$l], $this->posicionAi[$l], $this->eventosProgramados, $this->posicionAi);
                                                    }
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
        // }
    }
    public function contarViolacionesSuaves($ant)
    {
        global $count;
        $count = 0;
        for ($i = 0; $i < sizeof($ant->Vi); $i++) {
            $ant->Vi[$i] = 0;
        }

        $ant->Vi[0] = 0;
        for ($j = 0; $j < $this->eventos; $j++) {
            global $encontrado;
            $encontrado = false;
            global $newValue;
            $newValue = 0;
            global $timeloslot;
            $timeloslot = 0;
            //dd($ant,$ant->Ai[$j],$this->problema->eventos[$j]->espaciosComun,$this->eventos);
            //for (int k = 0; k < indice; k++) {
            // if ($this->problema->eventos[$j]->espaciosComun . contains(ant . Ai . get(j))) {
            if (in_array($ant->Ai[$j], $this->problema->eventos[$j]->espaciosComun)) {
                $encontrado = true;
                //break;
            } else {
                $encontrado = false;
                $timeloslot = $ant->Ai[$j];
            }
            if ($encontrado == false) {
                $newValue = $ant->Vi[$timeloslot] + 1;
                $ant->SetViolaciones($timeloslot, $newValue);
            }
        }
        for ($i = 0; $i < sizeof($ant->Vi); $i++) {
            $count += $ant->Vi[$i];
        }
        $ant->seTcantidadDeViolaciones($count);
    }

    public function penalizarEmpalmeMaestro($ant)
    {
        global $count;
        $count = 0;
        global $contadorMaestroEmpalme;
        $ContadorMaestroEmpalme = 0;
        global $contadorEspacioDeTiempo;
        $contadorEspacioDeTiempo = 0;
        // List<String> maestros = new ArrayList<String>();
        global $maestros;
        $maestros = [];

        global $testerSizeOf;
        // for (int i = 0; i < timeslot; i++) {
        for ($i = 0; $i < $this->timeslot; $i++) {
            // eventosProgramados.clear();
            // unset($this->eventosProgramados);
            $this->eventosProgramados = array();
            unset($maestros);
            //Agrego los proyectos asignaods a un espacio de tiempo en especifico
            // for (int j = 0; j < ant.Ai.size(); j++) {
            for ($j = 0; $j < sizeof($ant->Ai); $j++) {
                if ($ant->Ai[$j] == $i) {
                    $this->eventosProgramados[] = $ant->problema->eventos[$j];
                }
            }
            if (sizeof($this->eventosProgramados) > 1) {
                $contadorEspacioDeTiempo = 0;
                for ($l = 0; $l < sizeof($this->eventosProgramados); $l++) {
                    if (in_array($i, $this->eventosProgramados[$l]->espaciosComun)) {
                        $contadorEspacioDeTiempo++;
                        //break;                        
                    }
                }
                $ContadorMaestroEmpalme = 0;
                for ($l = 0; $l < sizeof($this->eventosProgramados); $l++) {
                    for ($m = $l + 1; $m < sizeof($this->eventosProgramados); $m++) {
                        for ($n = 0; $n < sizeof($this->eventosProgramados[$l]->maestroList); $n++) {
                            for ($o = 0; $o < sizeof($this->eventosProgramados[$m]->maestroList); $o++) {
                                if ($this->eventosProgramados[$l]->maestroList[$n]->nombre == $this->eventosProgramados[$m]->maestroList[$o]->nombre) {
                                    $ContadorMaestroEmpalme++;
                                    $n = $o = sizeof($this->eventosProgramados[$m]->maestroList) + 1;
                                }
                            }
                        }
                    }
                }
                $ant->seTcantidadDeViolacionesDuras($i, $ContadorMaestroEmpalme);
            } else {
                $ant->seTcantidadDeViolacionesDuras($i, 0);
            }
        }
        foreach ($ant->ViolacionesDuras as $j) {
            $count += $j;
        }
        $ant->setIntViolacionesDuras($count);
    }

    public function penalizarMaestro2($ant, $timeslot)
    {
        $proyectos = array();
        $ant->Vi[$timeslot] = 0;
        for ($i = 0; $i < sizeof($ant->Ai); $i++) {
            if ($i == $timeslot) {
                $proyectos[] = $this->problema->eventos[$i];
            }
        }
        // dd($proyectos);
        for ($i = 0; $i < sizeof($proyectos); $i++) {
            if (!in_array($timeslot, $proyectos[$i]->espaciosComun)) {
                $newValue = $ant->Vi[$timeslot] + 1;
                $ant->SetViolaciones($timeslot, $newValue);
            }
        }
    }
    public function penalizarMaestro($ant, $evento, $timeslot)
    {
        global $count;
        $count = 0;
        $newValue = 0;
        // $indice = $ant->problema->eventos[$evento]->sizeComun;
        // for ($i = 0; $i < $indice; $i++) {
        //     if ($ant->problema->eventos[$evento]->espaciosComun[$i] == $timeslot) {
        //         $encontrado = true;
        //     }
        // }
        if (!in_array($timeslot, $ant->problema->eventos[$evento]->espaciosComun)) {
            $newValue = $ant->Vi[$timeslot] + 1;
            $ant->SetViolaciones($timeslot, $newValue);

            $this->ViolacionesSuavesTotal[$timeslot] + 1;
        }
        // else{
        //     $newValue = $ant->Vi[$timeslot] - 1;
        //     $ant->SetViolaciones($timeslot, $newValue);
        // }
        foreach ($ant->Vi as $j) {
            $count += $j;
        }
        $ant->seTcantidadDeViolaciones($count);
    }

    public function penalizar($ant)
    {
        // dd($ant);
        global $contadorTime;
        for ($i = 0; $i < sizeof($ant->Ai); $i++) {
            for ($j = 0; $j < sizeof($ant->Ai); $j++) {
                // if (ants.get(ant).Ai.get(i).equals(ants.get(ant).Ai.get(j))) {
                if ($ant->Ai[$i] == $ant->Ai[$j]) {
                    $contadorTime += 1;
                }
                if ($contadorTime >= $this->salones) {
                    // ants.get(ant).cListAlready.set(ants.get(ant).Ai.get(i), true);
                    $ant->cListAlready[$ant->Ai[$i]] = true; //.set(ants.get(ant).Ai.get(i), true);
                    // dd("aqui se ve el true ",$ant->cListAlready,"hormigas",$this->ants);
                }
                if ($contadorTime > $this->salones) {
                    // int newValue = contadorTime - salones;
                    $newValue = $contadorTime - $this->salones;
                    // ants.get(ant).Vi.set(ants.get(ant).Ai.get(i), newValue);
                    $ant->Vi[$ant->Ai[$i]] = $newValue; //.set(ants.get(ant).Ai.get(i), newValue);
                }
            }
            $contadorTime = 0;
        }
    }
    public function CalcularProbabilidades($evento, $ant)
    {
        $this->pheromone = 0.0;
        for ($l = 0; $l < sizeof($this->cList); $l++) {
            // Ni_et.set(l, (1 / (1.0 + ants.get(ant).Vi.get(l))));
            // $this->Ni_et[$l] = (1 / (1.0 + $ant->Vi[$l]));
            $this->Ni_et[$l] = (1 / (1.0 + $this->ViolacionesSuavesTotal[$l]));
        }
        // evitar receso CalcularProbabilidades
        if ($this->breaks != null) {
            foreach ($this->breaks as $break) {
                $this->Ni_et[$break] = 0.0;
            }
        }
        for ($l = 0; $l < sizeof($this->cList); $l++) {
            if (!$ant->cListAlready[$l]) {
                $this->pheromone += pow($this->matrizPheromoneT[$evento][$l], $this->alpha) * pow($this->Ni_et[$l], $this->beta);
            }
        }
        for ($l = 0; $l < sizeof($this->cList); $l++) {
            if ($ant->cListAlready[$l]) {
                $this->probabilidad[$l] = 0.0; //.set(l, 0.0);                                
            } else {
                $numerador = pow($this->matrizPheromoneT[$evento][$l], $this->alpha) * pow($this->Ni_et[$l], $this->beta);
                $this->probabilidad[$l] = $numerador / $this->pheromone; //.set(l, (numerador / pheromone));
            }
        }
    }


    public function reset()
    {
        // for (int i = 0; i < ants.size(); i++) {
        foreach ($this->ants as $ant) {
            // $ant->Ai ();            
            unset($ant->Ai);
            for ($j = 0; $j < sizeof($this->cList); $j++) {
                // $ant->Ai [$j] =null;
                $ant->cListAlready[$j] = false; //.set(j, false);
                $this->probabilidad[$j] = 0.0;
            }
            foreach ($this->breaks as $break) {
                // dd($this->receso);
                $ant->cListAlready[$break] = true;
                $this->Ni_et[$break] = 0.0;
                // $this->probabilidad[$break->posicion] = 0.0; //.set(l, 0.0);                             
            }
        }
        // dd($this->ants);
    }

    public function mejorHormigaLocal()
    {
        global $recorrido;
        $recorrido = 0.0;
        global $count;
        $count = 0;
        global $menor;
        global $current;
        //int countHard = 0;        
        $menor = $this->ants[0]->violaciones;

        for ($i = 0; $i < sizeof($this->ants); $i++) {
            $count = $this->ants[$i]->violaciones;
            if ($count <= $menor && !in_array($this->ants[$i], $this->mejoresHormigas)) {
                $menor = $count;
                // $current = i;
                $this->currentLocalBest = $this->ants[$i];
            }
        }
        array_push($this->mejoresHormigas, $this->currentLocalBest);
        $recorrido = $this->q / (1 + $menor);
        $this->currentLocalBest->setRecorrido($recorrido);
    }
}
