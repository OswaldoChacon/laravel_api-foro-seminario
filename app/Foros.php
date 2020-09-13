<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Foros extends Model
{
    //
    public $timestamps = false;
    protected $fillable = [
        'no_foro', 'nombre', 'periodo', 'anio'
    ];
    protected $hidden = ['id','user_id','users'];
    // protected $appends = ['maestros'];

    // public function getMaestrosAttribute()
    // {        
    //     return $this->users()->get();
    // }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function users()
    {
        return $this->belongsToMany(User::class,'foros_user');
    }
    public function proyectos()
    {
        return $this->hasMany(Proyectos::class);
    }
    public function fechas()
    {
        return $this->hasMany(Fechas_Foros::class)->orderBy('fecha');
    }
    public function canActivate()
    {
        if ($this->acceso)
            return true;
        $meses = array("Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio", "Julio", "Agosto", "Septiembre", "Octubre", "Noviembre", "Diciembre");
        $fechas_foro = explode("-", $this->periodo);
        $mes_actual = date("m");
        if (date("Y") == $this->anio) {
            if (array_search($fechas_foro[0], $meses) + 1 <= $mes_actual && $mes_actual <= array_search($fechas_foro[1], $meses) + 1)
                return true;
        }
        return false;
    }

    public function scopeActivo($query)
    {
        return $query->where('acceso', true);
    }

    public function scopeBuscar($query, $slug)
    {
        return $query->where('slug',$slug);
    }
}
