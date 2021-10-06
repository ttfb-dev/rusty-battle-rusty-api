<?php

namespace App\Http\Controllers;

use App\Domains\Member;
use App\Modules\ModulesCollection;
use App\Services\ArmingRoundService;
use App\Services\BattleService;
use App\Services\FightLog;
use App\Services\FightRoundService;
use App\Services\RobotsService;
use Illuminate\Http\Request;

class BattleController extends Controller
{
    public function dev() {

    }

    public function create(Request $request, BattleService $battleService) {
        $ids = $request->get('user_ids');
        $source = $request->get('source');
        $members = [];

        foreach ($ids as $id) {
            $members []= (new Member($source))->setOwnerId($id);
        }

        $battle = $battleService->createWithCore($members);

        return response()->json([
            'battle_id' => $battle->model->id,
            'status' => $battle->getStatus(),
        ]);
    }

    public function getArmingRound(int $battle_id, Request $request, ArmingRoundService $armingRoundService, RobotsService $robotsService) {
        $user_id = $request->get('user_id');
        $source = $request->get('source');

        $member = (new Member($source))->setOwnerId($user_id);

        $round = $armingRoundService->getCurrent($battle_id);

        if (empty($round->getProposedUserModules($user_id))) {

            $robot = $robotsService->load($member, $battle_id);
            $using_modules = $robot->getModules();
            $armingRoundService->initProposedModules($round, $user_id, 3, $using_modules);
        }

        return response()->json([
            'modules' => ModulesCollection::toApiArrays($round->getProposedUserModules($user_id)),
            'round_number' => $round->round_number,
        ]);
    }

    public function chooseModuleInArmingRound(int $battle_id, Request $request, ArmingRoundService $armingRoundService, RobotsService $robotsService) {
        $user_id = $request->get('user_id');
        $source = $request->get('source');
        $module = $request->get('module');
        $slot = $request->get('slot');

        $member = (new Member($source))->setOwnerId($user_id);

        $armingRound = $armingRoundService->getCurrent($battle_id);

        if (!empty($module)) {

            $module_to_select = ModulesCollection::createFromCode($module);

            throw_if(!in_array($slot, $module_to_select->getSlots()), new \Exception('Нельзя установить модуль в этот слот'));

            $proposed_modules = $armingRound->getProposedUserModules($user_id);

            $diff = ModulesCollection::exclude([$module_to_select], $proposed_modules);

            throw_if(count($diff) > 0, new \Exception('Вам такие модули не предлагались'));

            $armingRound->withSelectedUserModules([$module_to_select], $user_id);
            $armingRound->save();

            $robot = $robotsService->load($member, $battle_id);
            $robot->addModule($module_to_select, $slot);

        }

        $armingRoundService->finish($armingRound);

        return response()->json(['status' => 'ok']);
    }

    public function getRobot(int $battle_id, Request $request, RobotsService $robotsService) {
        $user_id = $request->get('user_id');
        $source = $request->get('source');

        $member = (new Member($source))->setOwnerId($user_id);

        $robot = $robotsService->load($member, $battle_id);

        return response()->json($robot->toApiArray());
    }

    public function finishArming(int $battle_id, Request $request, BattleService $battleService, RobotsService $robotsService) {
        $battle = $battleService->load($battle_id);

        $battle->finishArming();

        $coreMember = $battle->getCoreMember();

        $coreRobot = $robotsService->load($coreMember, $battle_id);

        $robotsService->fillCoreRobotWithModules($battle, $coreRobot);

        return response()->json(['status' => 'ok']);
    }

    public function getCoreRobot(int $battle_id, Request $request, BattleService $battleService, RobotsService $robotsService) {
        $battle = $battleService->load($battle_id);

        $core_member = $battle->getCoreMember();

        $robot = $robotsService->load($core_member, $battle_id);

        return response()->json($robot->toApiArray());
    }

    public function fightRound(int $battle_id, Request $request, BattleService $battleService, RobotsService $robotsService, FightRoundService $fightRoundService) {
        $user_id = $request->get('user_id');
        $source = $request->get('source');
        $module_ids = $request->get('module_ids', []);

        $member = (new Member($source))->setOwnerId($user_id);
        $battle = $battleService->load($battle_id);
        $fightRound = $fightRoundService->create($battle_id);
        $battle->setFightRound($fightRound);
        $coreModules = $fightRoundService->activateCoreModules($fightRound, $battle);
        $userModules = $fightRoundService->activateUserModules($fightRound, $battle, $member, $module_ids);
        $fightRoundService->setFightActions($userModules, $coreModules, $fightRound, $battle, $member);
        $fightRoundService->processActions($fightRound, $battle);
        $fightRoundService->finish($fightRound, $battle);

        if (count($battle->getWinners())) {
            $winner = $battle->getWinners()[0]->getOwner() === Member::MEMBER_VK ? 'user' : 'core';
        }

        return response()->json([
            'log' => FightLog::read(),
            'status' => $battle->getStatus(),
            'winner' => $winner,
        ]);
    }
}
