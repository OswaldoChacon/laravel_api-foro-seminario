<?php

namespace App;

use App\Plantilla;
use App\Traits\TraitActivoAttribute;
use Illuminate\Database\Eloquent\Model;

class Grupo extends Model
{
    use TraitActivoAttribute;
    protected $fillable = ['plantilla_id', 'nombre', 'ponderacion', 'seminario'];

    protected $appends = [
        'Concepto', 'canActivate'
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
