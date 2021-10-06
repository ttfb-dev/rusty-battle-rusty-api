<?php


namespace App\Domains;


use App\Models\Robot;
use App\Modules\BaseModule;
use App\Modules\ModulesCollection;
use App\Traits\CodeTrait;
use Illuminate\Support\Facades\Log;

abstract class BaseRobot
{
    use CodeTrait;

    /** @var Robot  */
    protected $model;

    /** @var int */
    protected $damaged_energy = 0;

    public function __construct(Robot $robot)
    {
        $this->model = $robot;
    }

    abstract function addModule(BaseModule $module, string $slot);

    /** @return BaseModule[] */
    public function getModules(): array {
        $modules = [];
        $module_arrays = json_decode($this->model->modules, true) ?? [];
        foreach ($module_arrays as $key => $module_array) {
            $module = BaseModule::fromArray($module_array);
            $module->setRobot($this);
            $modules[$key] = $module;
        }
        return $modules;
    }

    /** @param BaseModule[] */
    protected function setModules(array $modules) {
        $modules_array = [];
        foreach ($modules as $key => $module) {
            $modules_array[$key] = $module->toArray();
        }
        $this->model->modules = json_encode($modules_array);
        $this->refreshStats();
        $this->model->save();
    }

    private function refreshStats() {
        $this->calcHealth();
        $this->calcEnergy();
    }

    private function calcHealth() {
        $health_bonus = 0;
        $modules = $this->getModules();
        foreach ($modules as $module) {
            $health_bonus += $module->getHealthBonus();
        }
        $this->model->health_max = $this->model->health_base + $health_bonus;
        $this->model->health += $health_bonus;
        if ($this->model->health > $this->model->health_max) {
            $this->model->health = $this->model->health_max;
        }
    }

    private function calcEnergy() {
        $energy_bonus = 0;
        $modules = $this->getModules();
        foreach ($modules as $module) {
            $energy_bonus += $module->getEnergyBonus();
        }
        $this->model->energy_max = $this->model->energy_base + $energy_bonus;
        $this->model->energy += $energy_bonus;
        if ($this->model->energy > $this->model->energy_max) {
            $this->model->energy = $this->model->energy_max;
        }
    }

    public function getOwner(): Member {
        return $this->model->getOwner();
    }

    public function getEnergyBase(): int {
        return $this->model->energy_base;
    }

    public function getEnergyMax(): int {
        return $this->model->energy_max;
    }

    public function getEnergy(): int {
        return $this->model->energy;
    }

    public function getHealthBase(): int {
        return $this->model->health_base;
    }

    public function getHealthMax(): int {
        return $this->model->health_max;
    }

    public function getHealth(): int {
        return $this->model->health;
    }

    public function useEnergy(int $energy) {
        $this->model->energy -= $energy;
        $this->model->save();
    }

    public function damageEnergy(int $damage) {
        $this->damaged_energy += $damage;
        $this->useEnergy($damage);
    }

    public function damageHealth(int $damage) {
        $this->model->health -= $damage;
        $this->model->save();
    }

    public function getDamagedEnergy(): int {
        return $this->damaged_energy;
    }

    public function restoreEnergy(): int {
        $result_energy = $this->getEnergyMax() - $this->getDamagedEnergy();
        $restored_energy = $result_energy - $this->model->energy;
        $this->model->energy = $result_energy;
        $this->model->save();
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
            'health' => $this->model->health,
            'energy' => $this->model->energy,
            'health_max' => $this->model->health_max,
            'energy_max' => $this->model->energy_max,
            'health_base' => $this->model->health_base,
            'energy_base' => $this->model->energy_base,
            'status' => $this->model->status,
            'modules' => array_values(ModulesCollection::toArrays($this->getModules())),
        ];
    }
}
