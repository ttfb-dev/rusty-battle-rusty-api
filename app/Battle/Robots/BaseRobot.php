<?php


namespace App\Battle\Robots;

use App\Battle\ArmingRound;
use App\Battle\Battle;
use App\Battle\Modules\BaseModule;
use App\Battle\Factories\ModulesFactory;
use App\Services\ConfigService;
use App\Traits\CodeTrait;

abstract class BaseRobot
{
    use CodeTrait;

    const STATUS_ACTIVE = 'active';

    /** @var BaseModule */
    protected $modules = [];

    /** @var int  */
    protected $module_id_index = 0;

    /** @var int  */
    protected $health_base = 0;
    /** @var int  */
    protected $energy_base = 0;
    /** @var int  */
    protected $health_max = 0;
    /** @var int  */
    protected $energy_max = 0;
    /** @var int  */
    protected $health = 0;
    /** @var int  */
    protected $energy = 0;

    /** @var string  */
    protected $status = self::STATUS_ACTIVE;

    /** @var int */
    protected $damaged_energy = 0;

    public function __construct()
    {
        $this->health_base = ConfigService::getRobotBaseHealth($this->getMemberOwner());
        $this->health = $this->health_base;
        $this->energy_base = ConfigService::getRobotBaseEnergy($this->getMemberOwner());
        $this->energy = $this->energy_base;
    }

    abstract function addModule(BaseModule $module, string $slot);

    abstract function getMemberOwner(): string;

    /** @return BaseModule[] */
    public function getModules(): array {
        return $this->modules;
    }

    /** @param BaseModule[] */
    protected function setModules(array $modules) {
        $this->modules = $modules;
    }

    protected function refreshStats() {
        $this->calcHealth();
        $this->calcEnergy();
    }

    protected function calcHealth() {
        $health_bonus = 0;
        $modules = $this->getModules();
        foreach ($modules as $module) {
            $health_bonus += $module->getHealthBonus();
        }
        $this->health_max = $this->health_base + $health_bonus;
        $this->health += $health_bonus;
        if ($this->health > $this->health_max) {
            $this->health = $this->health_max;
        }
    }

    protected function calcEnergy() {
        $energy_bonus = 0;
        $modules = $this->getModules();
        foreach ($modules as $module) {
            $energy_bonus += $module->getEnergyBonus();
        }
        $this->energy_max = $this->energy_base + $energy_bonus;
        $this->energy += $energy_bonus;
        if ($this->energy > $this->energy_max) {
            $this->energy = $this->energy_max;
        }
    }

    public function getEnergyBase(): int {
        return $this->energy_base;
    }

    public function getEnergyMax(): int {
        return $this->energy_max;
    }

    public function getEnergy(): int {
        return $this->energy;
    }

    public function setEnergy(int $energy): self {
        $this->energy = $energy;
        return $this;
    }

    public function getHealthBase(): int {
        return $this->health_base;
    }

    public function getHealthMax(): int {
        return $this->health_max;
    }

    public function getHealth(): int {
        return $this->health;
    }

    public function useEnergy(int $energy) {
        $this->energy -= $energy;
    }

    public function damageEnergy(int $damage) {
        $this->damaged_energy += $damage;
        $this->useEnergy($damage);
    }

    public function damageHealth(int $damage) {
        $this->health -= $damage;
    }

    public function getDamagedEnergy(): int {
        return $this->damaged_energy;
    }

    public function restoreEnergy(): int {
        $result_energy = $this->getEnergyMax() - $this->getDamagedEnergy();
        $restored_energy = $result_energy - $this->energy;
        $this->energy = $result_energy;
        return $restored_energy;
    }

    public function subtractHealth(): bool {
        if ($this->getEnergy() <= 0) {
            $this->loseLife(1);
            return true;
        }
        return false;
    }

    public function loseLife(int $damage) {
        $this->damageHealth($damage);
        $this->damaged_energy = 0;
        $this->restoreEnergy();
    }

    public function toArray() {
        return [
            'health' => $this->health,
            'energy' => $this->energy,
            'health_max' => $this->health_max,
            'energy_max' => $this->energy_max,
            'health_base' => $this->health_base,
            'energy_base' => $this->energy_base,
            'status' => $this->status,
            'modules_index' => $this->module_id_index,
            'modules' => array_values(ModulesFactory::toArrays($this->getModules())),
        ];
    }

    public function fillWithRandomModules(Battle $battle) {
        $installed_modules = $battle->getRobotsModules();

        $arming_rounds = $battle->getArmingRounds();

        $finished_arming_rounds_count = count(array_filter($arming_rounds, function (ArmingRound $arming_round) {
            return $arming_round->getStatus() === ArmingRound::STATUS_FINISHED;
        }));

        $available_modules = ModulesFactory::getShuffledWithLimitWithoutExcludedAndGarbage(
            $installed_modules,
            ConfigService::getGeneral('core_slots_with_arming_rounds', [])[$finished_arming_rounds_count] ?? 3
        );

        foreach ($available_modules as $available_module) {
            $slot_key = array_rand($available_module->getSlots(), 1);
            $this->addModule($available_module, $available_module->getSlots()[$slot_key]);
        }
    }

    public function getRandomCoreModules(): array {
        $modules = $this->getModules();
        shuffle($modules);
        shuffle($modules);
        $activating_modules = [];

        $energy = $this->getEnergy();

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
                $this->useEnergy($module->getEnergyCoast());
                $energy_used += $module->getEnergyCoast();
            }
        }

        return $activating_modules;
    }

    /**
     * @return BaseModule[]
     */
    public function getModulesByIds(array $modules_ids) {
        $modules = [];
        foreach ($modules_ids as $module_id) {
            foreach ($this->getModules() as $module) {
                if ($module->getId() === $module_id) {
                    $modules []= $module;
                }
            }
        }
        return $modules;
    }

    /** @param BaseModule[] $modules */
    public function activateModules(array $modules) {
        foreach ($modules as $module) {
            if ($module->isPassive()) {
                continue;
            }
            if ($module->getEnergyCoast()) {
                $this->useEnergy($module->getEnergyCoast());
            }
        }
    }

    public function getModuleById(int $module_id): ?BaseModule {
        $modules = $this->getModules();
        foreach ($modules as $module) {
            if ($module->getId() === $module_id) {
                return $module;
            }
        }
        return null;
    }

    public function dropDamagedEnergy() {
        $this->damaged_energy = 0;
    }
}
