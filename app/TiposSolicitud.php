<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TiposSolicitud extends Model
{
    //
    public $timestamps = false;
    public $table = "tipos_solicitud";
    protected $hidden = ['id'];
    protected $fillable = ['nombre_'];

   
    public function notificacion()
    {
    }

   
}
