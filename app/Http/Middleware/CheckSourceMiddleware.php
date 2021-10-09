<?php


namespace App\Http\Middleware;


use App\Battle\Member;
use Closure;

class CheckSourceMiddleware
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
        $source = $request->get('source');
        throw_if(!in_array($source, Member::ALLOWED_MEMBERS), new \Exception('Missing required parameter: source'));
        return $next($request);
    }
}
