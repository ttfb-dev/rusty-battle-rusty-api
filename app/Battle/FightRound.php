<?php

namespace App\Battle;

use App\Services\FightLog;
use App\Battle\{Actions\BaseAction, Modules\BaseModule};

class FightRound
{
    const STATUS_STARTED  = 'started';
    const STATUS_FINISHED = 'finished';

    /** @var int */
    protected $round_number = 1;

    /** @var BaseModule[][] */ //member->index
    protected $modules = [];

    /** @var BaseAction[][] */ //sort->index
    protected $actions = [];

    protected $status = self::STATUS_STARTED;

    /** @var Member[] */
    protected $members = [];

    /** @var string[]  */
    protected $log = [];

    public function getRoundNumber(): int {
        return $this->round_number;
    }

    public function setRoundNumber(int $round_number): self {
        $this->round_number = $round_number;
        return $this;
    }

    /** @param BaseModule[] $modules */
    public function setMemberModules(Member $member, array $modules) {
        $this->modules[$member->toString()] = $modules;
    }

    /** @return BaseModule[][] */
    public function getModules(): array {
        return $this->modules;
    }

    /** @return BaseModule[] */
    public function getMemberModules(Member $member): array {
        return $this->modules[$member->toString()] ?? [];
    }

    /** @return BaseAction[][] */
    public function getActions(): array {
        return $this->actions;
    }

    public function cancelTargetMemberActions(Battle $battle, Member $member) {
        $sorted_actions = $this->getActions();

        foreach ($sorted_actions as $sort => $actions) {
            foreach ($actions as $action) {
                if ($action->isUsed() || !$action->isActive()) {
                    continue;
                }
                if ($action->getTarget()->equal($member) && $action->isActive()) {
                    FightLog::write("Действие {$action->getCode()} модуля {$action->getModule($battle)->getName()} по роботу {$member->getOwner()} отменено");
                    $action->setActive(false);
                }
            }
        }
    }

    public function fillActions(Battle $battle) {
        $delayed_actions = $battle->getDelayedActions();
        $actions = [];
        foreach ($delayed_actions as $delayed_action) {
            $actions[$delayed_action->getSort()] []= $delayed_action;
        }
        foreach ($this->getModules() as $str_member => $modules) {
            $member = Member::fromString($str_member);
            foreach ($modules as $module) {
                $module_actions = $module->getActions($battle, $member, $this->getAnotherMember($battle, $member));
                foreach ($module_actions as $module_action) {
                    $actions[$module_action->getSort()][] = $module_action;
                }
            }
        }
        uksort($actions, function($a, $b) { if ($a === $b) {return 0;} return $a > $b ? 1 : -1; });
        $this->actions = $actions;
    }

    /* костыль */
    private function getAnotherMember(Battle $battle, Member $member): Member {
        foreach ($battle->getMembers() as $battle_member) {
            if ($battle_member->toString() !== $member->toString()) {
                return $battle_member;
            }
        }
        return $member;
    }

    public function processActions(Battle $battle) {
        $robots = $battle->getRobots();
        foreach ($robots as $robot) {
            FightLog::write("Робот {$robot->getMemberOwner()} начинает битву с {$robot->getEnergy()} энергии и {$robot->getHealth()} жизней");
        }
        $current_round_number = $this->getRoundNumber();
        $delayed_actions = [];
        foreach ($this->getActions() as $sort => $actions) {
            foreach ($actions as $action) {
                if ($action->isActive() && $action->isInRound($current_round_number)) {
                    if ($action->handle($battle, $this)) {
                        $action->markUsed(true);
                    }
                }
                if ($action->getRoundNumber() > $current_round_number) {
                    $delayed_actions []= $action;
                }
            }
        }
        $battle->setDelayedActions($delayed_actions);
    }

    public function finish(Battle $battle) {
        $this->status = self::STATUS_FINISHED;
        $this->subtractingHealth($battle);
        $this->restoreEnergy($battle);
        $this->dropDamagedEnergy($battle);
        $this->saveFightLog();
        $this->checkBattleFinished($battle);
    }

    private function subtractingHealth(Battle $battle) {
        $robots = $battle->getRobots();
        foreach ($robots as $robot) {
            if ($robot->subtractHealth()) {
                FightLog::write("Робот {$robot->getMemberOwner()} потерял здоровье");
            }
        }
    }

    private function restoreEnergy(Battle $battle) {
        $robots = $battle->getRobots();
        foreach ($robots as $robot) {
            $energy = $robot->restoreEnergy();
            FightLog::write("Робот {$robot->getMemberOwner()} восстановил энергию ({$energy})");
        }
    }

    private function saveFightLog() {
        $this->log = FightLog::read();
    }

    /** @return string[] */
    public function getLog(): array {
        return $this->log;
    }

    private function checkBattleFinished(Battle $battle) {
        $battle_finished = false;
        $winners = [];
        $robots = $battle->getRobots();
        foreach ($robots as $member_str => $robot) {
            if ($robot->getHealth() <= 0) {
                $battle_finished = true;
            } else {
                $winners []= Member::fromString($member_str);
            }
        }
        if ($battle_finished) {
            $battle->setWinners($winners);
            $battle->finish();
        }
    }

    private function dropDamagedEnergy(Battle $battle) {
        $robots = $battle->getRobots();
        foreach ($robots as $robot) {
            $robot->dropDamagedEnergy();
        }
    }
}
