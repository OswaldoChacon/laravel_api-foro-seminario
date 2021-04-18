<?php

namespace App;

use App\Grupo;
use App\Traits\TraitActivoAttribute;
use Illuminate\Database\Eloquent\Model;

class Plantilla extends Model
{
    use TraitActivoAttribute;
    // protected $table = "plantilla";
    protected $fillable = ['nombre'];

    protected $appends = [
        'Grupo',  'canActivate'
    ];

    public function grupos()
    {
        return $this->hasMany(Grupo::class);
    }

    function getGrupoAttribute()
    {
        $grupo = true;
        return $grupo;
    }  
}
