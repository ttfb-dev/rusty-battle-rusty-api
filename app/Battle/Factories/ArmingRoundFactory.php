<?php


namespace App\Battle\Factories;


use App\Battle\ArmingRound;
use App\Battle\Battle;

class ArmingRoundFactory
{
    public static function newArmingRound(Battle $battle) {
        $arming_rounds = $battle->getArmingRounds();
        $last_arming_round = end($arming_rounds);
        $last_arming_round_number = $last_arming_round instanceof ArmingRound ? $last_arming_round->getRoundNumber() : 0;
        return (new ArmingRound())->setRoundNumber($last_arming_round_number + 1);
    }
}
