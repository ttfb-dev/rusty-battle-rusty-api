<?php

namespace App\Battle;

use App\Battle\Modules\BaseModule;

class ArmingRound
{
    const STATUS_STARTED = 'started';
    const STATUS_FINISHED = 'finished';

    /** @var int */
    protected $round_number = 1;

    /** @var BaseModule[][] */ //member->index
    protected $proposed_modules = [];

    protected $status = self::STATUS_STARTED;

    public function getRoundNumber(): int {
        return $this->round_number;
    }

    public function setRoundNumber(int $round_number): self {
        $this->round_number = $round_number;
        return $this;
    }

    public function getStatus(): string {
        return $this->status;
    }

    public function setStatus(string $status): self {
        $this->status = $status;
        return $this;
    }

    /** @return BaseModule[][] */
    public function getProposedModules() {
        return $this->proposed_modules;
    }

    /** @return BaseModule[] */
    public function getProposedMemberModules(Member $member): array {
        return $this->getProposedModules()[$member->toString()] ?? [];
    }

    /* @param BaseModule[][] $proposed_modules */
    public function setProposedModules(array $proposed_modules): self {
        $this->proposed_modules = $proposed_modules;
        return $this;
    }

    public function setProposedMemberModules(Member $member, array $proposed_modules): self {
        $this->proposed_modules[$member->toString()] = $proposed_modules;
        return $this;
    }

    public function finish() {
        $this->status = self::STATUS_FINISHED;
    }
}
