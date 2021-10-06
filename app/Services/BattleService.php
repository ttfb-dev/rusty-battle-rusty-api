<?php


namespace App\Services;


use App\Domains\Member;
use App\Models\Battle as BattleModel;
use \App\Domains\Battle;
use Illuminate\Support\Str;

class BattleService
{
    private $robotsService;

    public function __construct(RobotsService $robotsService) {
        $this->robotsService = $robotsService;
    }

    /** @param Member[] $members */
    public function create(array $members): Battle {
        $battleModel = $this->createModel($members);
        $battle = new Battle($battleModel);
        $robots = $this->robotsService->initRobotsForBattle($battle->getMembers(), $battle->getId());
        $battle->withRobots($robots);
        return $battle;
    }

    public function load(int $battle_id): Battle {
        $battleModel = $this->getModel($battle_id);
        throw_if(!($battleModel instanceof BattleModel), new \Exception('Бой с таким ID не найден'));
        $battle = new Battle($battleModel);
        $robots = $this->robotsService->loadBattleRobots($battle->getMembers(), $battle->getId());
        $battle->withRobots($robots);
        return $battle;
    }

    /** @param Member[] $members */
    public function createWithCore(array $members): Battle {
        $coreMember = (new Member(Member::MEMBER_CORE))->setOwnerId(Str::uuid());
        $members []= $coreMember;
        return $this->create($members);
    }

    /** @param Member[] $members */
    private function createModel(array $members): BattleModel {
        $battle = new BattleModel();
        $battle->withMembers($members);
        $battle->save();
        return $battle;
    }

    private function getModel(int $battle_id): ?BattleModel {
        return BattleModel::find($battle_id);
    }
}
