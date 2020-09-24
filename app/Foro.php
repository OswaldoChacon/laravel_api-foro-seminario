<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Foro extends Model
{
    public $timestamps = false;
    protected $fillable = [
        'no_foro', 'nombre', 'periodo', 'anio', 'fecha_limite'
    ];
    protected $hidden = [
        'id', 'user_id', //'users'
    ];

    public function getRouteKeyName()
    {
        return 'slug';
    }
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function users()
    {
        return $this->belongsToMany(User::class, 'foro_user');
    }
    public function proyectos()
    {
        return $this->hasMany(Proyecto::class);
    }
    public function fechas()
    {
        return $this->hasMany(FechaForo::class)->orderBy('fecha');
    }

    // fin de relaciones

    // inicia validaciones
    public function getPrefijo($anio,$periodo)
    {
        $prefijo = str_split($anio);
        $prefijo = $prefijo[2] . $prefijo[3];
        if ($periodo == "Agosto-Diciembre") {
            $prefijo = $prefijo . "02-";
        } else {
            $prefijo = $prefijo . "01-";
        }
        return $prefijo;
    }
    public function canActivate()
    {
        if ($this->activo || $this->inTime())
            return true;
        return false;
    }
    public function inTime()
    {
        $meses = array("ENERO", "FEBRERO", "MARZO", "ABRIL", "MAYO", "JUNIO", "JULIO", "AGOSTO", "SEPTIEMBRE", "OCTUBRE", "NOVIEMBRE", "DICIEMBRE");
        $fechas_foro = explode("-", $this->periodo);
        $mes_actual = date("m");
        if (date("Y") == $this->anio) {
            if ((array_search($fechas_foro[0], $meses) + 1) <= $mes_actual && $mes_actual <= array_search($fechas_foro[1], $meses) + 1)
                return true;
        }
        return false;
    }
    public function getDocentesAttribute()
    {
        $docentes = $this->users()->get();
        if ($this->activo) {
            $docentes = User::DatosBasicos()->UsuariosConRol('Docente')->get();
            foreach ($docentes as $docente) {
                $docente['taller'] = $docente->foros_users()->where('slug', $this->slug)->count() > 0 ? true : false;
            }
        }
        return $docentes;
    }
    // scopes    
    public function scopeActivo($query)
    {
        return $query->where('activo', true);
    }
    public function scopeBuscar($query, $slug)
    {
        return $query->where('slug', $slug);
    }
}
