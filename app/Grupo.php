<?php

namespace App;

use App\Plantilla;
use App\Traits\TraitActivoAttribute;
use App\Traits\TraitCreatedAtAttribute;
use Illuminate\Database\Eloquent\Model;

class Grupo extends Model
{
    // use TraitActivoAttribute;
    use TraitCreatedAtAttribute;
    protected $fillable = ['nombre', 'ponderacion', 'seminario'];

    protected $appends = [
        'Concepto'
    ];

    public function plantilla()
    {
        return $this->belongsTo(Plantilla::class);
    }

    function getConceptoAttribute()
    {
        $concepto = true;
        return $concepto;
    }
}
