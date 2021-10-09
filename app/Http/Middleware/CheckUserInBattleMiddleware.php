<?php


namespace App\Http\Middleware;


use App\Battle\Battle;
use App\Battle\Member;
use Closure;

class CheckUserInBattleMiddleware
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

        $user_id = $request->get('user_id');
        $source = $request->get('source');

        $members = $battle->getMembers();

        foreach ($members as $member) {
            if ($member->getOwner() === Member::MEMBER_CORE) {
                continue;
            }
            if ($member->getOwner() === $source && $member->getOwnerId() === $user_id) {
                return $next($request);
            }
        }
        throw new \Exception('Пользователь с таким ID отсутствует в этой битве');
    }
}
