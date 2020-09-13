<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TiposSolicitud extends Model
{
    //
    public $timestamps = false;
    public $table = "tipos_solicitud";
    protected $hidden = ['id'];
    protected $fillable = ['nombre_'];


    public function notificaciones()
    {
        return  $this->HasMany(Notificaciones::class,'tipo_solicitud');
    }

    public function proyectos()
    {
    }
}
