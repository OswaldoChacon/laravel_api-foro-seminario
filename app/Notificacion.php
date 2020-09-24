<?php

namespace App;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class Notificacion extends Model
{
    //
    protected $table = 'notificaciones';
    public $timestamps = false;
    protected $hidden = [
        'id', 'proyecto_id', 'emisor_id', 'tipo_de_proyecto_id','receptor_id'
    ];

    protected $appends = [
        'solicitud', 'editar'
    ];

    public function emisor()
    {
        return $this->belongsTo(User::class);
    }
    public function receptor()
    {
        // return $this->belongsTo(User::class, 'receptor');
        // return $this->belongsTo(User::class, 'receptor_id');
        return $this->belongsTo(User::class);
    }
    public function proyecto()
    {
        return $this->belongsTo(Proyecto::class);
    }
    public function tipo_de_solicitud()
    {
        return $this->belongsTo(TipoDeSolicitud::class);
        // , 'tipo_solicitud'
    }
    public function nuevo_asesor()
    {
        return $this->belongsTo(User::class);
    }


    // validaciones
    public function getEditarAttribute()
    {
        if ($this->tipo_de_solicitud()->first()->nombre_ == 'REGISTRO DE PROYECTO') {
            if ($this->receptor()->whereHas('roles', function (Builder $query) {
                $query->where('nombre_', 'Alumno');
            })->count() > 0) {
                if ($this->proyecto()->first()->aceptado)
                    return false;
                else if ($this->respuesta == null || !$this->proyecto()->first()->enviado || $this->proyecto()->first()->aceptado)
                    return true;
            } else if ($this->receptor()->whereHas('roles', function (Builder $query) {
                $query->where('nombre_', 'Docente');
            })->count() > 0) {
                if ($this->proyecto()->first()->asesor_id === $this->receptor()->first()->id || $this->proyecto()->first()->asesor_id == null)
                    return true;
                if ($this->proyecto()->first()->asesor_id != $this->receptor()->first()->id)
                    return false;
            }
        } else if ($this->respuesta === null)
            return true;
        return false;
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
}
