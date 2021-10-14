<?php


namespace App\Services;

use App\Models\Battle;
use Illuminate\Support\Facades\DB;
use App\Battle\Member;

class LeaderboardService
{
    public function getLeaderBoard(string $owner, int $limit = 25): array {
        $points_version = ConfigService::getPoints('version', 1);
        $members_max_score = $this->getMemberMaxScore($owner, $points_version, $limit);

        $result = [];

        foreach ($members_max_score as $row) {
            $battle = Battle::query()
                ->whereJsonContains('members', $row['member'])
                ->where('points', $row['points'])
                ->where('points_version', $points_version)
                ->first();
            
            if ($battle instanceof Battle) {
                $result []= [
                    'battle_id' => $battle->id,
                    'updated_at' => $battle->updated_at,
                    'member' => Member::fromString($row['member'])->toArray(),
                    'points' => $row['points']
                ];
            }
        }

        return $result;
    }

    private function getMemberMaxScore(string $owner, int $points_version, int $limit = 25): array {
        $rows = DB::select("
            select member,
                   max(points) as max_points
            from (
                     select split_part(json_array_elements(members)::text, '\"', 2) as member,
                            points
                     from battles
                     where points_version = $points_version
                     order by points DESC
                 ) as inner_top
            where member like '$owner|%'
            and points > 0
            group by member
            order by max_points DESC

            limit $limit
        ") ?? [];

        foreach ($rows as &$row) {
            $row = (array)$row;
            $row['member'] = Member::fromString($row['member'])->toArray();
        }

        return $rows;
    }
}
