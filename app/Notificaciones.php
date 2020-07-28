<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Notificaciones extends Model
{
    //
    public $timestamps = false;
    public function usuario_emisor()
    {
        return $this->belongsTo('App\User','emisor');
    }
    public function usuario_receptor()
    {
        return $this->belongsTo('App\User','receptor');
    }
    public function proyecto()
    {
        return $this->belongsTo('App\Proyectos','proyecto_id');
    }
}
