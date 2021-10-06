<?php

namespace App\Models;

use App\Domains\Member;
use App\Modules\BaseModule;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Robot
 * @package App\Models
 * @property int $battle_id
 * @property string $modules
 * @property int $health_base
 * @property int $energy_base
 * @property int $health_max
 * @property int $energy_max
 * @property int $health
 * @property int $energy
 * @property string $status
 */
class Robot extends Model
{
    const STATUS_READY      = 'ready';
    const STATUS_STUNNED    = 'stunned';

    protected $fillable = [
        'owner',
        'battle_id',
        'modules',
        'health_base',
        'energy_base',
        'health_max',
        'energy_max',
        'health',
        'energy',
        'status',
    ];

    protected $attributes = [
        'status' => self::STATUS_READY,
    ];

    public function withOwner(Member $member) {
        $this->owner = $member->toString();
    }

    /** @return Member */
    public function getOwner() {
        return Member::fromString($this->owner);
    }

}
