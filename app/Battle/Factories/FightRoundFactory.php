<?php


namespace App\Battle\Factories;

use App\Battle\Battle;
use App\Battle\FightRound;

class FightRoundFactory
{
    public static function newArmingRound(Battle $battle) {
        $arming_rounds = $battle->getFightRounds();
        $last_arming_round = end($arming_rounds);
        $last_arming_round_number = $last_arming_round instanceof FightRound ? $last_arming_round->getRoundNumber() : 0;
        return (new FightRound())->setRoundNumber($last_arming_round_number + 1);
    }
}
