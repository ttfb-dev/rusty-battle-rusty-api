<?php


namespace App\Battle\Actions;


use App\Battle\Battle;
use App\Battle\FightRound;
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

        FightLog::write("Модуль {$this->getModule($battle)->getName()} наносит {$this->getDamage()} урона энергии роботу {$targetRobot->getMemberOwner()}");

        if ($targetRobot->getEnergy() <= 0) {
            $this->markUsed(true);
            $targetRobot->loseLife(1);
            FightLog::write("Робот {$targetRobot->getMemberOwner()} теряет жизнь");
            $fightRound->cancelTargetMemberActions($battle, $this->getTarget());
        }

        return true;
    }
}