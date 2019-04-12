<?php


Route::get('/', function () {
    return view('welcome');
});

Route::get('/users', 'UsersController@getUsers');

Route::delete('/users/{user}/delete', 'UsersController@deleteUser');


