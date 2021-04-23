<?php

namespace App;

use App\Traits\TraitActivoAttribute;
use App\Traits\TraitCreatedAtAttribute;
use Illuminate\Database\Eloquent\Model;

class Concepto extends Model
{
    // use TraitActivoAttribute;
    use TraitCreatedAtAttribute;
    protected $fillable = [
        'nombre', 'ponderacion', 'grupo_id',
        // 'seminario'
    ];

    protected $appends = [
        'Concepto'
    ];

    function getConceptoAttribute()
    {
        $concepto = true;
        return $concepto;
    }   

    public function grupo()
    {
        return $this->belongsTo(Grupo::class);
    }
}
