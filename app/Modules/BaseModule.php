<?php


namespace App\Modules;


use App\Actions\BaseAction;
use App\Domains\BaseRobot;
use App\Domains\Battle;
use App\Domains\Member;
use App\Services\ConfigService;
use App\Traits\CodeTrait;

class BaseModule
{
    use CodeTrait;

    const EFFECT_COOLDOWN = 'cooldown';
    const EFFECT_STUN     = 'stun';

    const DISABLE_EFFECTS = [
        self::EFFECT_COOLDOWN,
        self::EFFECT_STUN,
    ];

    /** @var BaseRobot */
    protected $robot;

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

    /** @var string[] */
    protected $effects = [];

    /** @var bool */
    protected $passive = false;

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

    public function setRobot(BaseRobot $robot): self {
        $this->robot = $robot;
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

    public function getRobot(): ?BaseRobot {
        return $this->robot;
    }

    public function getSlot(): string {
        return $this->slot;
    }

    public function isPassive(): bool {
        return $this->passive;
    }

    /** @return BaseAction[] */
    public function getActions(Battle $battle, Member $action_owner, Member $round_appanent): array {
        return [];
    }

    public function isActive(): bool {
        foreach ($this->effects as $effect) {
            if (in_array($effect, self::DISABLE_EFFECTS)) {
                return false;
            }
        }
        return true;
    }

    public function setEffect(string $effect) {
        $this->effects []= $effect;
    }

    /** @param string[] $effects */
    public function setEffects(array $effects): self {
        $this->effects = $effects;
        return $this;
    }

    /** @return string[] */
    public function getEffects(): array {
        return $this->effects;
    }

    public function removeEffect(string $removing_effect) {
        foreach ($this->effects as $index => $effect) {
            if ($effect === $removing_effect) {
                unset($this->effects[$index]);
                break;
            }
        }
        $this->effects = array_values($this->effects);
    }

    public static function fromArray(array $module_array): self {
        $module = ModulesCollection::createFromCode($module_array['id']);
        return $module
            ->setEnergyCoast($module_array['energy_coast'])
            ->setEnergyBonus($module_array['energy_bonus'])
            ->setHealthBonus($module_array['health_bonus'])
            ->setDamage($module_array['damage'])
            ->setSlot($module_array['slot'])
            ->setEffects($module_array['effects']);
    }

    public function toArray() {
        return [
            'id' => $this->getCode(),
            'name' => $this->getName(),
            'description' => $this->getDescription(),
            'energy_coast' => $this->getEnergyCoast(),
            'energy_bonus' => $this->getEnergyBonus(),
            'health_bonus' => $this->getHealthBonus(),
            'damage' => $this->getDamage(),
            'slots' => $this->getSlots(),
            'slot' => $this->getSlot(),
            'effects' => $this->getEffects(),
        ];
    }

    public function toApi() {
        return [
            'id' => $this->getCode(),
            'title' => $this->getName(),
            'description' => $this->getDescription(),
            'energy' => $this->getEnergyCoast(),
            'health_add' => $this->getHealthBonus(),
            'damage' => $this->getDamage(),
            'energy_add' => $this->getEnergyBonus(),
            'status' => '',
            'image' => '',
            'slot' => $this->getSlot(),
        ];
    }
}
