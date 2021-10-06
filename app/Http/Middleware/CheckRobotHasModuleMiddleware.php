<?php


namespace App\Http\Middleware;


use App\Domains\Member;
use App\Services\BattleService;
use Closure;

class CheckRobotHasModuleMiddleware
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
        $module_id = $request->get('module');
        $slot = $request->get('slot');
        $user_id = $request->get('user_id');
        $source = $request->get('source');
        $member = (new Member($source))->setOwnerId($user_id);
        $robot = $battle->getMemberRobot($member);

        $modules = $robot->getModules();

        foreach ($modules as $module) {
            if ($module->getSlot() === $slot && $module->getCode() === $module_id) {
                return $next($request);
            }
        }

        throw new \Exception('Такой модуль в слоте не найден');

    }
}
