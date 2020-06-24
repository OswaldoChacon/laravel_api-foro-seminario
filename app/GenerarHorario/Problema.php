<?php

namespace App\GenerarHorario;

use App\GenerarHorario\Maestros;
use App\GenerarHorario\Eventos;
use Illuminate\Support\Facades\Session;

class Problema
{
    public $eventos = []; // = new ArrayList<>();
    //Arraylist de clase maestro
    public $maestros = []; // = new ArrayList<>();
    //Arraylist entero donde se almacenan los espacios de tiempo
    public $timeslots = []; // = new ArrayList<>();

    public $timeslotsHoras = [];

    public function __construct($eventosConMaestros, $maestros_et, $espaciosDeTiempo)
    {
        $this->timeslotsHoras = $espaciosDeTiempo;
        foreach ($maestros_et as $jurado) {
            $this->maestros[] = new Maestros($jurado->nombre, $jurado->horas);
        }
        foreach ($eventosConMaestros as $evento) {         
            $aux_maestro = array();
            foreach ($this->maestros as $maestro) {                
                if (in_array($maestro->nombre, $evento->maestros))
                    $aux_maestro[] = $maestro;
                    // dd($evento,$this->maestros);
            }        
            //dd($evento);
            $this->eventos[] = new Eventos($evento->id_proyecto, $evento->titulo, $aux_maestro);           
        }        
        // dd($this->eventos);
        foreach ($this->eventos as $evento) {
            $evento->setPosibleEspaciosT($this->getEspaciosEnComun($evento));            
            if($evento->espaciosComun !=null)
            $evento->setSizeComun(sizeof($evento->espaciosComun));
        }
        // dd($this->eventos);
        // dd($espaciosDeTiempo);
        // foreach ($espaciosDeTiempo as $timeslots) {
        for ($i = 0; $i < sizeof($espaciosDeTiempo); $i++) {
            $this->timeslots[] = "$i";
        }
        // dd($this->timeslots);
        // dd("pu");
        // dd($this->timeslots);

        // dd($this->eventos);
        // dd("ppp");
        $this->ordenarEventos();
        if (!$this->validarExisteEspaciosEnComun()) { }
    }
    public function getListMaestros()
    {
        return $this->eventos;
    }
    public function getEspaciosEnComun($evento)
    {
        global $result;
        //dd($this->eventos);
        $test = array();
        // unset($this->aux_timeslot_common);

        // dd($evento->maestroList);
        foreach ($evento->maestroList as $maestros) {
            $test[] = $maestros->horario;
        }
        // dd($test);
        //   dd($evento);
        
        if (sizeof($test)>1 ) {
            $result = call_user_func_array('array_intersect', $test);
            $result = array_values($result);
        }
       
        // dd($result);
        // return $aux_timeslot_common;
        return $result;
    }
    public function ordenarEventos()
    {
        $flag = true;
        // $temp;
        while ($flag) {
            $flag = false;
            for ($i = 0; $i < sizeof($this->eventos) - 1; $i++) {
                // dd(sizeof($this->eventosOrdenados));
                if ($this->eventos[$i]->sizeComun > $this->eventos[$i + 1]->sizeComun) {
                    $temp = $this->eventos[$i];
                    $this->eventos[$i] = $this->eventos[$i + 1];
                    $this->eventos[$i + 1] = $temp;
                    $flag = true;
                }
            }
        }
    }
    public function validarExisteEspaciosEnComun()
    {
        foreach ($this->eventos as $evento) {
            if ($evento->sizeComun < 1) {
                // dd($evento);
                return false;
                // return $evento;
            }
            return true;
        }
    }
}
