<?php

namespace App;

use App\Plantilla;
use Illuminate\Database\Eloquent\Model;

class Grupo extends Model
{
    protected $fillable = ['plantilla_id','nombre','ponderacion'];

    public function plantilla()
    {
        return $this->belongsTo(Plantilla::class);
    }

    function getConceptoAttribute(){
        $concepto = true;
        return $concepto;
    }
    
    protected $appends = [
        'Concepto',
    ];

    
}
