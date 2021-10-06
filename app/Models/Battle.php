<?php

namespace App\Models;

use App\Domains\Member;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Battle
 * @property int $id
 * @property string $status
 * @package App\Models
 */
class Battle extends Model
{
    const STATUS_ARMING     = 'arming';
    const STATUS_BATTLE     = 'battle';
    const STATUS_FINISHED   = 'finished';

    protected $fillable = [
        'members',
        'status',
        'winners',
    ];

    protected $attributes = [
        'status' => self::STATUS_ARMING,
    ];

    /** @return Member[] */
    public function getMembers(): array {
        return array_map(function (string $member): Member {
            return Member::fromString($member);
        }, json_decode($this->members, true) ?? []);
    }

    public function withMembers(array $members) {
        $this->members = json_encode(array_map(function (Member $member) {
            return $member->toString();
        }, $members));
    }

    public function setWinners(array $winners) {
        $this->winners = json_encode(array_map(function (Member $winners) {
            return $winners->toString();
        }, $winners));
    }

    public function getWinners() {
        return array_map(function (string $winner): Member {
            return Member::fromString($winner);
        }, json_decode($this->winners, true) ?? []);
    }
}
