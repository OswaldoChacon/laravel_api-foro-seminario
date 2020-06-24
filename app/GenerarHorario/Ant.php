<?php
namespace App\GenerarHorario;
use App\GenerarHorario\Problema; 

Class Ant{
    public $timeSlots; //creo que no funciona para naada


    public $id; //id
    public $recorrido; // 1/violaciones
    public $violaciones; //violaciones suaves entero
    public $intViolacionesDuras; //violaciones duras entero
    public $problema; //clase problema    
    public $Ai = []; // //las asignaciones
    public $Vi = []; //violaciones por Ai
    public $ViolacionesDuras = [];
    public $cListAlready = []; //asignacioones booleanas

    public function __construct($id, $problema)
    {
        $this->id =$id;
        $this->problema =$problema;   
        $this->recorrido = 0.0;
        $this->intViolacionesDuras = 0;
        $this->violaciones = 0;
    }

    public function seTcantidadDeViolaciones($countVi){
        $this->violaciones = $countVi;
    }
    public function seTcantidadDeViolacionesDuras($timeslot, $value){
        $this->ViolacionesDuras[$timeslot] = $value;
        // this.ViolacionesDuras.set(timeslot, value);
    }
    public function SetViolaciones($timeslot , $violaciones) {
        $this->Vi[$timeslot] = $violaciones;
    }
    public function setIntViolacionesDuras($value){
        $this->intViolacionesDuras = $value;
    }
    public function setRecorrido($recorrido){
        $this->recorrido = $recorrido;
    }
    public function getAi() {
        return $this->Ai;
    }

    public function getVi() {
        return $this->Vi;
    }    
}
