<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Notificaciones extends Model
{
    //
    public $timestamps = false;
    protected $hidden = ['id','proyecto_id','emisor'];
    
    protected $appends = [
        'solicitud'
    ];

    public function getSolicitudAttribute()
    {
        return $this->tipo_solicitud()->first()->nombre_;
    }





    public function toArray() {
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
        return $this->belongsTo('App\User','emisor');
    }
    public function receptor()
    {
        return $this->belongsTo(User::class,'receptor');
    }
    // public function usuario_receptor()
    // {
    //     return $this->belongsTo('App\User','receptor');
    // }
    public function proyecto()
    {
        return $this->belongsTo(Proyectos::class,'proyecto_id');
    }
    public function tipo_solicitud()
    {
        return $this->belongsTo(TiposSolicitud::class,'tipo_solicitud');
    }
    public function nuevo_asesor()
    {
        return $this->belongsTo(User::class,'nuevo_asesor');
    }
}
