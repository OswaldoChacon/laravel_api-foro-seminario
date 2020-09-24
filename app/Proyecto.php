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
        // return $this->belongsTo(Foro::class, 'foro_id');
        return $this->belongsTo(Foro::class);
    }
    public function jurado()
    {
        return $this->belongsToMany(User::class, 'jurados');
    }
    public function notificaciones()
    {
        // return $this->hasMany(Notificacion::class, 'proyecto_id');
        return $this->hasMany(Notificacion::class);
    }
    public function integrantes()
    {
        return $this->belongsToMany(User::class);
    }


    // fin de relaciones

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

    // manipulacion de datos
    public function getFolio(Foro $foro)
    {
        $folio = $foro->prefijo;
        $concat_folio = $foro->proyectos()->count() + 1;
        if ($concat_folio < 10)
            $folio .= "0";
        $folio .= $concat_folio;
        return $folio;
    }

    public function editarDatos()
    {
        if ($this->aceptado && $this->permitir_cambios)
            return true;
        if (!$this->enviado && !$this->aceptado)
            return true;
        return false;
    }

    public function enviarSolicitud()
    {

        if ($this->aceptado || $this->enviado || $this->asesor_id == null)
            return false;        
            // lo de arriba esta dudoso
        if (Carbon::now()->toDateString() > $this->foro()->first()->fecha_limite)
            return false;
        return ($this->integrantes()->count()) === $this->notificaciones()->whereHas('receptor.roles', function (Builder $query) {
            $query->where('nombre_', 'Alumno');
        })->where('respuesta', true)->count();
    }
    public function cancelarSolicitud()
    {
        if ($this->aceptado)
            return false;
        if ($this->enviado)
            return true;
        return false;
    }
    public function respuestaAsesor()
    {
        return $this->notificaciones()->whereHas('receptor.roles', function (Builder $query) {
            $query->where('nombre_', 'Docente');
        })->get()->first()->respuesta;
    }
    public function inTime()
    {
        return Carbon::now()->toDateString() <= $this->foro()->first()->fecha_limite || $this->permitir_cambios;
    }
}
