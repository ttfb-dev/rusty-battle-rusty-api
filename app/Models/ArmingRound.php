<?php

namespace App\Models;

use App\Modules\BaseModule;
use Illuminate\Database\Eloquent\Model;

/**
 * Class ArmingRound
 * @property int $id
 * @property int $battle_id
 * @property int $round_number
 * @property string $status
 * @package App\Models
 */
class ArmingRound extends Model
{
    const STATUS_CHOOSING = 'choosing';
    const STATUS_CANCELED = 'canceled';
    const STATUS_FINISHED = 'finished';

    protected $fillable = [
        'battle_id',
        'round_number',
        'status',
        'proposed_modules',
        'selected_modules',
    ];

    protected $casts = [
        'proposed_modules' => 'array',
        'selected_modules' => 'array',
    ];

    protected $attributes = [
        'round_number' => 1,
        'status' => self::STATUS_CHOOSING,
    ];

    /** @return BaseModule[][] */
    public function getProposedModules() {
        $modules = [];
        if (isset($this->proposed_modules)) {
            $data = json_decode($this->proposed_modules, true);
            foreach ($data as $user_id => $modules_arrays) {
                foreach ($modules_arrays as $module_array) {
                    $modules[$user_id][] = (new $module_array);
                }
            }
        }
        return $modules;
    }

    /** @return BaseModule[] */
    public function getProposedUserModules(int $user_id): array {
        return $this->getProposedModules()[$user_id] ?? [];
    }

    /* @param BaseModule[][] $proposed_modules */
    public function withProposedModules(array $proposed_modules) {
        $proposed_modules_map = [];
        foreach ($proposed_modules as $user_id => $modules) {
            foreach ($modules as $module) {
                $proposed_modules_map[$user_id][] = get_class($module);
            }
        }
        $this->proposed_modules = json_encode($proposed_modules_map);
    }

    public function withProposedUserModules(array $proposed_modules, $user_id) {
        $already_proposed = $this->getProposedModules();
        $already_proposed[$user_id] = $proposed_modules;
        $this->withProposedModules($already_proposed);
    }

    /** @return BaseModule[][] */
    public function getSelectedModules() {
        $modules = [];
        if (isset($this->selected_modules)) {
            $data = json_decode($this->selected_modules, true);
            foreach ($data as $user_id => $modules_arrays) {
                foreach ($modules_arrays as $module_array) {
                    $modules[$user_id][] = (new $module_array);
                }
            }
        }
        return $modules;
    }

    /** @return BaseModule[] */
    public function getSelectedUserModules(int $user_id): array {
        return $this->getSelectedModules()[$user_id] ?? [];
    }

    public function withSelectedModules(array $selected_modules) {
        $selected_modules_map = [];
        foreach ($selected_modules as $user_id => $modules) {
            foreach ($modules as $module) {
                $selected_modules_map[$user_id][] = get_class($module);
            }
        }
        $this->selected_modules = json_encode($selected_modules_map);
    }

    public function withSelectedUserModules(array $selected_modules, $user_id) {
        $already_selected = $this->getSelectedModules();
        $already_selected[$user_id] = $selected_modules;
        $this->withSelectedModules($already_selected);
    }
}
