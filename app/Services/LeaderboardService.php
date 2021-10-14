<?php


namespace App\Services;


use Illuminate\Support\Facades\DB;
use App\Battle\Member;

class LeaderboardService
{
    public function getLeaderBoard(string $owner, int $limit = 25): array {
        $rows = DB::select("
            select
                   battle_id,
                   adaptive.member,
                   adaptive.points,
                   updated_at
            from (
                select
                       id as battle_id,
                       split_part(json_array_elements(members)::text, '\"', 2) as member,
                       points,
                       updated_at
                from battles
            ) adaptive
            inner join (
                select member,
                       max(points) as max_points
                from (
                         select split_part(json_array_elements(members)::text, '\"', 2) as member,
                                points
                         from battles
                         where points_version = 1
                         order by points DESC
                     ) inner_top
                where member like '$owner|%'
                group by member
                order by max_points DESC
            ) as top
            on top.member = adaptive.member and top.max_points = adaptive.points
            order by points desc
            limit $limit
        ") ?? [];

        foreach ($rows as &$row) {
          $row['member'] = Member::fromString($row['member'])->toArray();
        }

        return $rows;
    }
}
