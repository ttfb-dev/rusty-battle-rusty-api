<?php


namespace App\Battle\Actions;


use App\Battle\Battle;
use App\Battle\FightRound;
use App\Services\ConfigService;
use App\Services\FightLog;
use Illuminate\Support\Facades\Log;

class DamageEnergyAction extends BaseAction
{

    public function getSort(): int
    {
        return self::SORT_DAMAGE;
    }

    public function handle(Battle $battle, FightRound $fightRound): bool
    {
        $targetRobot = $battle->getMemberRobot($this->getTarget());
        $points = $this->getDamage() > $targetRobot->getEnergy() ? $targetRobot->getEnergy() : $this->getDamage();
        $targetRobot->damageEnergy($this->getDamage());
        $battle->addPoints($points * ConfigService::getPoints("{$this->getAuthor()->getOwner()}_damage_energy_coef"));

        FightLog::write(
            FightLog::getRobotName($this->getAuthor()->getOwner()) .
            " наносит " .
            FightLog::getUnitsString($this->getDamage()) .
            " урона энергии " .
            FightLog::getRobotName($this->getTarget()->getOwner(), 2) .
            " (" . $this->getModule($battle)->getName() . ")"
        );

        if ($targetRobot->getEnergy() <= 0) {
            $this->markUsed(true);
            $targetRobot->loseLife(1);
            FightLog::write(
                FightLog::getRobotName($this->getTarget()->getOwner()) .
                " теряет жизнь и полностью восстанавливает энергию"
            );
            $fightRound->cancelTargetMemberActions($battle, $this->getTarget());
        }

        return true;
    }
}
