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

use Illuminate\Support\Facades\DB;

$router->get('', function () {
        DB::connection()->getPdo();
        if(DB::connection()->getDatabaseName()){
            echo "Yes! Successfully connected to the DB: " . DB::connection()->getDatabaseName();
        }else{
            die("Could not find the database. Please check your configuration.");
        }
});
// READY
$router->post('/user/register','UserController@register');
// READY
$router->post('/user/login','UserController@login');

$router->group(['middleware'=> 'auth'], function () use ($router){
// READY
    $router->post('/user/refresh-access-token','UserController@refreshAccessToken');

    $router->group(['prefix' => 'user'], function () use ($router) {
// getItems READY
        $router->get('/','UserController@getItems');
        $router->get('/get-items','UserController@getItems');
// getItem READY
        $router->get('/{id}','UserController@getItem');
        $router->get('/get-item/{id}','UserController@getItem');
    });

    $router->group(['prefix' => 'list'], function () use ($router) {
// getItems READY
        $router->get('/','ListController@getItems');
        $router->get('/get-items','ListController@getItems');
// getItem READY
        $router->get('/{id}','ListController@getItem');
        $router->get('/get-item/{id}','ListController@getItem');
// create READY
        $router->post('/create','ListController@create');
        $router->post('/','ListController@create');
// update READY
        $router->put('/update/{id}','ListController@update');
        $router->put('/{id}','ListController@update');
// delete READY
        $router->delete('/delete/{id}','ListController@delete');
        $router->delete('/{id}','ListController@delete');
    });

    $router->group(['prefix' => 'task'], function () use ($router) {
// getItems READY
        $router->get('','TaskController@getItems');
        $router->get('/get-items/','TaskController@getItems');
// getItem READY
        $router->get('/get-item/{id}','TaskController@getItem');
        $router->get('/{id}','TaskController@getItem');
// create READY
        $router->post('/create','TaskController@create');
        $router->post('/','TaskController@create');
// update READY
        $router->put('/update/{id}','TaskController@update');
        $router->put('/{id}','TaskController@update');
// delete READY
        $router->delete('/delete/{id}','TaskController@delete');
        $router->delete('/{id}','TaskController@delete');
    });
});
