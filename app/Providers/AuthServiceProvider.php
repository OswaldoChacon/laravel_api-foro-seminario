<?php

namespace App\Providers;

use App\FechaForo;
use App\LineaDeInvestigacion;
use App\Policies\FechaForoPolicy;
use App\Policies\ForoPolicy;
use App\Policies\LineaDeInvestigacionPolicy;
use App\Policies\ProyectoPolicy;
use App\Policies\RolPolicy;
use App\Policies\TipoDeProyectoPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;
use App\User;
use App\Policies\UserPolicy;
use App\TipoDeProyecto;
use App\TipoDeSolicitud;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
        // 'App\Model' => 'App\Policies\ModelPolicy',
        FechaForo::class=>FechaForoPolicy::class,
        Foro::class=>FechaForoPolicy::class,
        LineaDeInvestigacion::class=>LineaDeInvestigacionPolicy::class,
        Proyecto::class=>ProyectoPolicy::class,        
        Rol::class => RolPolicy::class,
        TipoDeProyecto::class => TipoDeProyectoPolicy::class,
        TipoDeSolicitud::class=>TipoDeProyectoPolicy::class,
        User::class => UserPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();

        //
    }
}
