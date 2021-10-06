<?php


namespace App\Actions;


use App\Domains\Battle;
use App\Models\FightRound;
use App\Services\FightLog;

class DamageHealthAction extends BaseAction
{

    public function getSort(): int
    {
        return self::SORT_DAMAGE;
    }

    public function handle(Battle $battle, FightRound $fightRound): bool
    {
        $targetRobot = $battle->getMemberRobot($this->getTarget());
        $targetRobot->loseLife($this->getDamage());
        FightLog::write("Модуль {$this->getModule()->getName()} наносит {$this->getDamage()} урона жизни роботу {$targetRobot->getOwner()->getOwner()}");
        $this->markUsed(true);
        $fightRound->cancelUnusedActions($this->getTarget());

        return true;
    }
}
