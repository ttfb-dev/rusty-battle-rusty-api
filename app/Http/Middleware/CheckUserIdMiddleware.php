<?php


namespace App\Http\Middleware;

use Closure;

class CheckUserIdMiddleware
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
        $user_id = $request->get('user_id');
        throw_if(is_null($user_id), new \Exception('Missing required parameter: user_id'));
        return $next($request);
    }
}
