<?php

namespace App;

use App\Grupo;
use Illuminate\Database\Eloquent\Model;

class Plantilla extends Model
{
    protected $table = "plantilla";
    protected $fillable = ['nombre'];
    
    public function grupos()
    {
        return $this->hasMany(Grupo::class);
    }

    function getGrupoAttribute(){
        $grupo = true;
        return $grupo;
    }
    
    protected $appends = [
        'Grupo',
    ];
}
