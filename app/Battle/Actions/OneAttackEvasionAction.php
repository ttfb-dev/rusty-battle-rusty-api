<?php


namespace App\Battle\Actions;


use App\Battle\Battle;
use App\Battle\FightRound;
use App\Services\FightLog;
use Illuminate\Support\Facades\Log;

class OneAttackEvasionAction extends BaseAction
{
    public function getSort(): int
    {
        return self::SORT_DISABLE_ATTACK;
    }

    public function handle(Battle $battle, FightRound $fightRound): bool
    {
        $sorted_actions = $fightRound->getActions();

        foreach ($sorted_actions as $actions) {
            foreach ($actions as $action) {
                if ($action->getTarget()->equal($this->getAuthor())
                    && $action->getDamage() > 0
                    && $action->isActive()
                    && $action->isInRound($fightRound->getRoundNumber())
                ) {
                    FightLog::write(ucfirst(FightLog::getRobotName($this->author->getOwner())) . " уворачивается от атаки " . FightLog::getRobotName($this->target->getOwner()) . " ({$action->getModule($battle)->getName()})");
                    $action->setActive(false);
                    break;
                }
            }
        }

        return true;
    }
}
