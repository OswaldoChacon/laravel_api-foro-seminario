<?php

namespace App;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class Notificacion extends Model
{
    protected $table = 'notificaciones';
    public $timestamps = false;
    protected $hidden = [
        'id', 'proyecto_id', 'emisor_id', 'tipo_de_proyecto_id', 'receptor_id', 'tipo_de_solicitud_id', 'anterior_asesor_id', 'nuevo_asesor_id'
    ];

    protected $appends = [
        'solicitud', 'editar', 'addComentarios'
    ];

    public function emisor()
    {
        return $this->belongsTo(User::class);
    }
    public function receptor()
    {
        return $this->belongsTo(User::class);
    }
    public function proyecto()
    {
        return $this->belongsTo(Proyecto::class);
    }
    public function tipo_de_solicitud()
    {
        return $this->belongsTo(TipoDeSolicitud::class);
    }
    public function nuevo_asesor()
    {
        return $this->belongsTo(User::class)->select('id', 'prefijo', 'nombre', 'apellidoP', 'apellidoM');
    }
    public function anterior_asesor()
    {
        return $this->belongsTo(User::class)->select('id', 'prefijo', 'nombre', 'apellidoP', 'apellidoM');
    }

    // validaciones    
    public function getEditarAttribute()
    {
        if ($this->tipo_de_solicitud->nombre_ == 'REGISTRO DE PROYECTO') {
            if ($this->proyecto()->first()->foro->fecha_limite < Carbon::now()->toDateString())
                return false;
            if ($this->receptor()->UsuariosConRol('Alumno')->count() > 0) {
                if ($this->proyecto->aceptado || $this->proyecto->enviado)
                    return false;
                else if ($this->respuesta == null || !$this->proyecto->enviado || $this->proyecto->aceptado)
                    return true;
            } else if ($this->receptor()->UsuariosConRol('Docente')->count() > 0) {
                if ($this->proyecto->asesor_id === $this->receptor->id || $this->proyecto->asesor_id == null)
                    return true;
                if ($this->proyecto->asesor_id != $this->receptor->id)
                    return false;
            }
        } else if ($this->respuesta === null)
            return true;
        return false;
    }
    public function getAddComentariosAttribute()
    {        
        if ($this->tipo_de_solicitud->nombre_ == 'REGISTRO DE PROYECTO' || $this->respuesta != null)
            return false;                   
        return true;        
    }
    public function getSolicitudAttribute()
    {
        return $this->tipo_de_solicitud()->first()->nombre_;
    }
    public function toArray()
    {
        $data = parent::toArray();
        if ($this->tipo_de_solicitud) {
            $data['tipo_de_solicitud'] = $this->tipo_de_solicitud()->first()->nombre_;
        } else {
            $data['tipo_de_solicitud'] = null;
        }
        return $data;
    }

    public function scopeReceptorConRol($query, $rol)
    {
        return $query->whereHas('receptor.roles', function (Builder $query) use ($rol) {
            $query->where('nombre_', $rol);
        });
    }
}
