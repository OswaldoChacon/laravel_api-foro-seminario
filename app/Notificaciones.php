<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Notificaciones extends Model
{
    //
    public $timestamps = false;
    protected $hidden = ['id','proyecto_id'];
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
        return $this->belongsTo('App\User','receptor');
    }
    // public function usuario_receptor()
    // {
    //     return $this->belongsTo('App\User','receptor');
    // }
    public function proyecto()
    {
        return $this->belongsTo('App\Proyectos','proyecto_id');
    }
}
