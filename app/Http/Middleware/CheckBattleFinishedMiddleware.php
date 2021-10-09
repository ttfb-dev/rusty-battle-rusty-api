<?php


namespace App\Http\Middleware;

use App\Battle\Battle;
use Closure;

class CheckBattleFinishedMiddleware
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
        $battle_id = $request->route()[2]['battle_id'];
        $battle = Battle::load($battle_id);

        throw_if(
            $battle->getStatus() !== Battle::STATUS_FINISHED,
            new \Exception('Битва в другом статусе')
        );

        return $next($request);
    }
}
