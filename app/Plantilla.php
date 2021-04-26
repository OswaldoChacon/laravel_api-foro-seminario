<?php

namespace App;

use App\Grupo;
use App\Traits\TraitActivoAttribute;
use App\Traits\TraitCreatedAtAttribute;
use Illuminate\Database\Eloquent\Model;

class Plantilla extends Model
{
    use TraitActivoAttribute, TraitCreatedAtAttribute;
    // protected $table = "plantilla";
    protected $fillable = ['nombre'];

    protected $appends = [
        'Grupo',  
        'acceso',
        'canActivate'
    ];

    public function grupos()
    {
        return $this->hasMany(Grupo::class);
    }

    public function getAccesoAttribute()
    {
        return $this->grupos->sum('ponderacion') === 100 ? true : false;
    }

    function getGrupoAttribute()
    {
        $grupo = true;
        return $grupo;
    }
}
