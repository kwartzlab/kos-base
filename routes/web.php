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
    Route::get('/home', 'DashboardController@index');
});

// Gatekeeper Management
Route::middleware(['auth','can:manage-gatekeepers'])->group(function() {
    Route::resource('gatekeepers', 'GatekeepersController');
    Route::get('/gatekeepers/{id}/remove_trainer/{key}', 'GatekeepersController@remove_trainer');
    Route::post('/gatekeepers/{id}/add_trainer', 'GatekeepersController@add_trainer');
});

// Membership Register
Route::middleware(['auth','can:manage-users-keys'])->group(function() {
    Route::resource('users', 'UsersController');
    Route::get('/users/index/{filter?}', 'UsersController@index');
    Route::get('/users/{user}/destroy_key/{key}', 'UsersController@destroy_key');
    Route::post('/users/{user}/store_key', 'UsersController@store_key');
});

// Member Directory
Route::middleware('auth')->group(function() {
    Route::get('members', 'MembersController@index');
    Route::get('members/index/{filter?}', 'MembersController@index');
    Route::get('members/{user}/profile', 'MembersController@profile');
});

// Training
Route::middleware('auth')->group(function() {
    Route::resource('training', 'TrainingController');
    Route::get('/training/{user}/destroy/{key}', 'TrainingController@destroy');
});

// Web Forms
Route::resource('forms', 'FormsController')->middleware(['auth','can:manage-forms']);

// Reports
Route::get('/reports', 'ReportsController@index')->middleware('auth');

// Gatekeeper sync and key authentication routes
Route::post('/keys', 'KeysController@index');
Route::get('/keys', 'KeysController@index');

// User Roles
Route::resource('roles', 'RoleController')->middleware(['auth','can:manage-roles']);

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