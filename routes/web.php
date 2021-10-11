<?php

/** @var \Laravel\Lumen\Routing\Router $router */

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/

$router->get('/', function () use ($router) {
    return $router->app->version();
});


$router->post('/v1/battle', [
    'middleware' => ['source'],
    'uses' => 'BattleController@create'
]);

$router->get('/v1/battle/{battle_id}/arming-round', [
    'middleware' => ['source', 'user_id', 'battle_status:arming', 'user_in_battle'],
    'uses' => 'BattleController@getArmingRound'
]);

$router->post('/v1/battle/{battle_id}/arming-round', [
    'middleware' => ['source', 'user_id', 'battle_status:arming', 'user_in_battle'],
    'uses' => 'BattleController@chooseModuleInArmingRound'
]);

$router->get('/v1/battle/{battle_id}/robot', [
    'middleware' => ['source', 'user_id', 'user_in_battle'],
    'uses' => 'BattleController@getRobot'
]);

$router->get('/v1/battle/{battle_id}/core-robot', [
    'middleware' => ['source', 'user_id', 'user_in_battle'],
    'uses' => 'BattleController@getCoreRobot'
]);

$router->post('/v1/battle/{battle_id}/finish-arming', [
    'middleware' => ['source', 'user_id', 'battle_status:arming', 'user_in_battle'],
    'uses' => 'BattleController@finishArming'
]);

$router->post('/v1/battle/{battle_id}/fight-round', [
    'middleware' => ['source', 'user_id', 'battle_status:fight', 'user_in_battle'],
    'uses' => 'BattleController@fightRound'
]);

$router->get('/v1/battle/where-i-am', [
    'middleware' => ['source', 'user_id'],
    'uses' => 'BattleController@whereIAm'
]);

$router->post('/v1/battle/{battle_id}/force-finish', [
    'middleware' => ['source', 'user_id', 'battle_status:arming,fight'],
    'uses' => 'BattleController@forceFinish'
]);

$router->get('/robots/{image_name}.png', [
    'middleware' => ['image_md5'],
    'uses' => 'ImageController@getImage'
]);

$router->get('/v1/dev', [
    'uses' => 'BattleController@dev'
]);
