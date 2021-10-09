<?php


namespace App\Battle\Modules;

use App\Battle\Actions\BaseAction;
use App\Battle\Factories\ModulesFactory;
use App\Battle\Robots\BaseRobot;
use App\Battle\Battle;
use App\Battle\Member;
use App\Services\ConfigService;
use App\Traits\CodeTrait;
use Illuminate\Support\Facades\Log;

class BaseModule
{
    use CodeTrait;

    const STATUS_ACTIVE = 'active';

    /** @var int */
    protected $id = 0;

    /** @var int */
    protected $energyCoast = 0;

    /** @var int */
    protected $energyBonus = 0;

    /** @var int */
    protected $healthBonus = 0;

    /** @var int */
    protected $damage = 0;

    /** @var string  */
    protected $name = '';

    /** @var string  */
    protected $description = '';

    /** @var string[]  */
    protected $slots = [];

    /** @var string */
    protected $slot = '';

    protected $status = self::STATUS_ACTIVE;

    /** @var bool */
    protected $passive = false;

    protected $activated = false;

    public function __construct()
    {
        $this->energyCoast = ConfigService::getModuleEnergyCoast($this->getCode(), $this->energyCoast);
        $this->energyBonus = ConfigService::getModuleEnergyBonus($this->getCode(), $this->energyBonus);
        $this->healthBonus = ConfigService::getModuleHealthBonus($this->getCode(), $this->healthBonus);
        $this->damage      = ConfigService::getModuleDamage($this->getCode(), $this->damage);

        $this->name        = ConfigService::getModuleName($this->getCode(), $this->name);
        $this->description = ConfigService::getModuleDescription($this->getCode(), $this->description);
        $this->slots       = ConfigService::getModuleSlots($this->getCode(), $this->slots);
        $this->passive     = ConfigService::getModulePassive($this->getCode(), $this->passive);
    }

    public function setId(int $id): self {
        $this->id = $id;
        return $this;
    }

    public function getId(): int {
        return $this->id;
    }

    public function setEnergyCoast(int $energyCoast): self {
        $this->energyCoast = $energyCoast;
        return $this;
    }

    public function setEnergyBonus(int $energyBonus): self {
        $this->energyBonus = $energyBonus;
        return $this;
    }

    public function setHealthBonus(int $healthBonus): self {
        $this->healthBonus = $healthBonus;
        return $this;
    }

    public function setDamage(int $damage): self {
        $this->damage = $damage;
        return $this;
    }

    public function setSlot(string $slot): self {
        $this->slot = $slot;
        return $this;
    }

    public function getEnergyCoast(): int {
        return $this->energyCoast;
    }

    public function getEnergyBonus(): int {
        return $this->energyBonus;
    }

    public function getHealthBonus(): int {
        return $this->healthBonus;
    }

    public function getDamage(): int {
        return $this->damage;
    }

    public function getSlots(): array {
        return $this->slots;
    }

    public function getName(): string {
        return $this->name;
    }

    public function getDescription(): string {
        return $this->description;
    }

    public function getSlot(): string {
        return $this->slot;
    }

    public function isPassive(): bool {
        return $this->passive;
    }

    public function getStatus(): string {
        return $this->status;
    }

    public function setStatus(string $status): self {
        $this->status = $status;
        return $this;
    }

    /** @return BaseAction[] */
    public function getActions(Battle $battle, Member $action_owner, Member $round_opponent): array {
        return [];
    }

    public function activate() {
        $this->activated = true;
    }

    public function deactivate() {
        $this->activated = false;
    }

    public function toArray() {
        return [
            'id' => $this->getId(),
            'code' => $this->getCode(),
            'name' => $this->getName(),
            'description' => $this->getDescription(),
            'energy_coast' => $this->getEnergyCoast(),
            'energy_bonus' => $this->getEnergyBonus(),
            'health_bonus' => $this->getHealthBonus(),
            'damage' => $this->getDamage(),
            'slots' => $this->getSlots(),
            'slot' => $this->getSlot(),
            'status' => $this->getStatus(),
        ];
    }
}
