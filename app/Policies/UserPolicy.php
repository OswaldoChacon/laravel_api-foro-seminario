<?php

namespace App\Policies;

use  JWTAuth;
use App\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class UserPolicy
{
    use HandlesAuthorization;

    /**
     * Create a new policy instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }
    public function actualizar_datos(User $user)
    {                        
        return JWTAuth::user()->id === $user->id;
    }
}
