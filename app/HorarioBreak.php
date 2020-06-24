<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class HorarioBreak extends Model
{
    //
    protected $table="horariobreak";
    public $timestamps=false;
    protected $fillable = [
        'hora','posicion'
    ];
    protected $hidden = ['id','fechas_foros_id'];
    public function fechas_foros()
    {
        return $this->belongsTo(Fechas_Foros::class,'fechas_foros_id');
    }
}
