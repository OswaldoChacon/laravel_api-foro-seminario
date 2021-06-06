<?php

namespace App\Policies;

use App\TipoDeSolicitud;
use App\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class TipoDeSolicitudPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any tipo de solicituds.
     *
     * @param  \App\User  $user
     * @return mixed
     */
    public function viewAny(User $user)
    {
        //
    }

    /**
     * Determine whether the user can view the tipo de solicitud.
     *
     * @param  \App\User  $user
     * @param  \App\TipoDeSolicitud  $tipoDeSolicitud
     * @return mixed
     */
    public function view(User $user, TipoDeSolicitud $tipoDeSolicitud)
    {
        //
    }

    /**
     * Determine whether the user can create tipo de solicituds.
     *
     * @param  \App\User  $user
     * @return mixed
     */
    public function create(User $user)
    {
        //
    }

    /**
     * Determine whether the user can update the tipo de solicitud.
     *
     * @param  \App\User  $user
     * @param  \App\TipoDeSolicitud  $tipoDeSolicitud
     * @return mixed
     */
    public function update(User $user, TipoDeSolicitud $tipoDeSolicitud)
    {
        //
    }

    /**
     * Determine whether the user can delete the tipo de solicitud.
     *
     * @param  \App\User  $user
     * @param  \App\TipoDeSolicitud  $tipoDeSolicitud
     * @return mixed
     */
    public function delete(User $user, TipoDeSolicitud $tipoDeSolicitud)
    {
        return $tipoDeSolicitud->notificaciones->count() ? false : true;
    }

    /**
     * Determine whether the user can restore the tipo de solicitud.
     *
     * @param  \App\User  $user
     * @param  \App\TipoDeSolicitud  $tipoDeSolicitud
     * @return mixed
     */
    public function restore(User $user, TipoDeSolicitud $tipoDeSolicitud)
    {
        //
    }

    /**
     * Determine whether the user can permanently delete the tipo de solicitud.
     *
     * @param  \App\User  $user
     * @param  \App\TipoDeSolicitud  $tipoDeSolicitud
     * @return mixed
     */
    public function forceDelete(User $user, TipoDeSolicitud $tipoDeSolicitud)
    {
        //
    }
}
