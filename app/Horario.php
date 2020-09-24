<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class Horario extends Model
{
    //
    public $timestamps = false;
    // protected $table = "horario_jurado";
    protected $fillable = [
        'hora', 'posicion'
    ];
    protected $hidden = [
        'id', 'docente_id', 'fechas_foros_id'
    ];

    public function user()
    {
        // cambiar por user?
        return $this->belongsTo(User::class);
    }
    public function fechaForo()
    {
        return $this->belongsTo(FechaForo::class);
    }

    public function scopeBuscarFecha($query, $fecha)
    {
        return $query->whereHas('fechaForo',function(Builder $query) use($fecha){
            $query->where('fecha',$fecha);
        });
    }
}
