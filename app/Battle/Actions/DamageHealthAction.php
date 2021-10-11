<?php


namespace App\Battle\Actions;

use App\Battle\Battle;
use App\Battle\FightRound;
use App\Services\ConfigService;
use App\Services\FightLog;

class DamageHealthAction extends BaseAction
{

    public function getSort(): int
    {
        return self::SORT_DAMAGE;
    }

    public function handle(Battle $battle, FightRound $fightRound): bool
    {
        $targetRobot = $battle->getMemberRobot($this->target);
        $points = $this->getDamage() === 1
            ? $targetRobot->getEnergy()
            : $targetRobot->getEnergy() + ($targetRobot->getEnergyMax() * ($this->getDamage() - 1));
        $battle->addPoints($points * ConfigService::getPoints("{$this->getAuthor()->getOwner()}_damage_energy_coef"));
        $targetRobot->loseLife($this->getDamage());

        FightLog::write(
            FightLog::getRobotName($this->getAuthor()->getOwner()) .
            " наносит " .
            FightLog::getUnitsString($this->getDamage()) .
            " урона жизни " .
            FightLog::getRobotName($this->getTarget()->getOwner(), 2) .
            " (" .
            $this->getModule($battle)->getName() .
            ")"
        );
        FightLog::write(
            FightLog::getRobotName($this->getTarget()->getOwner()) .
            " полностью восстанавливает энергию"
        );

        $this->markUsed(true);
        $fightRound->cancelTargetMemberActions($battle, $this->getTarget());

        return true;
    }
}
