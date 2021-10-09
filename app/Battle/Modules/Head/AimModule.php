<?php


namespace App\Battle\Modules\Head;

use App\Battle\Actions\DamageBonusNextMdlAction;
use App\Battle\Battle;
use App\Battle\Member;
use App\Battle\Modules\BaseModule;

class AimModule extends BaseModule
{
    public function getActions(Battle $battle, Member $action_owner, Member $round_opponent): array
    {
        return [
            (new DamageBonusNextMdlAction($action_owner, $round_opponent, $battle->getFightRoundnumber(), $this->getId())),
        ];
    }
}
