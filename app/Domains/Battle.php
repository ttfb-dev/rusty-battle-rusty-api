<?php


namespace App\Domains;

use App\Models\ArmingRound;
use \App\Models\Battle as BattleModel;
use App\Models\FightRound;

class Battle
{
    /** @var BattleModel */
    public $model;

    /** @var ArmingRound */
    private $armingRound;

    /** @var FightRound */
    private $fightRound;

    /** @var BaseRobot[] */
    private $robots;

    public function __construct(BattleModel $battle) {
        $this->model = $battle;
    }

    public function setArmingRound(ArmingRound $armingRound): self {
        $this->armingRound = $armingRound;
        return $this;
    }

    public function setFightRound(FightRound $fightRound): self {
        $this->fightRound = $fightRound;
        return $this;
    }

    public function getFightRoundNumber(): int {
        return $this->fightRound->round_number;
    }

    public function getArmorRoundNumber(): int {
        return $this->fightRound->round_number;
    }

    /** @return Member[] */
    public function getMembers(): array {
        return $this->model->getMembers();
    }

    /** @return BaseRobot[] */
    public function getRobots(): array {
        $members = $this->model->getMembers();
        $robots = [];
        foreach ($members as $member) {
            $robots []= $this->getMemberRobot($member);
        }
        return $robots;
    }

    /** @return ?Member */
    public function getCoreMember(): ?Member {
        $members = $this->model->getMembers();
        foreach ($members as $member) {
            if ($member->getOwner() === Member::MEMBER_CORE) {
                return $member;
            }
        }
        return null;
    }

    public function getId(): int {
        return $this->model->id;
    }

    /** @param BaseRobot */
    public function withRobots(array $robots): self {
        $this->robots = $robots;
        return $this;
    }

    public function finishArming() {
        throw_if($this->model->status !== BattleModel::STATUS_ARMING, new \Exception('Нельзя закончить раунд вооружения. Статус битвы другой.'));
        $this->model->status = BattleModel::STATUS_BATTLE;
        $this->model->save();
    }

    public function isArming(): bool {
        return $this->model->status === BattleModel::STATUS_ARMING;
    }

    public function isBattle(): bool {
        return $this->model->status === BattleModel::STATUS_BATTLE;
    }

    public function isFinished(): bool {
        return $this->model->status === BattleModel::STATUS_FINISHED;
    }

    public function getStatus(): string {
        return $this->model->status;
    }

    /** @return Member[] */
    public function getWinners(): array {
        return $this->model->getWinners();
    }

    public function getMemberRobot(Member $member): ?BaseRobot {
        foreach ($this->robots as $robot) {
            if ($robot->getOwner()->toString() === $member->toString()) {
                return $robot;
            }
        }
        return null;
    }

    /** @param Member[] $winners */
    public function finish(array $winners) {
        $this->model->status = BattleModel::STATUS_FINISHED;
        $this->model->setWinners($winners);
        $this->model->save();
    }
}
