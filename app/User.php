<?php

namespace App;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Tymon\JWTAuth\Contracts\JWTSubject;
use App\Mail\confirmacion;
use App\Mail\EmailConfirmacion;
use App\Mail\ForgotPassword;
use Illuminate\Support\Facades\Mail;

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
        'id', 'password', 'remember_token', 'confirmado', 'pivot'
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
        return $this->hasMany(Foro::class);
    }
    public function foros_users()
    {
        return $this->belongsToMany(Foro::class);
    }
    public function proyectos()
    {
        return $this->belongsToMany(Proyecto::class);
    }
    public function horarios()
    {
        return $this->hasMany(Horario::class);
    }
    public function miSolicitud()
    {
        return $this->hasMany(Notificacion::class, 'emisor_id');
    }
    public function misNotificaciones()
    {
        return $this->hasMany(Notificacion::class, 'receptor_id');
    }

    // validaciones
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
        if ($rol === 'Taller')
            if ($this->foros_users()->Activo(true)->count() > 0)
                return true;
        return false;
    }
    public function hasProject()
    {
        if (User::whereHas('proyectos.foro', function (Builder $query) {
            $query->where('promedio', '>', 69)->Activo(false);
        })->orWhereHas('proyectos.foro', function (Builder $query) {
            $query->Activo(true);
        })->UsuariosConRol('Alumno')->Buscar($this->num_control)->count() > 0)
            return false;
        return true;
    }
    public function esJurado()
    {
        return $this->jurado_proyecto()->whereHas('foro', function (Builder $query) {
            $query->Activo(true);
        })->count() > 0;
    }
    public function esMiProyecto(Proyecto $proyecto)
    {
        // dd($this->proyectos()->get()->contains($proyecto));
        return $this->proyectos()->get()->contains($proyecto);
    }
    public function validarDatosCompletos()
    {
        if ($this->nombre === null || $this->apellidoP === null || $this->apellidoM === null)
            return false;
        return true;
    }

    // getters
    public function getNombreCompletoAttribute()
    {
        return $this->getNombre();
    }
    public function getRolesAttribute()
    {
        $roles = Rol::all();
        foreach ($roles as $rol) {
            $rol->is = $this->hasRole($rol->nombre_);
        }
        return $roles;
    }
    public function getNombre()
    {
        $prefijo = $this->prefijo === null ? '' : $this->prefijo . ' ';
        if ($this->nombre === null || $this->apellidoP === null || $this->apellidoM === null)
            return 'Datos incompletos';
        return strtoupper("{$prefijo}{$this->nombre} {$this->apellidoP} {$this->apellidoM}");
    }
    public function getMisRoles()
    {
        $roles = array();
        foreach ($this->roles()->get() as $rol) {
            array_push($roles, strtolower($rol->nombre_));
        }
        if ($this->foros_users()->Activo(true)
            // where('activo', true)
            ->count() > 0
        )
            array_push($roles, 'Taller');
        return $roles;
    }
    public function getProyectoActual()
    {
        return $this->proyectos()->with('asesor')->whereHas('foro', function (Builder $query) {
            $query->Activo(true);
        })->first();
    }
    public function enviarEmail($password, $type)
    {
        $data = [
            'nombre' => strtoupper($this->nombre),
            'password' => $password,
        ];
        if ($type === 'forgot_password')
            Mail::to($this->email)->send(new ForgotPassword($data));
        else if ($type === 'nuevo')
            Mail::to($this->email)->send(new confirmacion($data));
    }

    // scopes
    public function scopeSinProyectos($query)
    {
        return $query->whereDoesntHave('proyectos.foro', function (Builder $query) {
            $query->Activo(true)->orWhere('promedio', '>', 69);
        });
    }
    public function scopeMiEquipo($query, $folio)
    {
        return $query->whereHas('proyectos', function (Builder $query) use ($folio) {
            $query->Buscar($folio);
        });
    }
    public function scopeDatosBasicos($query)
    {
        return $query->select('users.id', 'num_control', 'prefijo', 'nombre', 'apellidoP', 'apellidoM', 'email');
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
