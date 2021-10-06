<?php


namespace App\Http\Middleware;


use App\Services\BattleService;
use Closure;

class CheckBattleFightMiddleware
{
    /** @var BattleService */
    private $battleService;

    public function __construct(BattleService $battleService)
    {
        $this->battleService = $battleService;
    }

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
        $battle = $this->battleService->load($battle_id);

        throw_if(!$battle->isBattle(), new \Exception('Битва в другом статусе'));

        return $next($request);
    }
}
