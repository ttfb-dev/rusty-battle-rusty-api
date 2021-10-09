<?php


namespace App\Battle\Modules\Head;

use App\Battle\Actions\OneTimeEnergyBonusAction;
use App\Battle\Battle;
use App\Battle\Member;
use App\Battle\Modules\BaseModule;

class BarrierModule extends BaseModule
{
    public function getActions(Battle $battle, Member $action_owner, Member $round_opponent): array
    {
        return [
            (new OneTimeEnergyBonusAction($action_owner, $round_opponent, $battle->getFightRoundnumber(), $this->getId())),
        ];
    }
}
