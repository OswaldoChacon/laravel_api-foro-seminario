<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Proyectos extends Model
{
    //
    public $timestamps = false;
    protected $fillable = [
        'titulo', 'empresa', 'objetivo', 'asesor'
    ];
    protected $hidden = ['id', 'lineadeinvestigacion_id', 'tipos_proyectos_id', 'foros_id', 'asesor', 'pivot'];
    
    public function asesora()
    {
        return $this->belongsTo(User::class, 'asesor');
    }
    public function lineadeinvestigacion()
    {
        return $this->belongsTo(LineasDeInvestigacion::class);
    }
    public function tipos_proyectos()
    {
        return $this->belongsTo(TiposProyectos::class);
    }
    public function foro()
    {
        return $this->belongsTo(Foros::class, 'foros_id');
    }
    public function jurado()
    {
        return $this->belongsToMany(User::class, 'jurados', 'proyecto_id', 'docente_id');
    }
    public function notificacion()
    {
        return $this->hasMany(Notificaciones::class, 'proyecto_id');
    }
    public function integrantes()
    {
        return $this->belongsToMany(User::class);
    }

    public function scopeBuscar($query, $folio)
    {
        return $query->where('folio', $folio);
    }

    public function folio(Foros $foro)
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
        return !$this->enviado;
    }

    public function enviarSolicitud()
    {
        if ($this->enviado)
            return false;
        return ($this->integrantes()->count()) === $this->notificacion()->whereHas('receptor.roles', function (Builder $query) {
            $query->where('nombre_', 'Alumno');
        })->where('respuesta', true)->count();
    }

    public function contarNotificaciones()
    {
    }

    public function respuestaAsesor()
    {
        return $this->notificacion()->whereHas('receptor.roles', function (Builder $query) {
            $query->where('nombre_', 'Docente');
        })->get()->first()->respuesta;
    }
}
