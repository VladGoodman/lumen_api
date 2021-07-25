<?php

/** @var \Laravel\Lumen\Routing\Router $router */

$router->post('/user/register','UserController@register');
$router->post('/user/login','UserController@login');

$router->group(['middleware'=> 'auth'], function () use ($router){
    $router->post('/user/refresh-access-token','UserController@refreshAccessToken');

    $router->group(['prefix' => 'user'], function () use ($router) {
        $router->get('/','UserController@getItems');
        $router->get('/get-items','UserController@getItems');
        $router->get('/{id}','UserController@getItem');
        $router->get('/get-item/{id}','UserController@getItem');
    });

    $router->group(['prefix' => 'list'], function () use ($router) {
        $router->get('/','ListController@getItems');
        $router->get('/get-items','ListController@getItems');
        $router->get('/{id}','ListController@getItem');
        $router->get('/get-item/{id}','ListController@getItem');
        $router->post('/create','ListController@create');
        $router->post('/','ListController@create');
        $router->put('/update/{id}','ListController@update');
        $router->put('/{id}','ListController@update');
        $router->delete('/delete/{id}','ListController@delete');
        $router->delete('/{id}','ListController@delete');
    });

    $router->group(['prefix' => 'task'], function () use ($router) {
        $router->get('','TaskController@getItems');
        $router->get('/get-items/','TaskController@getItems');
        $router->get('/get-item/{id}','TaskController@getItem');
        $router->get('/{id}','TaskController@getItem');
        $router->post('/create','TaskController@create');
        $router->post('/','TaskController@create');
        $router->put('/update/{id}','TaskController@update');
        $router->put('/{id}','TaskController@update');
        $router->delete('/delete/{id}','TaskController@delete');
        $router->delete('/{id}','TaskController@delete');
    });
});
