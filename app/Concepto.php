<?php

namespace App;

use App\Traits\TraitActivoAttribute;
use App\Traits\TraitCreatedAtAttribute;
use Illuminate\Database\Eloquent\Model;

class Concepto extends Model
{
    // use TraitActivoAttribute;
    use TraitCreatedAtAttribute;
    protected $fillable = ['conceptos', 'ponderacion', 'grupo_id', 'seminario'];

    function getConceptoAttribute()
    {
        $concepto = true;
        return $concepto;
    }

    protected $appends = [
        'Concepto', 'canActivate'
    ];    
}
