<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Receso extends Model
{
    //
    // protected $table="horariobreak";
    // protected $table = "breaks";
    protected $table = "recesos";
    public $timestamps = false;
    protected $fillable = [
        'hora', 'posicion'
    ];
    protected $hidden = [
        'id', 'fechaforo_id'
    ];

    public function fechaForo()
    {
        // return $this->belongsTo(FechaForo::class, 'fechaforo_id');
        return $this->belongsTo(FechaForo::class);
    }
}
