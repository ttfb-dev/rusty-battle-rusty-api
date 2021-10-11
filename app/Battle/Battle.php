<?php


namespace App\Battle;


use App\Battle\Actions\BaseAction;
use App\Battle\Factories\ArmingRoundFactory;
use App\Battle\Factories\FightRoundFactory;
use App\Battle\Modules\BaseModule;
use App\Battle\Robots\BaseRobot;
use App\Services\ConfigService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Zumba\JsonSerializer\JsonSerializer;
use \App\Models\Battle as BattleModel;

class Battle
{
    const STATUS_ARMING         = 'arming';
    const STATUS_FIGHT          = 'fight';
    const STATUS_FINISHED       = 'finished';
    const STATUS_FORCE_FINISHED = 'force_finished';

    private $version = 1;

    /** @var int  */
    private $id;

    private $status = self::STATUS_ARMING;

    /** @var ArmingRound[] */
    private $arming_rounds = [];

    /** @var FightRound[] */
    private $fight_rounds = [];

    /** @var BaseRobot[] */
    private $robots = [];

    /** @var Member[] */
    private $members = [];

    /** @var Member[] */
    private $winners = [];

    /** @var BaseAction[] */
    private $delayed_actions = [];

    /** @var int */
    private $points = 0;

    public function __construct(int $battle_id)
    {
        $this->id = $battle_id;
    }

    public function getId(): int {
        return $this->id;
    }

    public function getStatus(): string {
        return $this->status;
    }

    public function setStatus(string $status): self {
        $this->status = $status;
        return $this;
    }

    /** @return ArmingRound[] */
    public function getArmingRounds(): array {
        return $this->arming_rounds;
    }

    public function getCurrentArmingRoundOrCreate(): ArmingRound {
        $arming_rounds = $this->getArmingRounds();
        $last_arming_round = end($arming_rounds);
        if ($last_arming_round instanceof ArmingRound && $last_arming_round->getStatus() === ArmingRound::STATUS_STARTED) {
            return $last_arming_round;
        }
        $new_arming_round = ArmingRoundFactory::newArmingRound($this);
        $this->arming_rounds []= $new_arming_round;
        return $new_arming_round;
    }

    public function deleteStartedArmingRounds() {
        foreach ($this->arming_rounds as $index => $arming_round) {
            if ($arming_round->getStatus() === ArmingRound::STATUS_STARTED) {
                unset($this->arming_rounds[$index]);
                $this->arming_rounds = array_values($this->arming_rounds);
            }
        }
    }

    public function addPoints(int $points): self {
        $this->points += $points;
        Log::debug("add points: $points");
        return $this;
    }

    public function setPoints(int $points): self {
        $this->points = $points;
        return $this;
    }

    public function getPoints(): int {
        return $this->points;
    }

    /** @return FightRound[] */
    public function getFightRounds(): array {
        return $this->fight_rounds;
    }

    public function getNewFightRound(): FightRound {
        $fight_round = FightRoundFactory::newArmingRound($this);
        $this->fight_rounds []= $fight_round;
        return $fight_round;
    }

    public function getFightRoundNumber(): int {
        $fight_round = end($this->fight_rounds);
        return $fight_round instanceof FightRound ? $fight_round->getRoundNumber() : 0;
    }

    /** @return BaseAction[] */
    public function getDelayedActions(): array {
        return $this->delayed_actions;
    }

    /** @param BaseAction[] $delayed_actions */
    public function setDelayedActions(array $delayed_actions): self {
        $this->delayed_actions = $delayed_actions;
        return $this;
    }

    /** @param Member[] $members */
    public function setMembers(array $members): self {
        $this->members = $members;
        return $this;
    }

    /** @return Member[] $members */
    public function getMembers(): array {
        return $this->members;
    }

    public function getCoreMember(): ?Member {
        foreach ($this->members as $member) {
            if ($member->getOwner() === Member::MEMBER_CORE) {
                return $member;
            }
        }
        return null;
    }

    public function setMemberRobot(Member $member, BaseRobot $robot): self {
        $this->robots[$member->toString()] = $robot;
        return $this;
    }

    public function getMemberRobot(Member $member): ?BaseRobot {
        return $this->robots[$member->toString()] ?? null;
    }

    /** @return BaseRobot[]  */
    public function getRobots(): array {
        return $this->robots;
    }

    /** @return BaseModule[] */
    public function getRobotsModules(): array {
        return array_merge(
            ...array_values(
                array_map(
                    function(BaseRobot $robot) { return array_values($robot->getModules()); },
                    $this->robots
                )
            )
        );
    }

    /** @return Member[] */
    public function getWinners(): array {
        return $this->winners;
    }

    /** @return Member[] */
    public function setWinners(array $winners): self {
        $this->winners = $winners;
        return $this;
    }

    public function finishArming() {
        $this->status = self::STATUS_FIGHT;
    }

    public function finish(bool $force = false) {
        $this->status = $force ? self::STATUS_FORCE_FINISHED : self::STATUS_FINISHED;
    }


    public function save() {
        $serialized = self::getSerializer()->serialize($this);
        DB::update('update battle_line set line = ? where id = ?', [$serialized, $this->id]);
        $this->refreshModel();
    }

    public static function load(int $id): self {
        $result = DB::selectOne('select line from battle_line where id = ?', [$id]);
        throw_if(!isset($result->line), new \Exception('Битва с таким ID не найдена'));
        return self::getSerializer()->unserialize($result->line);
    }

    private static function getSerializer(): JsonSerializer {
        return new JsonSerializer();
    }

    public static function whereIAm(Member $member): ?self {
        $battleModel = BattleModel::query()
            ->whereIn('status', [self::STATUS_FIGHT, self::STATUS_ARMING])
            ->whereJsonContains('members', $member->toString())
            ->first();

        if (!($battleModel instanceof BattleModel)) {
            return null;
        }
        return self::load($battleModel->id);
    }

    private function refreshModel() {
        $battleModel = BattleModel::find($this->getId()) ?? new BattleModel(['id' => $this->getId()]);
        $battleModel->status = $this->getStatus();
        $battleModel->members = array_map(function (Member $member) { return $member->toString(); }, $this->getMembers());
        $battleModel->winners = array_map(function (Member $winner) { return $winner->toString(); }, $this->getWinners());
        $battleModel->points = $this->getPoints();
        $battleModel->points_version = ConfigService::getPoints('version', 1);
        $battleModel->save();
    }
}
