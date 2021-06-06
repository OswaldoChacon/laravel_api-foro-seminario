<?php

namespace App\Policies;

use App\Foro;
use App\Receso;
use App\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class RecesoPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any recesos.
     *
     * @param  \App\User  $user
     * @return mixed
     */
    public function viewAny(User $user)
    {
        //
    }

    /**
     * Determine whether the user can view the receso.
     *
     * @param  \App\User  $user
     * @param  \App\Receso  $receso
     * @return mixed
     */
    public function view(User $user, Receso $receso)
    {
        //
    }

    /**
     * Determine whether the user can create recesos.
     *
     * @param  \App\User  $user
     * @return mixed
     */
    public function create(User $user, Receso $receso, Foro $foro)
    {
        if (!$foro->activo)
            return false;
        // return response()->json(['message' => 'Foro inactivo'], 400);
        if (!$foro->inTime())
            return false;
        return true;
    }

    /**
     * Determine whether the user can update the receso.
     *
     * @param  \App\User  $user
     * @param  \App\Receso  $receso
     * @return mixed
     */
    public function update(User $user, Receso $receso)
    {
        //
    }

    /**
     * Determine whether the user can delete the receso.
     *
     * @param  \App\User  $user
     * @param  \App\Receso  $receso
     * @return mixed
     */
    public function delete(User $user, Receso $receso, Foro $foro)
    {
        if (!$foro->activo)
            return false;
        return true;
    }

    /**
     * Determine whether the user can restore the receso.
     *
     * @param  \App\User  $user
     * @param  \App\Receso  $receso
     * @return mixed
     */
    public function restore(User $user, Receso $receso)
    {
        //
    }

    /**
     * Determine whether the user can permanently delete the receso.
     *
     * @param  \App\User  $user
     * @param  \App\Receso  $receso
     * @return mixed
     */
    public function forceDelete(User $user, Receso $receso)
    {
        //
    }
}
