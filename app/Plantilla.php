<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Plantilla extends Model
{
    protected $table = "plantilla";
    protected $fillable = ['nombre'];
    
    function getGrupoAttribute(){
        $grupo = true;
        return $grupo;
    }
    
    protected $appends = [
        'Grupo',
    ];
}
