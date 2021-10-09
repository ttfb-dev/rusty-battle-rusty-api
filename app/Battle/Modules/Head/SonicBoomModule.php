<?php


namespace App\Battle\Modules\Head;


use App\Battle\Actions\DownMdlEnergyCoastAction;
use App\Battle\Actions\UpMdlEnergyCoastAction;
use App\Battle\Battle;
use App\Battle\Member;
use App\Battle\Modules\BaseModule;

class SonicBoomModule extends BaseModule
{
    public function getActions(Battle $battle, Member $action_owner, Member $round_opponent): array
    {
        return [
            (new UpMdlEnergyCoastAction($action_owner, $round_opponent, $battle->getFightRoundnumber(), $this->getId())),
            (new DownMdlEnergyCoastAction($action_owner, $round_opponent, $battle->getFightRoundnumber() + 1, $this->getId()))
        ];
    }
}
