<?php


namespace App\Actions;


use App\Domains\Battle;
use App\Models\FightRound;
use App\Services\FightLog;

class DamageEnergyAction extends BaseAction
{

    public function getSort(): int
    {
        return self::SORT_DAMAGE;
    }

    public function handle(Battle $battle, FightRound $fightRound): bool
    {
        $targetRobot = $battle->getMemberRobot($this->getTarget());
        $targetRobot->damageEnergy($this->getDamage());

        FightLog::write("Модуль {$this->getModule()->getName()} наносит {$this->getDamage()} урона энергии роботу {$targetRobot->getOwner()->getOwner()}");

        if ($targetRobot->getEnergy() <= 0) {
            $this->markUsed(true);
            $targetRobot->loseLife(1);
            FightLog::write("Робот {$targetRobot->getOwner()->getOwner()} теряет жизнь");
            $fightRound->cancelUnusedActions($this->getTarget());
        }

        return true;
    }
}
