<?php

namespace App\Policies;

use App\Foro;
use App\Proyecto;
use App\User;
use Carbon\Carbon;
use Illuminate\Auth\Access\HandlesAuthorization;

class ProyectoPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any proyectos.
     *
     * @param  \App\User  $user
     * @return mixed
     */
    public function viewAny(User $user)
    {
        //
    }

    /**
     * Determine whether the user can view the proyecto.
     *
     * @param  \App\User  $user
     * @param  \App\Proyecto  $proyecto
     * @return mixed
     */
    public function view(User $user, Proyecto $proyecto)
    {
        //
    }

    /**
     * Determine whether the user can create proyectos.
     *
     * @param  \App\User  $user
     * @return mixed
     */
    public function create(User $user, Proyecto $proyecto)
    {
        if (!$user->validarDatosCompletos())
            return false;
        else if (!$user->hasRole('Alumno'))
            return false;
        else if (Carbon::now()->toDateString() > $proyecto->foro->fecha_limite)
            return false;
        else if (!$proyecto->foro->activo)
            return false;
        else if (!$user->hasProject())
            return false;
        return true;
    }

    /**
     * Determine whether the user can update the proyecto.
     *
     * @param  \App\User  $user
     * @param  \App\Proyecto  $proyecto
     * @return mixed
     */
    public function update(User $user, Proyecto $proyecto)
    {
        if (!$user->esMiProyecto($proyecto))
            return false;
        else if ($proyecto->enviado && !$proyecto->aceptado)
            return false;
        else if (Carbon::now()->toDateString() > $proyecto->foro->fecha_limite)
            return false;
        else if ($proyecto->aceptado && !$proyecto->permitir_cambios)
            return false;
        return true;
    }

    /**
     * Determine whether the user can delete the proyecto.
     *
     * @param  \App\User  $user
     * @param  \App\Proyecto  $proyecto
     * @return mixed
     */
    public function delete(User $user, Proyecto $proyecto)
    {
        return false;
    }

    /**
     * Determine whether the user can restore the proyecto.
     *
     * @param  \App\User  $user
     * @param  \App\Proyecto  $proyecto
     * @return mixed
     */
    public function restore(User $user, Proyecto $proyecto)
    {
        //
    }

    /**
     * Determine whether the user can permanently delete the proyecto.
     *
     * @param  \App\User  $user
     * @param  \App\Proyecto  $proyecto
     * @return mixed
     */
    public function forceDelete(User $user, Proyecto $proyecto)
    {
        //
    }
}
