<?php

namespace App;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class Notificaciones extends Model
{
    //
    public $timestamps = false;
    protected $hidden = ['id', 'proyecto_id', 'emisor'];

    protected $appends = [
        'solicitud',
        'editar'
    ];

    public function getEditarAttribute()
    {
        if ($this->tipo_solicitud()->first()->nombre_ == 'REGISTRO DE PROYECTO') {
            if ($this->receptor()->whereHas('roles', function (Builder $query) {
                $query->where('nombre_', 'Alumno');
            })->count() > 0) {
                // dd($this->proyecto()->first()->aceptado);
                if ($this->proyecto()->first()->aceptado)
                // if ($this->proyecto()->first()->aceptado || $this->proyecto()->first()->enviado)
                    return false;
                else if ($this->respuesta == null)
                    return true;
                // else if()
                // else if($this->respuesta )
            } else if ($this->receptor()->whereHas('roles', function (Builder $query) {
                $query->where('nombre_', 'Docente');
            })->count() > 0) {
                if ($this->proyecto()->first()->asesor === $this->receptor()->first()->id || $this->proyecto()->first()->asesor == null)
                    return true;
                if ($this->proyecto()->first()->asesor != $this->receptor()->first()->id)
                    return false;
            }
        }
        $hoy = Carbon::now()->toDateString();
        // if ($this->receptor()->whereHas('roles', function (Builder $query) {
        //     $query->where('nombre_', 'Alumno');
        // })
        //     ->count() > 0
        // ) {
        //     // tal vez aqui poner condición del tio de solicitud
        //     if ($this->proyecto()->first()->aceptado || $this->proyecto()->first()->enviado)
        //         return false;           
        // } else if ($this->receptor()->whereHas('roles', function (Builder $query) {
        //     $query->where('nombre_', 'Docente');
        // })->count() > 0) {
        //     if ($this->proyecto()->first())
        //         return true;
        // }        
    }


    public function editar($usuario, $hoy)
    {
    }

    public function getSolicitudAttribute()
    {
        return $this->tipo_solicitud()->first()->nombre_;
    }





    public function toArray()
    {
        // get the original array to be displayed
        $data = parent::toArray();

        // change the value of the 'mime' key
        if ($this->tipo_solicitud) {
            $data['tipo_solicitud'] = $this->tipo_solicitud()->first()->nombre_;
        } else {
            $data['tipo_solicitud'] = null;
        }

        return $data;
    }














    // public function usuario_emisor()
    // {
    //     return $this->belongsTo('App\User','emisor');
    // }
    public function emisor()
    {
        return $this->belongsTo(User::class, 'emisor');
    }
    public function receptor()
    {
        return $this->belongsTo(User::class, 'receptor');
    }
    // public function usuario_receptor()
    // {
    //     return $this->belongsTo('App\User','receptor');
    // }
    public function proyecto()
    {
        return $this->belongsTo(Proyectos::class, 'proyecto_id');
    }
    public function tipo_solicitud()
    {
        return $this->belongsTo(TiposSolicitud::class, 'tipo_solicitud');
    }
    public function nuevo_asesor()
    {
        return $this->belongsTo(User::class, 'nuevo_asesor');
    }
}
