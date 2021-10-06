<?php


namespace App\Modules\Weapon;

use App\Actions\DamageHealthAction;
use App\Domains\Battle;
use App\Domains\Member;
use App\Modules\BaseModule;

class KnifeModule extends BaseModule
{
    public function getActions(Battle $battle, Member $action_owner, Member $round_appanent): array
    {
        return [
            (new DamageHealthAction($round_appanent, $battle->getFightRoundNumber(), $this))->setDamage($this->getDamage())
        ];
    }
}
