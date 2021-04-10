<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Concepto extends Model
{
    protected $fillable = ['grupo_id ','conceptos','ponderacion'];

    function getConceptoAttribute(){
        $concepto = true;
        return $concepto;
    }
    
    protected $appends = [
        'Concepto',
    ];
}
