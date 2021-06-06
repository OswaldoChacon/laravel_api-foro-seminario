<?php

namespace App\Policies;

use App\FechaForo;
use App\Foro;
use App\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class FechaForoPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any fecha foros.
     *
     * @param  \App\User  $user
     * @return mixed
     */
    public function viewAny(User $user)
    {
        //
    }

    /**
     * Determine whether the user can view the fecha foro.
     *
     * @param  \App\User  $user
     * @param  \App\FechaForo  $fechaForo
     * @return mixed
     */
    public function view(User $user, FechaForo $fechaForo)
    {
        //
    }

    /**
     * Determine whether the user can create fecha foros.
     *
     * @param  \App\User  $user
     * @return mixed
     */
    public function create(User $user, FechaForo $fechaForo, Foro $foro)
    {
        if (!$foro->activo)
            return false;
        // if (!$foro->inTime())
        //     return false;
        return true;
    }

    /**
     * Determine whether the user can update the fecha foro.
     *
     * @param  \App\User  $user
     * @param  \App\FechaForo  $fechaForo
     * @return mixed
     */
    public function update(User $user, FechaForo $fechaForo, Foro $foro)
    {
        if (!$foro->activo)
            return false;
        // if (!$foro->inTime())
        //     return false;
        return true;
    }

    /**
     * Determine whether the user can delete the fecha foro.
     *
     * @param  \App\User  $user
     * @param  \App\FechaForo  $fechaForo
     * @return mixed
     */
    public function delete(User $user, FechaForo $fechaForo)
    {
        //
    }

    /**
     * Determine whether the user can restore the fecha foro.
     *
     * @param  \App\User  $user
     * @param  \App\FechaForo  $fechaForo
     * @return mixed
     */
    public function restore(User $user, FechaForo $fechaForo)
    {
        //
    }

    /**
     * Determine whether the user can permanently delete the fecha foro.
     *
     * @param  \App\User  $user
     * @param  \App\FechaForo  $fechaForo
     * @return mixed
     */
    public function forceDelete(User $user, FechaForo $fechaForo)
    {
        //
    }
}
