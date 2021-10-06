<?php


namespace App\Services;

use App\Domains\BaseRobot;
use App\Modules\BaseModule;
use Illuminate\Support\Facades\Log;

class CoreRobotService
{
    /** @return BaseModule[] */
    public function activateRandomCoreModules(BaseRobot $robot): array {
        $modules = $robot->getModules();
        shuffle($modules);
        $activating_modules = [];

        $energy = $robot->getEnergy();

        $energy_interval = ConfigService::getGeneral('core_use_energy_on_modules_in_percents', ['min' => 50, 'max' => 75]);

        $energy_min = round($energy * ($energy_interval['min'] / 100));
        $energy_max = round($energy * ($energy_interval['max'] / 100));
        $energy_on_modules = rand($energy_min, $energy_max);
        $energy_used = 0;

        foreach ($modules as $module) {
            if ($module->isPassive()) {
                continue;
            }
            if ($module->getEnergyCoast() <= $energy_on_modules) {
                $energy_on_modules -= $module->getEnergyCoast();
                $activating_modules []= $module;
                $robot->useEnergy($module->getEnergyCoast());
                $energy_used += $module->getEnergyCoast();
            }
        }

        FightLog::write("Робот core использовал на модули {$energy_used} энергии. Осталось {$robot->getEnergy()}");

        return $activating_modules;
    }
}
