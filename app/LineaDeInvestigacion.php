<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class LineaDeInvestigacion extends Model
{
    //
    public $timestamps = false;
    public $table = "lineas_de_investigacion";
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
        // return $this->hasMany(Proyecto::class, 'lineadeinvestigacion_id');
        return $this->hasMany(Proyecto::class);
    }
    public function scopeBuscar($query, $clave)
    {
        return $query->where('clave',$clave);
    }
}
