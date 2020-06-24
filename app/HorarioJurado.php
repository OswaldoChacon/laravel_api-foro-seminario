<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class HorarioJurado extends Model
{
    //
    public $timestamps=false;
    protected $table="horario_jurado";
    protected $fillable = [
        'hora','posicion'
    ];
    protected $hidden = ['id','docente_id','fechas_foros_id'];
    public function docente()
    {
        return $this->belongsTo(User::class);
    }
    public function fechas_foros()
    {
        return $this->belongsTo(Fechas_Foros::class);
    }
}
