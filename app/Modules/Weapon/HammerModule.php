<?php


namespace App\Modules\Weapon;


use App\Actions\DamageEnergyAction;
use App\Domains\Battle;
use App\Domains\Member;
use App\Modules\BaseModule;

class HammerModule extends BaseModule
{
    public function getActions(Battle $battle, Member $action_owner, Member $round_appanent): array
    {
        return [
            (new DamageEnergyAction($round_appanent, $battle->getFightRoundNumber(), $this))->setDamage($this->getDamage())
        ];
    }
}
