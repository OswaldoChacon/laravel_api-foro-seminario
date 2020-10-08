<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TipoDeProyecto extends Model
{
    //
    public $timestamps = false;
    public $table = 'tipos_de_proyecto';
    protected $fillable = [
        'clave', 'nombre'
    ];
    protected $hidden = [
        'id'
    ];
    public function getRouteKeyName()
    {
        return 'clave';
    }

    public function proyectos()
    {
        return $this->hasMany(Proyecto::class);
    }

    // scopes
    public function scopeBuscar($query, $clave)
    {
        return $query->where('clave', $clave);
    }
}
