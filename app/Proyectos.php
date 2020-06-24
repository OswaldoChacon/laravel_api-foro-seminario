<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Proyectos extends Model
{
    //
    public $timestamps=false;
    protected $fillable = [
        'titulo', 'empresa', 'objetivo', 'asesor'
    ];
    protected $hidden = ['id','lineadeinvestigacion_id','tipos_proyectos_id','foros_id','asesor'];
    public function asesora()
    {
        return $this->belongsTo(User::class,'asesor');
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
        return $this->belongsTo(Foros::class,'foros_id');
    }
    public function jurado()
    {       
        return $this->belongsToMany(User::class,'jurados','proyecto_id','docente_id');    
    }

    public function folio(Foros $foro)
    {
        $folio = $foro->prefijo;
        $concat_folio = $foro->proyectos()->count() +1;
        if ($concat_folio < 10)
            $folio .= "0";
        // $folio .= $concat_folio + 1;
        $folio .= $concat_folio;
        return $folio;
    }
}
