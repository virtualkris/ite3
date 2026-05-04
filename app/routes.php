<?php
// This file is responsible for defining the routes of the application.
$router->get('home', 'PostController@index');
$router->get('posts/create', 'PostController@create');
$router->post('posts', 'PostController@store');
$router->get('posts/edit/{id}', 'PostController@edit');
$router->post('posts/update', 'PostController@update');
$router->get('posts/delete/{id}', 'PostController@delete');
$router->post('posts/delete', 'PostController@delete');
$router->get('login', 'AuthController@login');
$router->post('login', 'AuthController@login');
$router->get('logout', 'AuthController@logout');