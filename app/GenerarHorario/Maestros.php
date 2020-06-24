<?php
namespace App\GenerarHorario;
class Maestros {

    public $nombre; //nombre del maestro
    public $horario = [];   //horario del maestro
    public function __construct($nombre, $horario) {
        $this->nombre = $nombre;        
        $this->horario = $horario;
    }
    
    public function setHorario($horario) {        
        $this->horario = $horario;
    }
    
    public function getName() {
        return $this->nombre;
    }        
}
