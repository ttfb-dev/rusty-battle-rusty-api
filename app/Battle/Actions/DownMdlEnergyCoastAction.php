<?php


namespace App\Battle\Actions;


use App\Battle\Battle;
use App\Battle\FightRound;
use App\Services\FightLog;

class DownMdlEnergyCoastAction extends BaseAction
{

    public function getSort(): int
    {
        return self::SORT_MODULE_CHANGE_ENERGY_COAST;
    }

    public function handle(Battle $battle, FightRound $fightRound): bool
    {
        $robot = $battle->getMemberRobot($this->target);

        $modules = $robot->getModules();

        foreach ($modules as $module) {
            if ($module->getEnergyCoast() > 0) {
                $module->setEnergyCoast($module->getEnergyCoast() - 1);
            }
        }

        FightLog::write("Стоимость активации модулей " . FightLog::getRobotName($this->getAuthor()->getOwner(), 3) . " снижена");

        return true;
    }
}
