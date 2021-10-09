<?php


namespace App\Battle\Modules\Foot;


use App\Battle\Actions\DamageMultiplicationNextMdlAction;
use App\Battle\Battle;
use App\Battle\Member;
use App\Battle\Modules\BaseModule;

class AttackModule extends BaseModule
{
    public function getActions(Battle $battle, Member $action_owner, Member $round_opponent): array
    {
        return [
            (new DamageMultiplicationNextMdlAction($action_owner, $round_opponent, $battle->getFightRoundnumber(), $this->getId()))
        ];
    }
}
