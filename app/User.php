<?php

namespace App;

use Illuminate\Contracts\Auth\MustVerifyEmail;
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

    public function getNombreCompletoAttribute()
    {
        return $this->getNombre();
    }

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
        return $this->hasMany(Proyectos::class, 'asesor');
    }

    public function jurado_proyecto()
    {
        return $this->belongsToMany(Proyectos::class, 'jurados', 'docente_id', 'proyecto_id');
    }

    public function foro()
    {
        return $this->hasMany(Foros::class,'user_id');
    }
    public function foros_user()
    {
        // return $this->belongsToMany(Foros::class,'foros_user','user_id','foros_id');
        return $this->belongsToMany(Foros::class,'foros_user','user_id','foros_id')->withPivot('grupo');;
    }

    public function proyectos()
    {
        return $this->belongsToMany(Proyectos::class, 'proyectos_user');
    }

    public function miSolicitud()
    {
        return $this->hasMany(Notificaciones::class, 'emisor');
    }

    public function misNotificaciones()
    {
        return $this->hasMany(Notificaciones::class, 'receptor');
    }

    public function horarios()
    {
        return $this->hasMany(HorarioJurado::class, 'docente_id');
    }

    public function getNameRoles()
    {
        $roles = array();
        foreach ($this->roles as $rol) {
            array_push($roles, $rol->nombre_);
        }
        return $roles;
    }

    public function getDocentes()
    {
    }



    public function proyectoActual()
    {
        return $this->proyectos()->whereHas('foro', function (Builder $query) {
            $query->where('acceso', true);
        })->with(['asesora' => function ($query) {
            // $query->select('id', DB::raw("CONCAT(IFNULL(prefijo,''),' ',nombre,' ',apellidoP,' ',apellidoM) AS nombreCompleto"));
        }])->firstOrFail();
        // first()
    }

    public function hasProject()
    {
        if (User::whereHas('proyectos.foro', function (Builder $query) {
            $query->where('promedio', '>', 69)->where('acceso', false);
        })->orWhereHas('proyectos.foro', function (Builder $query) {
            $query->where('acceso', true);
        })->whereHas('roles', function (Builder $query) {
            $query->where('roles.nombre_', 'Alumno');
        })->Buscar($this->num_control)->count() > 0)
            return false;
        return true;
    }

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

    public function getNombre()
    {
        return strtoupper("{$this->prefijo} {$this->nombre} {$this->apellidoP} {$this->apellidoM}");
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
        Mail::to($this->email)->send(new EmailConfirmacion($data));
        $this->password = bcrypt($this->password);
    }

    public function scopeDatosBasicos($query)
    {
        return $query->select('id','num_control','prefijo','nombre','apellidoP','apellidoM');
    }
    public function scopeBuscar($query, $num_control)
    {
        return $query->where('num_control', $num_control);
    }
}
