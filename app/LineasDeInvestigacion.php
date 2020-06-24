<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class LineasDeInvestigacion extends Model
{
    //
    public $timestamps = false;
    public $table = "lineasdeinvestigacion";
    protected $fillable = [
        'clave','nombre'
    ];
    protected $hidden =['id'];    

    public function proyectos()
    {
        return $this->hasMany(Proyectos::class,'lineadeinvestigacion_id');
    }
}
