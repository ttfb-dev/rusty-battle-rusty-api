<?php


namespace App\Battle\Modules\Head;


use App\Battle\Actions\BlockRandomMdlAction;
use App\Battle\Actions\DamageEnergyAction;
use App\Battle\Battle;
use App\Battle\Member;
use App\Battle\Modules\BaseModule;

class LaserModule extends BaseModule
{
    public function getActions(Battle $battle, Member $action_owner, Member $round_opponent): array
    {
        return [
            (new DamageEnergyAction($action_owner, $round_opponent, $battle->getFightRoundnumber(), $this->getId()))->setDamage($this->getDamage()),
        ];
    }
}
