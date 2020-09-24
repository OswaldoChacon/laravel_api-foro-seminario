<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Rol extends Model
{
    //
    protected $table = 'roles';
    public $timestamps = false;
    protected $fillable = [
        'nombre_'
    ];
    protected $append = ['is'];
    protected $hidden = [
        'id', 'pivot'
    ];

    public function getRouteKeyName()
    {
        return 'nombre_';
    }
    public function users()
    {
        return $this->belongsToMany(User::class);
    }
    public function getNameRole()
    {
        return $this->nombre;
    }
}
