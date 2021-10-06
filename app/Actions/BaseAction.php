<?php


namespace App\Actions;


use App\Domains\Battle;
use App\Domains\Member;
use App\Models\FightRound;
use App\Modules\BaseModule;
use App\Modules\ModulesCollection;
use App\Traits\CodeTrait;

abstract class BaseAction
{
    use CodeTrait;

    const SORT_DISABLE_MODULE       = 0;
    const SORT_DISABLE_ATTACK       = 1;
    const SORT_DAMAGE_REDUCTION     = 2;
    const SORT_DAMAGE               = 3;
    const SORT_MODULE_RECHARGE      = 4;

    /** @var int */
    protected $round;

    /** @var Member */
    protected $target;

    /** @var bool */
    protected $is_active = true;

    /** @var bool */
    protected $used = false;

    /** @var int */
    protected $damage;

    /** @var BaseModule */
    protected $module;

    public function __construct(Member $target = null, int $round = null, $module = null)
    {
        $this->target = $target;
        $this->round = $round;
        $this->module = $module;
    }

    public function getRound(): int {
        return $this->round;
    }

    public function getTarget(): Member {
        return $this->target;
    }

    public function isInRound(int $round): bool {
        return is_null($this->round) || $this->round === $round;
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
            'round' => $this->getRound(),
            'target' => $this->getTarget()->toString(),
            'is_active' => $this->isActive(),
            'damage' => $this->getDamage(),
            'used' => $this->isUsed(),
            'module' => $this->module ? $this->module->getCode() : '',
        ];
    }

    public static function fromArray(array $action_array): BaseAction {
        $target = Member::fromString($action_array['target']);
        $round = $action_array['round'];
        $is_active = $action_array['is_active'];
        $action = ActionsCollection::getByCode($action_array['id'], [$target, $round])
            ->setActive($is_active)
            ->setDamage($action_array['damage'])
            ->markUsed($action_array['used']);

        if (strlen($action_array['module'])) {
            $action->byModule(
                ModulesCollection::createFromCode($action_array['module'])
            );
        }

        return $action;
    }

    public function isUsed(): bool {
        return $this->used;
    }

    public function markUsed(bool $used): self {
        $this->used = $used;
        return $this;
    }

    public function byModule(BaseModule $module): self {
        $this->module = $module;
        return $this;
    }

    public function getModule(): ?BaseModule {
        return $this->module;
    }
}
