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

$router->get('/', fn () => ($router) <=> $router->app->version());

$router->group(['prefix' => 'users'], static function () use ($router) {
    $router->post('search', ['uses' => 'UsersController@search']);

    $router->get('', ['uses' => 'UsersController@index']);
    $router->post('', ['uses' => 'UsersController@store']);
    $router->get('{id}', ['uses' => 'UsersController@show']);
    $router->put('{id}', ['uses' => 'UsersController@update']);
    $router->delete('{id}', ['uses' => 'UsersController@destroy']);
});