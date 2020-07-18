<?php

namespace App\Http\Middleware\api;

use Closure;
use JWTAuth;
use Exception;
use Tymon\JWTAuth\Http\Middleware\BaseMiddleware;

class AuthJWT
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();
            // $num_control = $this->auth->getPayload()->get('sub');
            // $user = User::where(compact('num_control'))->firstOrFail();
        } catch (Exception $e) {
            if ($e instanceof \Tymon\JWTAuth\Exceptions\TokenInvalidException){
                return response()->json(['mensaje' => 'Token is Invalid'],401);
            }else if ($e instanceof \Tymon\JWTAuth\Exceptions\TokenExpiredException){
                return response()->json(['mensaje' => 'Token is Expired'],401);
            }else{
                return response()->json(['mensaje' => 'Authorization Token not found'],401);
            }
        }
        foreach($roles as $role) {
            // Check if user has the role This check will depend on how your roles are set up
            if($user->hasRole($role))
                return $next($request);
        }
        return response()->json(['message'=>'No autorizado'], 403);
    
        // return $next($request);
    }
}
