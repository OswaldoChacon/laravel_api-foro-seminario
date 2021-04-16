<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Concepto extends Model
{
    protected $fillable = ['conceptos','ponderacion','grupo_id','seminario'];

    function getConceptoAttribute(){
        $concepto = true;
        return $concepto;
    }
    
    protected $appends = [
        'Concepto',
    ];
}
