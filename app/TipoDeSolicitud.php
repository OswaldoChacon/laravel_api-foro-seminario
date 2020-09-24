<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TipoDeSolicitud extends Model
{
    //
    public $timestamps = false;
    public $table = "tipos_de_solicitud";
    protected $hidden = [
        'id'
    ];
    protected $fillable = [
        'nombre_'
    ];

    public function getRouteKeyName()
    {
        return 'nombre_';
    }
    public function notificaciones()
    {
        // return  $this->HasMany(Notificacion::class,'tipo_solicitud');
        return  $this->HasMany(Notificacion::class);
    }
    
}
