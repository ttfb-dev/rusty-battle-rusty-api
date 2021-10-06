<?php

use App\Services\ConfigService;

class BattleApiTest extends TestCase
{
    private $battle_id = 3;
    private $arming_rounds = 5;
    private $user_id = 1850436;

    public function test_a_battle()
    {
        $response = $this->call('POST', '/v1/battle?disable_analytics=1&source=vk', ['user_ids' => [$this->user_id]]);
        $data = json_decode($response->content(), true);
        $battle_id = $data['battle_id'];
        $this->assertIsInt($battle_id);
    }

    public function test_b_arming()
    {
        for($i = 1; $i <= 5; $i++) {
            $response_get = $this->call('GET', "/v1/battle/{$this->battle_id}/arming-round?disable_analytics=1&source=vk&user_id={$this->user_id}");
            $data = json_decode($response_get->content(), true);
            $module_name = '';
            $slot = '';
            foreach ($data['modules'] as $module) {
                if ($module['id'] !== 'garbage') {
                    $module_name = $module['id'];
                    $index = rand(0, (count($module['slots']) - 1));
                    $slot = $module['slots'][$index] ?? '';
                    break;
                }
            }
            $response_post = $this->call('POST', "/v1/battle/{$this->battle_id}/arming-round?disable_analytics=1&source=vk&user_id={$this->user_id}", ['module' => $module_name, 'slot' => $slot]);
            $resp = json_decode($response_post->content(), true);
            $this->assertEquals('ok', $resp['status']);
            $this->assertEquals(200, $response_post->status());
        }
    }

    public function test_c_robot()
    {
        $response = $this->call('GET', "/v1/battle/{$this->battle_id}/robot?disable_analytics=1&source=vk&user_id={$this->user_id}");
        $robot = json_decode($response->content(), true);

        $this->assertNotEmpty($robot);

        $this->assertNotEmpty($robot['modules']);

        $expected_health = $robot['health_base'];
        foreach ($robot['modules'] as $module) {
            $expected_health += $module['health_bonus'];
        }
        $this->assertEquals($expected_health, $robot['health_max']);

        $expected_energy = $robot['energy_base'];
        foreach ($robot['modules'] as $module) {
            $expected_energy += $module['energy_bonus'];
        }
        $this->assertEquals($expected_energy, $robot['energy_max']);
    }

    public function test_d_finish_arming()
    {
        $response = $this->call('POST', "/v1/battle/{$this->battle_id}/finish-arming?disable_analytics=1&source=vk&user_id={$this->user_id}");
        $data = json_decode($response->content(), true);
        $this->assertEquals('ok', $data['status']);
        $this->assertEquals(200, $response->status());
    }

    public function test_e_finish_arming_failed()
    {
        $response = $this->call('POST', "/v1/battle/{$this->battle_id}/finish-arming?disable_analytics=1&source=vk&user_id={$this->user_id}");
        $this->assertEquals(500, $response->status());
    }

    public function test_e_robot()
    {
        $response = $this->call('GET', "/v1/battle/{$this->battle_id}/core-robot?disable_analytics=1&source=vk&user_id={$this->user_id}");
        $robot = json_decode($response->content(), true);

        $this->assertNotEmpty($robot);

        $this->assertNotEmpty($robot['modules']);

        $expected_modules = ConfigService::getGeneral('core_slots_with_arming_rounds', $this->arming_rounds)[$this->arming_rounds];

        $this->assertEquals(true, count($robot['modules']) <= $expected_modules);

        $expected_health = $robot['health_base'];
        foreach ($robot['modules'] as $module) {
            $expected_health += $module['health_bonus'];
        }
        $this->assertEquals($expected_health, $robot['health_max']);

        $expected_energy = $robot['energy_base'];
        foreach ($robot['modules'] as $module) {
            $expected_energy += $module['energy_bonus'];
        }
        $this->assertEquals($expected_energy, $robot['energy_max']);
    }

    public function test_f_fight()
    {
        $battle_finished = false;
        $rounds = 0;

        while(!$battle_finished) {
            $response = $this->call('POST', "/v1/battle/{$this->battle_id}/fight-round?disable_analytics=1&source=vk&user_id={$this->user_id}", ["module_ids"]);
            $data = json_decode($response->content(), true);
            if ($data['battle']['status'] === 'finished') {
                $battle_finished = true;
                $this->assertEquals(true, $rounds <= 30);
            }
            if ($rounds > 30) {
                $battle_finished = true;
                $this->fail('30 раундов не хватило чтоб доиграть');
            }
            $rounds ++;
        }
    }
}
