<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class Horario extends Model
{
    //
    public $timestamps = false;    
    protected $fillable = [
        'hora', 'posicion'
    ];
    protected $hidden = [
        'id', 'user_id', 'fecha_foro_id'
    ];

    public function user()
    {        
        return $this->belongsTo(User::class);
    }
    public function fechaForo()
    {
        return $this->belongsTo(FechaForo::class);
    }

    // scopes
    public function scopeBuscarFecha($query, $fecha)
    {
        return $query->whereHas('fechaForo',function(Builder $query) use($fecha){
            $query->where('fecha',$fecha);
        });
    }
}
