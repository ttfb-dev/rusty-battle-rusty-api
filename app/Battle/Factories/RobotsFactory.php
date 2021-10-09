<?php


namespace App\Battle\Factories;


use App\Battle\Robots\BaseRobot;
use App\Battle\Robots\CoreRobot;
use App\Battle\Member;
use App\Battle\Robots\UserRobot;

class RobotsFactory
{
    const ROBOT_MAP = [
        Member::MEMBER_VK => UserRobot::class,
        Member::MEMBER_CORE => CoreRobot::class,
    ];

    public static function createMemberRobot(Member $member): BaseRobot {
        $robot_class = self::ROBOT_MAP[$member->getOwner()];
        throw_if(!class_exists($robot_class), new \Exception('Для такого owner робота ещё не придумали'));
        /** @var BaseRobot $robot */
        $robot = (new $robot_class());
        return $robot;
    }
}
