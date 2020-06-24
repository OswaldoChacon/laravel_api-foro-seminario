<?php

namespace App;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Tymon\JWTAuth\Contracts\JWTSubject;


class User extends Authenticatable implements JWTSubject
{
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */    
    public $timestamps = false;
    // protected $primaryKey = 'num_control';
    protected $fillable = [
        'nombre', 'num_control', 'prefijo', 'apellidoP', 'apellidoM', 'email', 'password'
    ];
    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'id', 'password', 'remember_token', 'email_verificado_at', 'cod_confirmacion', 'confirmado','pivot'
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];
    public function getJWTIdentifier()
    {
        // return $this->getKey();
        return $this->num_control;
    }
    public function getAuthIdentifierName()
    {
        return 'num_control'; // the key name you what to use as the JWT's subject in User model
    }
    public function getJWTCustomClaims()
    {
        return [
            // 'apellidoP'=> $this->apellidoP,
            // 'apellidoM'=> $this->apellidoM,
            // 'email'    => $this->email,
            'roles'    => $this->getNameRoles()
        ];
    }
    public function roles()
    {
        return $this->belongsToMany(Roles::class);
    }
    public function asesor()
    {
        return $this->hasMany(Proyectos::class,'asesor');
    }
    public function jurado_proyecto()
    {
        return $this->belongsToMany(Proyectos::class, 'jurados', 'docente_id', 'proyecto_id');
    }
    public function foros_user()
    {
        return $this->belongsToMany(Foros::class);
    }
    public function proyectos()
    {
        return $this->belongsToMany(Proyectos::class,'proyectos_user');
    }
    public function horarios()
    {
        return $this->hasMany(HorarioJurado::class, 'docente_id');
    }
    public function getNameRoles(){
        $roles = array();
        foreach($this->roles as $rol){
            array_push($roles,$rol->nombre);
        }
        return $roles;
    }
    public function hasProject()
    {
        // dd($this->num_control);
        if (User::whereHas('proyectos.foro', function (Builder $query) {
            $query->where('promedio', '>', 69)->where('acceso', false);
        })->orWhereHas('proyectos.foro', function (Builder $query) {
            $query->where('acceso', true);
        })->whereHas('roles', function (Builder $query) {
            $query->where('roles.nombre', 'Alumno');
        })->where('num_control', $this->num_control)->count() > 0)
            return false;
        return true;
    }
    public function hasAnyRole($roles)
    {
        foreach($roles as $rol){
            if($this->hasRole($rol))
                return true;
        }
        return false;
    }
    public function hasRole($rol)
    {
        if($this->roles()->where('nombre',$rol)->count() > 0)
        {
            // if($rol == "Administrador" && $this->num_control == 56)
            //    dd($this->roles()->where('nombre',$rol)->count());
            return true;
        }
        return false;
    }
    public function getNombre()
    {
        return strtoupper("{$this->prefijo} {$this->nombre} {$this->apellidoP} {$this->apellidoM}");
    }
    
}
