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
            " (" .
            $this->getModule($battle)->getName() .
            ") наносит " .
            FightLog::getUnitsString($this->getDamage()) .
            " урона жизни"
        );

        $this->markUsed(true);
        $fightRound->cancelTargetMemberActions($battle, $this->getTarget());

        return true;
    }
}
