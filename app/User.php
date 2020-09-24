<?php

namespace App;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Tymon\JWTAuth\Contracts\JWTSubject;
use App\Mail\confirmacion;
use App\Mail\EmailConfirmacion;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;


class User extends Authenticatable implements JWTSubject
{
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    public $timestamps = false;

    protected $fillable = [
        'nombre', 'num_control', 'prefijo', 'apellidoP', 'apellidoM', 'email', 'password'
    ];
    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'id', 'password', 'remember_token', 'email_verificado_at', 'cod_confirmacion', 'confirmado', 'pivot'
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    protected $appends = [
        'nombreCompleto'
    ];

    public function getRouteKeyName()
    {
        return 'num_control';
    }
    public function getNombreCompletoAttribute()
    {
        return $this->getNombre();
    }

    public function getJWTIdentifier()
    {
        return $this->num_control;
    }

    public function getAuthIdentifierName()
    {
        return 'num_control'; // the key name you what to use as the JWT's subject in User model
    }

    public function getJWTCustomClaims()
    {
        return [
            'roles'    => $this->getMisRoles()
        ];
    }

    public function roles()
    {
        return $this->belongsToMany(Rol::class);
    }

    public function asesor()
    {
        return $this->hasMany(Proyecto::class, 'asesor_id');
    }

    public function jurado_proyecto()
    {
        return $this->belongsToMany(Proyecto::class, 'jurados', 'user_id', 'proyecto_id');
    }

    public function foros()
    {
        // return $this->hasMany(Foro::class,'user_id');
        return $this->hasMany(Foro::class);
    }
    public function foros_users()
    {
        // return $this->belongsToMany(Foro::class,'foro_user','user_id','foro_id');
        return $this->belongsToMany(Foro::class);
    }

    public function proyectos()
    {
        // return $this->belongsToMany(Proyecto::class, 'proyecto_user');
        return $this->belongsToMany(Proyecto::class);
    }

    public function miSolicitud()
    {
        return $this->hasMany(Notificacion::class, 'emisor_id');
    }

    public function misNotificaciones()
    {
        // return $this->hasMany(Notificacion::class, 'receptor');
        return $this->hasMany(Notificacion::class, 'receptor_id');
    }

    public function horarios()
    {
        // return $this->hasMany(Horario::class, 'user_id');
        return $this->hasMany(Horario::class);
    }


    // validación de roles
    public function hasAnyRole($roles)
    {
        foreach ($roles as $rol) {
            if ($this->hasRole($rol))
                return true;
        }
        return false;
    }

    public function hasRole($rol)
    {
        if ($this->roles()->where('nombre_', $rol)->count() > 0)
            return true;
        return false;
    }



    // funciones agenas a las relaciones en la bd
    public function getMisRoles()
    {
        $roles = array();
        foreach ($this->roles()->get() as $rol) {
            array_push($roles, $rol->nombre_);
        }
        return $roles;
    }
    public function validarDatosCompletos()
    {
        if ($this->nombre === null || $this->apellidoP === null || $this->apellidoM === null)
            return false;
        return true;
    }

    public function getProyectoActual()
    {
        return $this->proyectos()->with('asesor')->whereHas('foro', function (Builder $query) {
            $query->where('activo', true);
        })->first();
    }

    public function hasProject()
    {
        if (User::whereHas('proyectos.foro', function (Builder $query) {
            $query->where('promedio', '>', 69)->where('activo', false);
        })->orWhereHas('proyectos.foro', function (Builder $query) {
            $query->where('activo', true);
        })->UsuariosConRol('Alumno')->Buscar($this->num_control)->count() > 0)
            return false;
        return true;
    }

    
    public function getNombre()
    {
        $prefijo = $this->prefijo === null ? '' : $this->prefijo . ' ';
        if ($this->nombre === null || $this->apellidoP === null || $this->apellidoM === null)
            return 'Datos incompletos';
        return strtoupper("{$prefijo}{$this->nombre} {$this->apellidoP} {$this->apellidoM}");
    }

    public function enviarEmailConfirmacion()
    {
        $this->cod_confirmacion = Str::random(25);
        $this->password = Str::random(10);
        $data = [
            'nombre' => strtoupper($this->nombre),
            'password' => $this->password,
            'cod_confirmacion' => $this->cod_confirmacion
        ];
        // Mail::to($this->email)->send(new EmailConfirmacion($data));
        Mail::to($this->email)->send(new confirmacion($data));
        $this->password = bcrypt($this->password);
    }



    public function getRolesAttribute()
    {
        $roles = Rol::all();
        foreach ($roles as $rol) {
            $rol->is = $this->hasRole($rol->nombre_);
        }
        return $roles;
    }

    // scopes
    public function scopeDatosBasicos($query)
    {
        return $query->select('users.id', 'num_control', 'prefijo', 'nombre', 'apellidoP', 'apellidoM');
    }
    public function scopeBuscar($query, $num_control)
    {
        return $query->where('num_control', $num_control);
    }
    public function scopeUsuariosConRol($query, $rol)
    {
        return $query->whereHas('roles', function ($query) use ($rol) {
            $query->where('nombre_', $rol);
        });
    }
    public function scopeConDatosCompletos($query)
    {
        return $query->where([
            ['nombre', '!=', null],
            ['apellidoP', '!=', null],
            ['apellidoM', '!=', null]
        ]);
    }
}
