<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', 'DashboardController@index');
Route::get('/home', 'DashboardController@index');


Route::resource('gatekeepers', 'GatekeepersController');
Route::get('/gatekeepers/{id}/remove_trainer/{key}', 'GatekeepersController@remove_trainer');
Route::post('/gatekeepers/{id}/add_trainer', 'GatekeepersController@add_trainer');

Route::resource('users', 'UsersController');
Route::get('/users/index/{filter?}', 'UsersController@index');
Route::get('/users/{user}/destroy_key/{key}', 'UsersController@destroy_key');
Route::post('/users/{user}/store_key', 'UsersController@store_key');

Route::get('members', 'MembersController@index');
Route::get('members/index/{filter?}', 'MembersController@index');
Route::get('members/{user}/profile', 'MembersController@profile');

Route::resource('training', 'TrainingController');
Route::get('/training/{user}/destroy/{key}', 'TrainingController@destroy');

Route::resource('forms', 'FormsController');

Route::get('/reports', 'ReportsController@index');

Route::post('/keys', 'KeysController@index');
Route::get('/keys', 'KeysController@index');
//Route::post('/keys/authenticate', 'KeysController@authenticate');
//Route::get('/keys/getkeys', 'KeysController@getkeys');
//Route::post('/keys/getkeys', 'KeysController@getkeys');
//Route::post('/keys/sendauths', 'KeysController@sendauths');

// Kiosk routes

Route::get('/kiosk', 'KioskController@index');
Route::post('/kiosk/authenticate', 'KioskController@authenticate');
Route::get('/kiosk/logout', 'KioskController@logout');
Route::get('/kiosk/unlock', 'KioskController@unlock');
Route::get('/kiosk/create_key', 'KioskController@create_key');
Route::post('/kiosk/create_key', 'KioskController@create_key');
Route::post('/kiosk/store_key', 'KioskController@store_key');


Auth::routes();


