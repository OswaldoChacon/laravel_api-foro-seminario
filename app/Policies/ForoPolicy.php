<?php

namespace App\Policies;

use App\Foro;
use App\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class ForoPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any foros.
     *
     * @param  \App\User  $user
     * @return mixed
     */
    public function viewAny(User $user)
    {
        //
    }

    /**
     * Determine whether the user can view the foro.
     *
     * @param  \App\User  $user
     * @param  \App\Foro  $foro
     * @return mixed
     */
    public function view(User $user, Foro $foro)
    {
        //
    }

    /**
     * Determine whether the user can create foros.
     *
     * @param  \App\User  $user
     * @return mixed
     */
    public function create(User $user)
    {
        //
    }

    /**
     * Determine whether the user can update the foro.
     *
     * @param  \App\User  $user
     * @param  \App\Foro  $foro
     * @return mixed
     */
    public function update(User $user, Foro $foro)
    {
        //
    }

    /**
     * Determine whether the user can delete the foro.
     *
     * @param  \App\User  $user
     * @param  \App\Foro  $foro
     * @return mixed
     */
    public function delete(User $user, Foro $foro)
    {
        if ($foro->activo)
            return false;
        else if ($foro->proyectos->count())
            return false;
        return true;
    }

    /**
     * Determine whether the user can restore the foro.
     *
     * @param  \App\User  $user
     * @param  \App\Foro  $foro
     * @return mixed
     */
    public function restore(User $user, Foro $foro)
    {
        //
    }

    /**
     * Determine whether the user can permanently delete the foro.
     *
     * @param  \App\User  $user
     * @param  \App\Foro  $foro
     * @return mixed
     */
    public function forceDelete(User $user, Foro $foro)
    {
        //
    }
}
