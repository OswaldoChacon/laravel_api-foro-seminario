<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class FechaForo extends Model
{
    //
    protected $table = "fechas_foros";
    public $timestamps = false;

    protected $fillable = [
        'fecha', 'hora_inicio', 'hora_termino'
    ];
    protected $hidden = [
        'id', 'foro_id'
    ];

    public function foro()
    {
        // return $this->belongsTo(Foro::class, 'foros_id');
        return $this->belongsTo(Foro::class);
    }
    public function recesos()
    {        
        // return $this->hasMany(HorarioBreak::class, 'fechaforo_id');
        return $this->hasMany(Receso::class);
    }
    // public function horario_jurado()
    // public function horarioJurado()
    public function horarios()
    {
        // return $this->hasMany('App\HorarioJurado', 'fechas_foros_id');
        // return $this->hasMany('App\HorarioJurado', 'fechaforo_id');
        return $this->hasMany(Horario::class);
    }
    public function horarioIntervalos($minutos, $what, $recesos)    
    {
        // en vez de horarioIntervalos, solo intervalos
        $aux_inicio = date('H:i', strtotime($this->hora_inicio));
        $aux_termino = date('H:i', strtotime($this->hora_termino));
        $intervalo = array();
        $posicionET = 0;
        while ($this->hora_inicio <= $this->hora_termino) {
            $newDate = strtotime('+0 hour', strtotime($this->hora_inicio));
            $newDate = strtotime('+' . $minutos . 'minute', $newDate);
            $newDate = date('H:i', $newDate);
            if ($what == 1)
                $temp = date('H:i', strtotime($this->hora_inicio)) . " - " . $newDate;
            else
                $temp = $this->fecha . " " . date('H:i', strtotime($this->hora_inicio)) . " - " . $newDate;
            $this->hora_inicio = $newDate;
            if ($newDate <= $this->hora_termino)
                array_push($intervalo, (object)[
                    'hora' => $temp,
                ]);
        }
        $this->hora_inicio = $aux_inicio;
        $this->hora_termino = $aux_termino;
        return $intervalo;
    }
}
