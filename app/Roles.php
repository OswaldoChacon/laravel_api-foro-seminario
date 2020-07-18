<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Roles extends Model
{
    //
    public $timestamps = false;
    protected $fillable = [
        'nombre'
    ];
    protected $append = ['is'];
    protected $hidden=['id','pivot'];
    public function users()
    {
        return $this->belongsToMany(User::class);
    }
    public function getNameRole(){
        return $this->nombre;
    }
}
