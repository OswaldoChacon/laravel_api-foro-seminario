<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TiposProyectos extends Model
{
    //
    public $timestamps = false;
    public $table = 'tipos_de_proyectos';
    protected $fillable = [
        'clave','nombre'
    ];
    protected $hidden = ['id'];
    public function proyectos()
    {
        return $this->hasMany(Proyectos::class);
    }
}
