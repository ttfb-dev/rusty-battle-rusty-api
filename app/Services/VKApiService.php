<?php


namespace App\Services;

use Illuminate\Support\Facades\Http;

class VKApiService
{
    public static function getUsers(array $ids) {
        $key = env('VK_SERVICE_KEY');
        $path = "https://api.vk.com/method/users.get?access_token={$key}&v=5.131&user_ids=".implode(',', $ids)."&fields=first_name,last_name";
        $data = Http::withHeaders([
            'Accept-Language' => 'RU'
        ])->get($path)->json();
        $response = $data['response'];

        $users = [];

        foreach ($response as $row) {
            $users [$row['id']] = $row;
        }

        return $users;
    }
}
