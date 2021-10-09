<?php


namespace App\Battle\Actions;


use App\Battle\Battle;
use App\Battle\FightRound;
use App\Services\FightLog;

class OneTimeEnergyBonusAction extends BaseAction
{

    public function getSort(): int
    {
        return self::SORT_SPECIFICATIONS_BONUS;
    }

    public function handle(Battle $battle, FightRound $fightRound): bool
    {
        $authorRobot = $battle->getMemberRobot($this->author);

        $energy_before = $authorRobot->getEnergy();

        $authorRobot->setEnergy($energy_before * 2);

        FightLog::write("Робот {$authorRobot->getMemberOwner()} повысил энергию с {$energy_before} до {$authorRobot->getEnergy()}");

        return true;
    }
}
