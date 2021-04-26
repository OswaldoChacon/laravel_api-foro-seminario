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
        'Concepto', 'acceso'
    ];

    public function plantilla()
    {
        return $this->belongsTo(Plantilla::class);
    }

    public function conceptos()
    {
        return $this->hasMany(Concepto::class);
    }

    public function getConceptoAttribute()
    {
        $concepto = true;
        return $concepto;
    }
    public function getAccesoAttribute()
    {        
        return $this->conceptos->sum('ponderacion') === 100 ? true : false;
    }
}
