<?php

namespace App;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;


class Proyecto extends Model
{
    //
    public $timestamps = false;
    protected $fillable = [
        'titulo', 'empresa', 'objetivo'
    ];
    protected $hidden = [
        'id', 'linea_de_investigacion_id', 'tipo_de_proyecto_id', 'foro_id', 'asesor_id', 'pivot'
    ];
    public function getRouteKeyName()
    {
        return 'folio';
    }
    public function asesor()
    {
        return $this->belongsTo(User::class, 'asesor_id');
    }
    public function linea_de_investigacion()
    {
        return $this->belongsTo(LineaDeInvestigacion::class);
    }
    public function tipo_de_proyecto()
    {
        return $this->belongsTo(TipoDeProyecto::class);
    }
    public function foro()
    {
        return $this->belongsTo(Foro::class);
    }
    public function jurado()
    {
        return $this->belongsToMany(User::class, 'jurados');
    }
    public function notificaciones()
    {
        return $this->hasMany(Notificacion::class);
    }
    public function integrantes()
    {
        return $this->belongsToMany(User::class);
    }

    // validaciones    
    public function outTime()
    {
        return false;
        // return Carbon::now()->toDateString() > $this->foro->fecha_limite;
    }
    public function todosAceptaron()
    {
        return ($this->integrantes()->count()) === $this->notificaciones()->ReceptorConRol('Alumno')->where('respuesta', true)->count();
    }
    public function respuestaAsesor()
    {
        return $this->notificaciones()->ReceptorConRol('Docente')->get()->first()->respuesta;
    }
    
    // getters
    public function getInTimeAttribute()
    {
        return Carbon::now()->toDateString() <= $this->foro->fecha_limite || $this->permitir_cambios;
    }
    public function getEditarAttribute()
    {
        if ($this->outTime())
            return false;
        if ($this->aceptado && $this->permitir_cambios)
            return true;
        if (!$this->enviado && !$this->aceptado)
            return true;
        return false;
    }
    public function getEnviarAttribute()
    {
        if ($this->outTime())
            return false;
        if ($this->aceptado || $this->enviado || $this->asesor_id == null)
            return false;
        return $this->todosAceptaron();
    }
    public function getCancelarAttribute()
    {
        if ($this->outTime())
            return false;
        if ($this->aceptado)
            return false;
        if ($this->enviado)
            return true;
        return false;
    }
    public function getEspaciosDeTiempoEnComunAttribute()
    {
        $jurado = $this->jurado()->with('horarios')->get();
        $horariosEnComun = array();
        foreach ($jurado as $maestro) {
            $maestro->horarios = $maestro->horarios->map(function ($item) {
                return $item->posicion;
            });
        }
        foreach ($jurado as $maestro) {
            $horariosEnComun[] = $maestro->horarios->toArray();
        }
        $result = array();
        if (sizeof($horariosEnComun) > 1) {
            $result = call_user_func_array('array_intersect', $horariosEnComun);
            $result = array_values($result);
        }
        return sizeof($result);
    }
    public function getFolio(Foro $foro)
    {
        $folio = $foro->prefijo;
        $concatFolio = $foro->proyectos()->count() + 1;        
        if ($concatFolio < 10)
            $folio .= "0";
        $folio .= $concatFolio;
        return $folio;
    }
    public function getIdsNotificaciones()
    {
        return $this->notificaciones()->ReceptorConRol('Alumno')->get()->pluck('id')->flatten();
    }

    // scopes
    public function scopeForo($query, $slug)
    {
        return $query->whereHas('foro', function (Builder $query) use ($slug) {
            $query->where('slug', $slug);
        });
    }
    public function scopeBuscar($query, $folio)
    {
        return $query->where('folio', $folio);
    }
}
