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
            $this->eventos[] = new Eventos($evento->folio, $evento->titulo, $aux_maestro);
        }
        // dd($this->eventos);
        foreach ($this->eventos as $evento) {
            $evento->setPosibleEspaciosT($this->getEspaciosEnComun($evento));
            if ($evento->espaciosComun != null)
                $evento->setSizeComun(sizeof($evento->espaciosComun));
        }
        for ($i = 0; $i < sizeof($espaciosDeTiempo); $i++) {
            $this->timeslots[] = "$i";
        }
        $this->ordenarEventos();
        if (!$this->validarExisteEspaciosEnComun()) {
        }
    }
    public function getListMaestros()
    {
        return $this->eventos;
    }
    public function getEspaciosEnComun($evento)
    {
        global $result;
        $test = array();
        foreach ($evento->maestroList as $maestros) {
            $test[] = $maestros->horario;
        }
        // dd($test);
        if (sizeof($test) > 1) {
            $result = call_user_func_array('array_intersect', $test);
            $result = array_values($result);
        }
        return $result;
    }
    public function ordenarEventos()
    {
        $flag = true;        
        while ($flag) {
            $flag = false;
            for ($i = 0; $i < sizeof($this->eventos) - 1; $i++) {                
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
                return false;                
            }
            return true;
        }
    }
}
