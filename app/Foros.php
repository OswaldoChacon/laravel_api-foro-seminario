<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Foros extends Model
{
    //
    public $timestamps=false;
    protected $fillable = [
        'no_foro','nombre','periodo','anio'
    ];
    protected $hidden =['id'];

    public function user()
    {
        return $this->belongsTo('App\User');
    }
    public function users()
    {
        return $this->belongsToMany('App\User');
    }
    public function proyectos()
    {
        return $this->hasMany('App\Proyectos');
    }
    public function fechas()
    {
        return $this->hasMany(Fechas_Foros::class)->orderBy('fecha');
    }
}
