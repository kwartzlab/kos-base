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


// Dashboard
Route::middleware('auth')->group(function() {
    Route::get('/', 'DashboardController@index');
    Route::get('/dashboard', 'DashboardController@index');
    Route::get('/home', 'DashboardController@index');
});

// Gatekeeper Management
Route::middleware('auth')->group(function() {
    Route::get('/gatekeepers/tools', 'GatekeepersController@tool_list');
    Route::resource('gatekeepers', 'GatekeepersController');
    Route::post('/gatekeepers/{id}/add_trainer/{user_id}', 'GatekeepersController@add_trainer');
    Route::post('/gatekeepers/{id}/add_maintainer/{user_id}', 'GatekeepersController@add_maintainer');
    Route::post('/gatekeepers/{id}/remove_trainer/{user_id}', 'GatekeepersController@remove_trainer');
    Route::post('/gatekeepers/{id}/remove_maintainer/{user_id}', 'GatekeepersController@remove_maintainer');
    Route::post('/gatekeepers/assignments/{request_action}/{request_id?}', 'GatekeepersController@assignments');
    Route::post('/gatekeepers/{id}/add_trainer', 'GatekeepersController@add_trainer');
    Route::get('/gatekeepers/{id}/dashboard', 'GatekeepersController@dashboard');
    Route::post('/gatekeepers/revoke/{auth_id}', 'GatekeepersController@revoke_auth');
    Route::post('/gatekeepers/authorize', 'GatekeepersController@grant_auth');
});

// Users & Membership Register
Route::middleware('auth')->group(function() {
    Route::resource('users', 'UsersController')->middleware(['auth','can:manage-users']);
    // override default resource routes so all users can access
    Route::get('/users/create', 'UsersController@create');
    Route::post('/users', 'UsersController@store');
    Route::get('/users/index/{filter?}', 'UsersController@index')->middleware(['auth','can:manage-users']);
    Route::get('/users/{user}/destroy_key/{key}', 'UsersController@destroy_key')->middleware(['auth','can:manage-keys']);
    Route::post('/users/{user}/store_key', 'UsersController@store_key')->middleware(['auth','can:manage-keys']);
    Route::post('/users/{id}/status', 'UsersController@update_status')->middleware(['auth','can:manage-users']);
    Route::delete('/users/{id}/status', 'UsersController@update_status')->middleware(['auth','can:manage-users']);
    Route::post('/users/check_attributes', 'UsersController@check_attributes')->middleware(['auth']);
    Route::post('/users/{id}/do_stuff', 'UsersController@do_stuff')->middleware(['auth']);
    Route::get('/users/{id}/toggle_flag/{flag}', 'UsersController@toggle_flag')->middleware(['auth','can:manage-users']);

});

// Member Directory
Route::middleware('auth')->group(function() {
    Route::resource('members', 'MembersController');
    Route::get('members/index/{filter?}', 'MembersController@index');
    Route::get('members/{user}/profile', 'MembersController@edit');
    Route::get('members/skill/{skill_id}', 'MembersController@skill');
});

// Training
Route::middleware('auth')->group(function() {
    Route::resource('training', 'TrainingController');
});

// Web Forms
Route::middleware('auth')->group(function() {
    Route::resource('forms', 'FormsController');
    Route::post('/forms/{id}/save', 'FormsController@save');
    Route::get('/forms/submission/{id}', 'FormsController@submission');
});

// Reports
Route::get('/reports', 'ReportsController@index')->middleware('auth');

// Gatekeeper sync and key authentication routes
Route::post('/keys', 'KeysController@index');
Route::get('/keys', 'KeysController@index');

// User Roles
Route::middleware(['auth','can:manage-roles'])->group(function() {
    Route::resource('roles', 'RoleController');
    Route::get('/roles/{id}/remove_user/{key}', 'RoleController@remove_user');
    Route::post('/roles/{id}/add_user', 'RoleController@add_user');
});

// Kiosk routes
Route::get('/kiosk', 'KioskController@index');
Route::post('/kiosk/authenticate', 'KioskController@authenticate');
Route::get('/kiosk/logout', 'KioskController@logout');
Route::get('/kiosk/unlock', 'KioskController@unlock')->middleware(['auth','can:manage-keys']);
Route::get('/kiosk/create_key', 'KioskController@create_key')->middleware(['auth','can:manage-keys']);
Route::post('/kiosk/create_key', 'KioskController@create_key')->middleware(['auth','can:manage-keys']);
Route::post('/kiosk/store_key', 'KioskController@store_key')->middleware(['auth','can:manage-keys']);

// Authentication routes
Auth::routes();
Auth::routes(['register' => false]);


// Image manipulation routes
Route::middleware(['auth'])->group(function() {
    Route::get('/image/lastupload', 'ImageController@getLastImage');
    Route::get('/image-crop/{photo_type?}/{id?}', 'ImageController@imageCrop');
   Route::post('/image-crop/{photo_type?}/{id?}', 'ImageController@imageCropPost');
});

// Teams routes
Route::middleware(['auth'])->group(function() {
    Route::get('/teams/manage', 'TeamsController@manage');
    Route::get('/teams/training', 'TeamsController@training');
    Route::get('/teams/training_request/{gatekeeper}', 'TeamsController@training_request');
    Route::get('/teams/training_cancel/{request_id}', 'TeamsController@training_cancel');
    Route::get('/teams/training_pass/{request_id}', 'TeamsController@training_pass');
    Route::get('/teams/training_fail/{request_id}', 'TeamsController@training_fail');
    Route::get('/teams/{team_id}/requests/{request_type}', 'TeamsController@requests');
    Route::get('/teams/{team_id}/dashboard', 'TeamsController@dashboard');
    Route::resource('teams', 'TeamsController');
});

 // Helpdesk routes
Route::middleware(['auth'])->group(function() {
    Route::resource('helpdesk', 'HelpdeskController');
 });