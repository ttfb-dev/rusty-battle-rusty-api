<?php


namespace App\Services;

use App\Actions\BaseAction;
use App\Domains\Battle;
use App\Domains\Member;
use App\Models\FightRound;
use App\Modules\BaseModule;

class FightRoundService
{
    /** @var FightRound */
    private $model;

    /** @var CoreRobotService */
    private $coreRobotService;

    public function __construct(CoreRobotService $coreRobotService)
    {
        $this->coreRobotService = $coreRobotService;
    }

    private function getLastFinishedModel(int $battle_id): ?FightRound {
        return FightRound::query()
            ->where('battle_id', $battle_id)
            ->where('status', FightRound::STATUS_FINISHED)
            ->orderBy('id', 'desc')
            ->first();
    }

    private function getNextNumber(int $battle_id): int {
        $last_round = $this->getLastFinishedModel($battle_id);
        return $last_round instanceof FightRound ? $last_round->round_number + 1 : 1;
    }

    public function create(int $battle_id): FightRound {
        $number = $this->getNextNumber($battle_id);
        $round = new FightRound(['battle_id' => $battle_id, 'round_number' => $number]);
        $round->save();
        return $round;
    }

    public function finish(FightRound $fightRound, Battle $battle) {
        $fightRound->status = FightRound::STATUS_FINISHED;
        $fightRound->save();

        $this->subtractingHealth($fightRound, $battle);
        $this->restoreEnergy($fightRound, $battle);
        $this->saveFightLog($fightRound);
        $this->checkBattleFinished($fightRound, $battle);
    }

    public function activateCoreModules(FightRound $fightRound, Battle $battle) {
        $coreRobot = $battle->getMemberRobot($battle->getCoreMember());
        $coreModules = $this->coreRobotService->activateRandomCoreModules($coreRobot);
        $fightRound->withModules($battle->getCoreMember(), $coreModules);
        return $coreModules;
    }

    /** @return BaseModule[] */
    public function activateUserModules(FightRound $fightRound, Battle $battle, Member $member, array $module_ids) {
        $userRobot = $battle->getMemberRobot($member);

        $modules = $userRobot->getModules();
        /** @var BaseModule[] $modules_to_activate */
        $modules_to_activate = array_map(function (string $module_id) use (&$modules) {
            foreach ($modules as $slot => $module) {
                if ($module->getCode() === $module_id) {
                    unset($modules[$slot]);
                    return $module;
                }
            }
            return null;
        }, $module_ids);

        $modules_to_activate = array_filter($modules_to_activate);

        $available_energy = $userRobot->getEnergy();
        $energy_used = 0;

        foreach ($modules_to_activate as $module) {
            if ($module->isPassive()) {
                continue;
            }
            if ($available_energy >= $module->getEnergyCoast()) {
                $available_energy -= $module->getEnergyCoast();
                $userRobot->useEnergy($module->getEnergyCoast());
                $energy_used += $module->getEnergyCoast();
            }
        }

        FightLog::write("Робот {$member->getOwner()} использовал на модули {$energy_used} энергии. Осталось {$userRobot->getEnergy()}");

        $fightRound->withModules($member, $modules_to_activate);
        return $modules_to_activate;
    }

    /**
     * @param BaseModule[] $user_modules
     * @param BaseModule[] $core_modules
     * @param FightRound $fightRound
     * @param Battle $battle
     */
    public function setFightActions(array $user_modules, array $core_modules, FightRound $fightRound, Battle $battle, Member $member) {
        /** @var BaseAction[][] $actions */
        $actions = [];
        foreach ($user_modules as $user_module) {
            $module_actions = $user_module->getActions($battle, $member, $battle->getCoreMember());
            foreach ($module_actions as $module_action) {
                $actions[$module_action->getSort()] [] = $module_action;
            }
        }
        foreach ($core_modules as $core_module) {
            $module_actions = $core_module->getActions($battle, $battle->getCoreMember(), $member);
            foreach ($module_actions as $module_action) {
                $actions[$module_action->getSort()] [] = $module_action;
            }
        }
        $fightRound->withActions($actions);
        $fightRound->save();
    }

    public function processActions(FightRound $fightRound, Battle $battle) {
        $robots = $battle->getRobots();
        foreach ($robots as $robot) {
            FightLog::write("Робот {$robot->getOwner()->getOwner()} имеет {$robot->getEnergy()} энергии и {$robot->getHealth()} здоровья");
        }

        foreach ($fightRound->getActions() as $sort => $actions) {
            foreach ($actions as $action) {
                if ($action->isActive()) {
                    if ($action->handle($battle, $fightRound)) {
                        $action->markUsed(true);
                    }
                }
            }
        }
    }

    private function subtractingHealth(FightRound $fightRound, Battle $battle) {
        $robots = $battle->getRobots();
        foreach ($robots as $robot) {
            if ($robot->subtractHealth()) {
                FightLog::write("Робот {$robot->getOwner()->getOwner()} потерял здоровье");
            }
        }
    }

    private function restoreEnergy(FightRound $fightRound, Battle $battle) {
        $robots = $battle->getRobots();
        foreach ($robots as $robot) {
            $energy = $robot->restoreEnergy();
            FightLog::write("Робот {$robot->getOwner()->getOwner()} восстановил энергию ({$energy})");
        }
    }

    private function saveFightLog(FightRound $fightRound) {
        $fightRound->log = FightLog::read();
        $fightRound->save();
    }

    private function checkBattleFinished(FightRound $fightRound, Battle $battle) {
        $battle_finished = false;
        $winners = [];
        $robots = $battle->getRobots();
        foreach ($robots as $robot) {
            if ($robot->getHealth() <= 0) {
                $battle_finished = true;
            } else {
                $winners []= $robot->getOwner();
            }
        }
        if ($battle_finished) {
            $battle->finish($winners);
        }
    }
}
