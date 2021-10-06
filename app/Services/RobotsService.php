<?php


namespace App\Services;

use App\Models\Robot;
use App\Modules\ModulesCollection;
use App\Domains\{Battle, CoreRobot, Member, UserRobot, BaseRobot};

class RobotsService
{
    const ROBOT_MAP = [
        Member::MEMBER_VK => UserRobot::class,
        Member::MEMBER_CORE => CoreRobot::class,
    ];

    private $armingRoundService;

    public function __construct(ArmingRoundService $armingRoundService)
    {
        $this->armingRoundService = $armingRoundService;
    }

    /**
     * @param Member[] $members
     * @return BaseRobot[]
     */
    public function initRobotsForBattle(array $members, int $battle_id): array {
        return array_map(function (Member $member) use ($battle_id) {
            return $this->create($member, $battle_id);
        }, $members);
    }

    /**
     * @param Member[] $members
     * @return BaseRobot[]
     */
    public function loadBattleRobots(array $members, int $battle_id): array {
        return array_map(function (Member $member) use ($battle_id) {
            return $this->load($member, $battle_id);
        }, $members);
    }

    public function create(Member $member, int $battle_id): BaseRobot {
        $robot_class = self::ROBOT_MAP[$member->getOwner()];
        throw_if(!class_exists($robot_class), new \Exception('Для такого owner робота ещё не придумали'));
        $model = $this->createRobotModel($member, $battle_id);
        /** @var BaseRobot $robot */
        $robot = (new $robot_class($model));
        return $robot;
    }

    public function load(Member $member, int $battle_id): BaseRobot {
        $robot_class = self::ROBOT_MAP[$member->getOwner()];
        throw_if(!class_exists($robot_class), new \Exception('Для такого owner робота ещё не придумали'));
        $model = $this->loadRobotModel($member, $battle_id);
        /** @var BaseRobot $robot */
        $robot = (new $robot_class($model));
        return $robot;
    }

    private function createRobotModel(Member $member, int $battle_id): Robot {
        $robot = new Robot();
        $robot->withOwner($member);
        $baseHealth = ConfigService::getRobotBaseHealth($member->getOwner());
        $baseEnergy = ConfigService::getRobotBaseEnergy($member->getOwner());
        $robot->health_base = $baseHealth;
        $robot->health_max = $baseHealth;
        $robot->health = $baseHealth;
        $robot->energy_base = $baseEnergy;
        $robot->energy_max = $baseEnergy;
        $robot->energy = $baseEnergy;
        $robot->battle_id = $battle_id;
        $robot->save();

        return $robot;
    }

    private function loadRobotModel(Member $member, int $battle_id): ?Robot {
        return Robot::query()
            ->where('owner', $member->toString())
            ->where('battle_id', $battle_id)
            ->get()->first();
    }

    /** @return BaseRobot[] */
    public function getRobots(Battle $battle): array {
        $members = $battle->getMembers();
        $robots = [];
        foreach ($members as $member) {
            $robots []= $this->load($member, $battle->getId());
        }
        return $robots;
    }

    /** @param UserRobot[] $userRobots */
    public function fillCoreRobotWithModules(Battle $battle, BaseRobot $core_robot) {
        $exclude_modules = [];
        $user_robots = $this->getRobots($battle);

        foreach ($user_robots as $user_robot) {
            $user_modules = array_values($user_robot->getModules());
            array_push($exclude_modules, ...$user_modules);
        }

        $limit = $this->getCoreModulesLimit(
            count($this->armingRoundService->getAllArmingRounds(
                $battle->getId()
            ))
        );

        $free_modules = ModulesCollection::getShuffledWithLimitWithoutExcludedAndGarbage($user_modules, $limit);

        foreach ($free_modules as $free_module) {
            $slots = $free_module->getSlots();
            $core_robot->addModule($free_module, $slots[array_rand($slots, 1)]);
        }
    }

    private function getCoreModulesLimit(int $arming_rounds_count): int {
        return ConfigService::getGeneral('core_slots_with_arming_rounds', [])[$arming_rounds_count] ?? 3;
    }
}
