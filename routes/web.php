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

$router->post('login', 'UsersController@authenticate');
$router->post('register', 'UsersController@register');

$router->group(['prefix' => 'trip'], function () use ($router) {
    $router->get('/', 'TripsController@index');
    $router->post('/store', 'TripsController@store');
    $router->get('/show/{id}', 'TripsController@show');
    $router->get('/show-by-slug/{slug}', 'TripsController@showBySlug');
    $router->put('/update/{id}', 'TripsController@update');
    $router->delete('/destroy/{id}', 'TripsController@destroy');
});

$router->group(['prefix' => 'booking'], function () use ($router) {
    $router->get('/', 'BookingController@index');
    $router->post('/make-reservation/{slug}', 'BookingController@makeReservation');
    $router->delete('/cancel-reservation/{slug}', 'BookingController@cancelReservation');
});
