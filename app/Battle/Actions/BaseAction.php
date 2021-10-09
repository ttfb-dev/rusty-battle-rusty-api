<?php


namespace App\Battle\Actions;

use App\Battle\Battle;
use App\Battle\Member;
use App\Battle\FightRound;
use App\Battle\Modules\BaseModule;
use App\Traits\CodeTrait;

abstract class BaseAction
{
    use CodeTrait;

    const SORT_DISABLE_MODULE               = 0;
    const SORT_DISABLE_ATTACK               = 1;
    const SORT_SPECIFICATIONS_BONUS         = 2;
    const SORT_DAMAGE_REDUCTION             = 3;
    const SORT_DAMAGE_BONUS                 = 4;
    const SORT_DAMAGE                       = 5;
    const SORT_MODULE_RECHARGE              = 6;
    const SORT_MODULE_CHANGE_ENERGY_COAST   = 7;

    /** @var int */
    protected $round_number;

    /** @var Member */
    protected $target;

    /** @var Member */
    protected $author;

    /** @var bool */
    protected $is_active = true;

    /** @var bool */
    protected $used = false;

    /** @var int */
    protected $damage = 0;

    /** @var int */
    protected $module_id = null;

    public function __construct(Member $author, Member $target, int $round_number, int $module_id)
    {
        $this->target = $target;
        $this->round_number = $round_number;
        $this->module_id = $module_id;
        $this->author = $author;
    }

    public function getRoundNumber(): int {
        return $this->round_number;
    }

    public function getTarget(): Member {
        return $this->target;
    }

    public function getAuthor(): Member {
        return $this->author;
    }

    public function isInRound(int $round_number): bool {
        return is_null($this->round_number) || $this->round_number === $round_number;
    }

    public function setActive(bool $is_active): self {
        $this->is_active = $is_active;
        return $this;
    }
    public function isActive(): bool {
        return $this->is_active;
    }

    public function setDamage(int $damage): self {
        $this->damage = $damage;
        return $this;
    }

    public function getDamage(): int {
        return $this->damage;
    }

    abstract public function getSort(): int;

    abstract public function handle(Battle $battle, FightRound $fightRound): bool;

    public function toArray(): array {
        return [
            'id' => $this->getCode(),
            'round' => $this->getRoundNumber(),
            'target' => $this->getTarget()->toString(),
            'is_active' => $this->isActive(),
            'damage' => $this->getDamage(),
            'used' => $this->isUsed(),
            'module_id' => $this->module_id,
        ];
    }

    public function isUsed(): bool {
        return $this->used;
    }

    public function markUsed(bool $used): self {
        $this->used = $used;
        return $this;
    }

    public function setModuleId(int $module_id): self {
        $this->module_id = $module_id;
        return $this;
    }

    public function getModuleId(): int {
        return $this->module_id;
    }

    public function getModule(Battle $battle): ?BaseModule {
        if (is_null($this->module_id)) {
            return null;
        }
        $robot = $battle->getMemberRobot($this->author);
        return $robot->getModuleById($this->module_id);
    }
}
