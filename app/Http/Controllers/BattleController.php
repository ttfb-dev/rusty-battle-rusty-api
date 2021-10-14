<?php

namespace App\Http\Controllers;

use App\Battle\Battle;
use App\Battle\Factories\BattleFactory;
use App\Battle\Factories\ModulesFactory;
use App\Battle\Member;
use App\Services\ConfigService;
use App\Services\FightLog;
use App\Services\LeaderboardService;
use App\Services\VKApiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class BattleController extends Controller
{
    public function dev() {
    }

    public function create(Request $request) {
        $user_id = $request->get('user_id');
        $source = $request->get('source');

        $member = (new Member($source))->setOwnerId($user_id);

        DB::beginTransaction();
        try {
            $battle = BattleFactory::createWithCore([$member]);

            $battle->save();

            DB::commit();
        } catch (\Exception $e) {
            Log::error($e->getMessage(), [$e->getFile(), $e->getLine()]);
            DB::rollback();
        }

        return response()->json([
            'battle_id' => $battle->getId(),
            'status' => $battle->getStatus(),
        ]);
    }

    public function getArmingRound(int $battle_id, Request $request) {
        $user_id = $request->get('user_id');
        $source = $request->get('source');
        $member = (new Member($source))->setOwnerId($user_id);
        DB::beginTransaction();
        try {
            $battle = Battle::load($battle_id);

            $arming_round = $battle->getCurrentArmingRoundOrCreate();

            $user_robot = $battle->getMemberRobot($member);

            $proposed_modules = $arming_round->getProposedMemberModules($member);

            if (count($proposed_modules) === 0) {

                $installed_modules = array_values($user_robot->getModules());

                $proposed_modules = ModulesFactory::getShuffledWithLimitAndExcluded($installed_modules, 3);

                $arming_round->setProposedMemberModules($member, $proposed_modules);

                $battle->save();

                DB::commit();
            }
        } catch (\Exception $e) {
            Log::error($e->getMessage(), [$e->getFile(), $e->getLine()]);
            DB::rollback();
        }

        return response()->json([
            'modules' => ModulesFactory::toArrays($arming_round->getProposedMemberModules($member)),
            'round_number' => $arming_round->getRoundNumber(),
        ]);
    }

    public function chooseModuleInArmingRound(int $battle_id, Request $request) {
        $user_id = $request->get('user_id');
        $source = $request->get('source');
        $module = $request->get('module');
        $slot = $request->get('slot');

        $member = (new Member($source))->setOwnerId($user_id);

        DB::beginTransaction();
        try {
            $battle = Battle::load($battle_id);

            $arming_round = $battle->getCurrentArmingRoundOrCreate();

            if (!empty($module)) {

                $module_to_install = ModulesFactory::createFromCode($module);

                throw_if(!in_array($slot, $module_to_install->getSlots()), new \Exception('Нельзя установить модуль в этот слот'));

                $proposed_modules = $arming_round->getProposedMemberModules($member);

                $diff = ModulesFactory::exclude([$module_to_install], $proposed_modules);

                throw_if(count($diff) > 0, new \Exception('Вам такие модули не предлагались'));

                $robot = $battle->getMemberRobot($member);

                $robot->addModule($module_to_install, $slot);
            }

            $arming_round->finish();

            $battle->addPoints(ConfigService::getPoints('finish_arming_round'));

            $battle->save();

            DB::commit();
        } catch (\Exception $e) {
            Log::error($e->getMessage(), [$e->getFile(), $e->getLine()]);
            DB::rollback();
        }

        return response()->json(['status' => 'ok']);
    }

    public function getRobot(int $battle_id, Request $request) {
        $user_id = $request->get('user_id');
        $source = $request->get('source');

        $member = (new Member($source))->setOwnerId($user_id);

        $battle = Battle::load($battle_id);

        $robot = $battle->getMemberRobot($member);

        return response()->json($robot->toArray());
    }

    public function finishArming(int $battle_id) {
        DB::beginTransaction();
        try {
            $battle = Battle::load($battle_id);

            $battle->deleteStartedArmingRounds();

            $core_member = $battle->getCoreMember();

            $core_robot = $battle->getMemberRobot($core_member);

            $core_robot->fillWithRandomModules($battle);

            $battle->finishArming();

            $battle->save();

            DB::commit();
        } catch (\Exception $e) {
            Log::error($e->getMessage(), [$e->getFile(), $e->getLine()]);
            DB::rollback();
        }

        return response()->json(['status' => $battle->getStatus()]);
    }

    public function getCoreRobot(int $battle_id) {
        $battle = Battle::load($battle_id);

        $core_member = $battle->getCoreMember();

        $core_robot = $battle->getMemberRobot($core_member);

        return response()->json($core_robot->toArray());
    }

    public function fightRound(int $battle_id, Request $request) {
        $user_id = $request->get('user_id');
        $source = $request->get('source');
        $modules_ids = $request->get('modules_ids', []);

        $member = (new Member($source))->setOwnerId($user_id);

        try {
            $battle = Battle::load($battle_id);

            $fight_round = $battle->getNewFightRound();

            $core_robot = $battle->getMemberRobot($battle->getCoreMember());
            $core_fight_modules = $core_robot->getRandomCoreModules();
            $core_robot->activateModules($core_fight_modules);
            $fight_round->setMemberModules($battle->getCoreMember(), $core_fight_modules);

            $user_robot = $battle->getMemberRobot($member);
            $user_fight_modules = $user_robot->getModulesByIds($modules_ids);
            $user_robot->activateModules($user_fight_modules);
            $fight_round->setMemberModules($member, $user_fight_modules);

            $fight_round->fillActions($battle);

            $fight_round->processActions($battle);

            $fight_round->finish($battle);

            $battle->addPoints(ConfigService::getPoints('finish_fight_round'));

            $battle->save();

            DB::commit();
        } catch (\Exception $e) {
            Log::error($e->getMessage(), [$e->getFile(), $e->getLine()]);
            DB::rollback();
        }

        FightLog::clear();

        $winner = count($battle->getWinners()) ? $battle->getWinners()[0]->getOwner() : '';

        return response()->json([
            'log' => $fight_round->getLog(),
            'status' => $battle->getStatus(),
            'winner' => $winner,
            'round_number' => $fight_round->getRoundNumber(),
            'points' => $battle->getStatus() === Battle::STATUS_FINISHED ? $battle->getPoints() : 0,
        ]);
    }

    public function whereIAm(Request $request) {
        $user_id = $request->get('user_id');
        $source = $request->get('source');
        $member = (new Member($source))->setOwnerId($user_id);

        $battle = Battle::whereIAm($member);

        if (!($battle instanceof Battle)) {
            return response()->json([
                'battle_id' => null,
                'status' => null,
            ]);
        }
        return response()->json([
            'battle_id' => $battle->getId(),
            'status' => $battle->getStatus(),
        ]);
    }

    public function forceFinish(int $battle_id) {
        $battle = Battle::load($battle_id);
        $battle->finish(true);
        $battle->save();

        return response()->json([
            'battle_id' => $battle->getId(),
            'status' => $battle->getStatus(),
        ]);
    }

    public function topList(Request $request, LeaderboardService $leaderboardService) {
        $source = $request->get('source');
        $rows = $leaderboardService->getLeaderBoard($source);

        return response()->json(['top' => $rows]);
    }

}
