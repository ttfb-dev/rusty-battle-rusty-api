<?php

namespace App\Models;

use App\Actions\BaseAction;
use App\Domains\Member;
use App\Modules\BaseModule;
use App\Services\FightLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

/**
 * Class FightRound
 * @property int $id
 * @property int $battle_id
 * @property int $round_number
 * @property string $status
 * @property string[] $log
 * @package App\Models
 */
class FightRound extends Model
{
    const STATUS_STARTED  = 'started';
    const STATUS_FINISHED = 'finished';

    /** @var BaseAction[][] */
    private $loaded_actions = null;

    protected $fillable = [
        'battle_id',
        'round_number',
        'modules',
        'actions',
        'robots',
        'log',
        'status'
    ];

    protected $casts = [
        'log' => 'array'
    ];

    protected $attributes = [
        'status' => self::STATUS_STARTED,
    ];

    /** @param BaseModule[] $modules */
    public function withModules(Member $member, array $modules) {
        $curr_modules = json_decode($this->modules, true) ?? [];
        $curr_modules[$member->toString()] = array_map(function (BaseModule $module) { return $module->getCode(); }, $modules);
        $this->modules = json_encode($curr_modules);
    }

    /** @return string[] */
    public function getModulesCodes(): array {
        return json_decode($this->modules, true);
    }

    /** @param BaseAction[][] $actions */
    private function saveActions(array $actions) {
        $actions_to_save = [];
        foreach ($actions as $sort => $action_list) {
            foreach ($action_list as $action) {
                $actions_to_save []= $action->toArray();
            }
        }
        $this->actions = json_encode($actions_to_save);
    }

    /** @return BaseAction[][] */
    private function loadActions(): array {
        $actions_to_get = [];
        $actions_array = json_decode($this->actions, true);
        foreach ($actions_array as $action_array) {
            $action = BaseAction::fromArray($action_array);
            $actions_to_get[$action->getSort()][] = $action;
        }
        return $actions_to_get;
    }

    public function withActions(array $actions) {
        $this->loaded_actions = $actions;
        $this->saveActions($actions);
    }

    public function getActions(): array {
        if (!$this->loaded_actions) {
            $this->loaded_actions = $this->loadActions();
        }
        return $this->loaded_actions;
    }

    public function cancelUnusedActions(Member $target) {
        $sorted_actions = $this->getActions();

        foreach ($sorted_actions as $sort => &$actions) {
            foreach ($actions as $key => &$action) {
                if ($action->isUsed() || !$action->isActive()) {
                    continue;
                }
                if ($action->getTarget()->toString() === $target->toString()) {
                    FightLog::write("Действие {$action->getCode()} по роботу {$target->getOwner()} отменено");
                    $action->setActive(false);
                }
            }
        }

        $this->withActions($sorted_actions);
    }
}
