<?php


namespace App\Battle\Actions;


use App\Battle\Battle;
use App\Battle\FightRound;
use App\Services\FightLog;
use Illuminate\Support\Facades\Log;

class BlockRandomMdlAction extends BaseAction
{

    public function getSort(): int
    {
        return self::SORT_DISABLE_MODULE;
    }

    public function handle(Battle $battle, FightRound $fightRound): bool
    {
        $modules = $fightRound->getModules();

        Log::debug('modules', [$modules]);
        $target_modules = $modules[$this->target->toString()];

        Log::debug('target modules', [$this->target, $target_modules]);

        if (count($target_modules) === 0) {
            return true;
        }

        $random_module_index = array_rand($target_modules, 1);
        $random_module = $target_modules[$random_module_index];

        FightLog::write("Отключен случайный модуль робота {$this->target->getOwner()}: {$random_module->getName()}");

        $actions_sorted = $fightRound->getActions();
        foreach ($actions_sorted as $actions) {
            foreach ($actions as $action) {
                if ($action->getModule($battle)->getCode() === $random_module->getCode()) {
                    $action->setActive(false);
                }
            }
        }

        return true;
    }
}
