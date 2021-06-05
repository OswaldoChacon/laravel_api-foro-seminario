<?php

namespace App\Policies;

use App\TipoDeProyecto;
use App\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class TipoDeProyectoPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any tipo de proyectos.
     *
     * @param  \App\User  $user
     * @return mixed
     */
    public function viewAny(User $user)
    {
        //
    }

    /**
     * Determine whether the user can view the tipo de proyecto.
     *
     * @param  \App\User  $user
     * @param  \App\TipoDeProyecto  $tipoDeProyecto
     * @return mixed
     */
    public function view(User $user, TipoDeProyecto $tipoDeProyecto)
    {
        //
    }

    /**
     * Determine whether the user can create tipo de proyectos.
     *
     * @param  \App\User  $user
     * @return mixed
     */
    public function create(User $user)
    {
        return true;
    }

    /**
     * Determine whether the user can update the tipo de proyecto.
     *
     * @param  \App\User  $user
     * @param  \App\TipoDeProyecto  $tipoDeProyecto
     * @return mixed
     */
    public function update(User $user, TipoDeProyecto $tipoDeProyecto)
    {
        //
    }

    /**
     * Determine whether the user can delete the tipo de proyecto.
     *
     * @param  \App\User  $user
     * @param  \App\TipoDeProyecto  $tipoDeProyecto
     * @return mixed
     */
    public function delete(User $user, TipoDeProyecto $tipoDeProyecto)
    {
        if ($tipoDeProyecto->proyectos->count())
            return false;
        return true;
    }

    /**
     * Determine whether the user can restore the tipo de proyecto.
     *
     * @param  \App\User  $user
     * @param  \App\TipoDeProyecto  $tipoDeProyecto
     * @return mixed
     */
    public function restore(User $user, TipoDeProyecto $tipoDeProyecto)
    {
        //
    }

    /**
     * Determine whether the user can permanently delete the tipo de proyecto.
     *
     * @param  \App\User  $user
     * @param  \App\TipoDeProyecto  $tipoDeProyecto
     * @return mixed
     */
    public function forceDelete(User $user, TipoDeProyecto $tipoDeProyecto)
    {
        //
    }
}
