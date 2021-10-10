<?php


namespace App\Battle\Actions;

use App\Battle\Battle;
use App\Battle\FightRound;
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
