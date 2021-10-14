<?php


namespace App\Services;


use App\Battle\Member;
use App\Models\Battle;

class HistoryService
{
    public function getMemberHistory(Member $member): array {
        $battles = Battle::query()
            ->whereJsonContains('members', $member->toString())
            ->orderBy('updated_at', 'DESC')
            ->get();

        $result = [];

        foreach ($battles as $battle) {
            $result[] = [
                'id' => $battle->id,
                'updated_at' => $battle->updated_at,
                'points' => $battle->points,
                'points_version' => $battle->points_version,
                'status' => $battle->status,
            ];
        }
        return $result;
    }
}
