<?php


namespace App\Battle\Factories;


use App\Battle\Battle;
use App\Battle\Member;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use \App\Models\Battle as BattleModel;

class BattleFactory
{
    /** @param Member $members */
    public static function createWithCore(array $members): Battle {
        $members []= (new Member(Member::MEMBER_CORE))->setOwnerId(Str::uuid());

        $battle = self::createBattle()->setMembers($members);

        foreach ($members as $member) {
            $battle->setMemberRobot($member, RobotsFactory::createMemberRobot($member));
        }
        return $battle;
    }

    private static function createBattle(): Battle {
        $id = DB::table('battle_line')->insertGetId(['line' => '']);
        $battle = new Battle($id);
        $battle->save();
        return $battle;
    }
}
