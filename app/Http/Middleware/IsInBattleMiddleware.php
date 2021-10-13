<?php


namespace App\Http\Middleware;


use App\Battle\Battle;
use App\Battle\Member;
use Closure;

class IsInBattleMiddleware
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
        $source = $request->get('source');

        $member = (new Member($source))->setOwnerId($user_id);
        $battle = Battle::whereIAm($member);

        if ($battle instanceof Battle) {
            throw new \Exception('Вы не закончили другую битву');
        }

        return $next($request);
    }
}
