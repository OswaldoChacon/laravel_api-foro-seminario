<?php

namespace App\Policies;

use App\LineaDeInvestigacion;
use App\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class LineaDeInvestigacionPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any linea de investigacions.
     *
     * @param  \App\User  $user
     * @return mixed
     */
    public function viewAny(User $user)
    {
        //
    }

    /**
     * Determine whether the user can view the linea de investigacion.
     *
     * @param  \App\User  $user
     * @param  \App\LineaDeInvestigacion  $lineaDeInvestigacion
     * @return mixed
     */
    public function view(User $user, LineaDeInvestigacion $lineaDeInvestigacion)
    {
        //
    }

    /**
     * Determine whether the user can create linea de investigacions.
     *
     * @param  \App\User  $user
     * @return mixed
     */
    public function create(User $user)
    {
        //
    }

    /**
     * Determine whether the user can update the linea de investigacion.
     *
     * @param  \App\User  $user
     * @param  \App\LineaDeInvestigacion  $lineaDeInvestigacion
     * @return mixed
     */
    public function update(User $user, LineaDeInvestigacion $lineaDeInvestigacion)
    {
        //
    }

    /**
     * Determine whether the user can delete the linea de investigacion.
     *
     * @param  \App\User  $user
     * @param  \App\LineaDeInvestigacion  $lineaDeInvestigacion
     * @return mixed
     */
    public function delete(User $user, LineaDeInvestigacion $lineaDeInvestigacion)
    {
        //
    }

    /**
     * Determine whether the user can restore the linea de investigacion.
     *
     * @param  \App\User  $user
     * @param  \App\LineaDeInvestigacion  $lineaDeInvestigacion
     * @return mixed
     */
    public function restore(User $user, LineaDeInvestigacion $lineaDeInvestigacion)
    {
        //
    }

    /**
     * Determine whether the user can permanently delete the linea de investigacion.
     *
     * @param  \App\User  $user
     * @param  \App\LineaDeInvestigacion  $lineaDeInvestigacion
     * @return mixed
     */
    public function forceDelete(User $user, LineaDeInvestigacion $lineaDeInvestigacion)
    {
        //
    }
}
