<?php


namespace App\Services;

use App\Models\ArmingRound;
use App\Modules\BaseModule;
use App\Modules\ModulesCollection;

class ArmingRoundService
{

    public function getCurrent(int $battle_id): ArmingRound {
        $round = ArmingRound::query()
            ->where('battle_id', $battle_id)
            ->where('status', ArmingRound::STATUS_CHOOSING)
            ->first();

        return $round instanceof ArmingRound ? $round : $this->create($battle_id);
    }

    private function getLastFinished(int $battle_id): ?ArmingRound {
        return ArmingRound::query()
            ->where('battle_id', $battle_id)
            ->where('status', ArmingRound::STATUS_FINISHED)
            ->orderBy('id', 'desc')
            ->first();
    }

    private function getNextNumber(int $battle_id): int {
        $last_round = $this->getLastFinished($battle_id);
        return $last_round instanceof ArmingRound ? $last_round->round_number + 1 : 1;
    }

    private function create(int $battle_id): ArmingRound {
        $number = $this->getNextNumber($battle_id);
        throw_if(
            $number > (int)ConfigService::getGeneral('arming_rounds', 10),
            new \Exception('Раунды закончились, пора в бой!')
        );
        $round = new ArmingRound(['battle_id' => $battle_id, 'round_number' => $number]);
        $round->save();
        return $round;
    }

    /** @return ArmingRound[] */
    public function getAllArmingRounds(int $battle_id): array {
        $rounds = ArmingRound::query()
            ->where('battle_id', $battle_id)
            ->get()->all();

        return is_array($rounds) ? $rounds : [$rounds];
    }

    /** @return BaseModule[] */
    public function getAllUsedModules(int $battle_id): array {
        $rounds = $this->getAllArmingRounds($battle_id);
        $modules = [];
        foreach($rounds as $round) {
            $module_map = $round->getSelectedModules();
            foreach ($module_map as $user_modules) {
                array_push($modules, ...$user_modules);
            }
        }

        return $modules;
    }

    /** @return BaseModule[] */
    public function getAllShowedModules(int $battle_id): array {
        $rounds = $this->getAllArmingRounds($battle_id);
        $modules = [];
        foreach($rounds as $round) {
            $module_map = $round->getProposedModules();
            foreach ($module_map as $user_modules) {
                array_push($modules, ...$user_modules);
            }
        }

        return $modules;
    }

    public function initProposedModules(ArmingRound $armingRound, int $user_id, int $limit, $excluded) {
        $modules = ModulesCollection::getShuffledWithLimitAndExcluded($excluded, $limit);
        $armingRound->withProposedUserModules($modules, $user_id);
        $armingRound->save();
    }

    public function finish(ArmingRound $armingRound) {
        $armingRound->status = ArmingRound::STATUS_FINISHED;
        $armingRound->save();
    }
}
