<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\FormsController;
use App\Http\Controllers\GatekeepersController;
use App\Http\Controllers\HelpdeskController;
use App\Http\Controllers\ImageController;
use App\Http\Controllers\KeysController;
use App\Http\Controllers\KioskController;
use App\Http\Controllers\MembersController;
use App\Http\Controllers\ReportsController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\SlackInviteController;
use App\Http\Controllers\TeamsController;
use App\Http\Controllers\TrainingController;
use App\Http\Controllers\UsersController;
use App\Models\User;
use Illuminate\Support\Facades\Route;

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
Route::middleware('auth')->group(function () {
    Route::get('/', [DashboardController::class, 'index']);
    Route::get('/dashboard', [DashboardController::class, 'index']);
    Route::get('/home', [DashboardController::class, 'index']);
});

// Gatekeeper Management
Route::middleware('auth')->group(function () {
    Route::get('/gatekeepers/tools', [GatekeepersController::class, 'tool_list']);
    Route::resource('gatekeepers', GatekeepersController::class);
    Route::post('/gatekeepers/{id}/add_trainer/{user_id}', [GatekeepersController::class, 'add_trainer']);
    Route::post('/gatekeepers/{id}/add_maintainer/{user_id}', [GatekeepersController::class, 'add_maintainer']);
    Route::post('/gatekeepers/{id}/remove_trainer/{user_id}', [GatekeepersController::class, 'remove_trainer']);
    Route::post('/gatekeepers/{id}/remove_maintainer/{user_id}', [GatekeepersController::class, 'remove_maintainer']);
    Route::post('/gatekeepers/assignments/{request_action}/{request_id?}', [GatekeepersController::class, 'assignments']);
    Route::post('/gatekeepers/{id}/add_trainer', [GatekeepersController::class, 'add_trainer']);
    Route::get('/gatekeepers/{id}/dashboard', [GatekeepersController::class, 'dashboard']);
    Route::post('/gatekeepers/revoke/{auth_id}', [GatekeepersController::class, 'revoke_auth']);
    Route::get('/gatekeepers/{id}/revoke_all_auth', [GatekeepersController::class, 'revoke_all_auth'])->middleware(['auth', 'can:manage-gatekeepers']);
    Route::post('/gatekeepers/authorize', [GatekeepersController::class, 'grant_auth']);
});

// Users & Membership Register
Route::middleware('auth')->group(function () {
    Route::resource('users', UsersController::class)->middleware(['auth', 'can:manage-users']);
    // override default resource routes so all users can access
    Route::get('/users/create', [UsersController::class, 'create']);
    Route::post('/users', [UsersController::class, 'store']);
    Route::get('/users/index/{filter?}', [UsersController::class, 'index'])->middleware(['auth', 'can:manage-users']);
    Route::get('/users/{user}/destroy_key/{key}', [UsersController::class, 'destroy_key'])->middleware(['auth', 'can:manage-keys']);
    Route::post('/users/{user}/store_key', [UsersController::class, 'store_key'])->middleware(['auth', 'can:manage-keys']);
    Route::post('/users/{id}/status', [UsersController::class, 'update_status'])->middleware(['auth', 'can:manage-users']);
    Route::delete('/users/{id}/status', [UsersController::class, 'update_status'])->middleware(['auth', 'can:manage-users']);
    Route::post('/users/check_attributes', [UsersController::class, 'check_attributes'])->middleware(['auth']);
    Route::post('/users/{id}/do_stuff', [UsersController::class, 'do_stuff'])->middleware(['auth']);
    Route::get('/users/{id}/toggle_flag/{flag}', [UsersController::class, 'toggle_flag'])->middleware(['auth', 'can:manage-users']);
});

// Member Directory
Route::middleware('auth')->group(function () {
    Route::resource('members', MembersController::class);
    Route::get('members/index/{filter?}', [MembersController::class, 'index']);
    Route::get('members/{user}/profile', [MembersController::class, 'edit']);
    Route::get('members/skill/{skill_id}', [MembersController::class, 'skill']);
});

// Training
Route::middleware('auth')->group(function () {
    Route::resource('training', TrainingController::class);
});

// Web Forms
Route::middleware('auth')->group(function () {
    Route::resource('forms', FormsController::class);
    Route::post('/forms/{id}/save', [FormsController::class, 'save']);
    Route::get('/forms/submission/{id}', [FormsController::class, 'submission']);
});

// Reports
Route::middleware(['auth', 'can:manage-reports'])->group(function () {
    Route::get('/reports', [ReportsController::class, 'index'])->name('reports');
    Route::get('/reports/member-status-report', [ReportsController::class, 'member_status_report'])->name('reports.member-status-report');
});

// Gatekeeper sync and key authentication routes
Route::post('/keys', [KeysController::class, 'index']);
Route::get('/keys', [KeysController::class, 'index']);

// User Roles
Route::middleware(['auth', 'can:manage-roles'])->group(function () {
    Route::resource('roles', RoleController::class);
    Route::get('/roles/{id}/remove_user/{key}', [RoleController::class, 'remove_user']);
    Route::post('/roles/{id}/add_user', [RoleController::class, 'add_user']);
});

// Kiosk routes
Route::get('/kiosk', [KioskController::class, 'index']);
Route::post('/kiosk/authenticate', [KioskController::class, 'authenticate']);
Route::get('/kiosk/logout', [KioskController::class, 'logout']);
Route::get('/kiosk/unlock', [KioskController::class, 'unlock'])->middleware(['auth', 'can:manage-keys']);
Route::get('/kiosk/create_key', [KioskController::class, 'create_key'])->middleware(['auth', 'can:manage-keys']);
Route::post('/kiosk/create_key', [KioskController::class, 'create_key'])->middleware(['auth', 'can:manage-keys']);
Route::post('/kiosk/store_key', [KioskController::class, 'store_key'])->middleware(['auth', 'can:manage-keys']);

// Authentication routes
Auth::routes();
Auth::routes(['register' => false]);

// Image manipulation routes
Route::middleware(['auth'])->group(function () {
    Route::get('/image/lastupload', [ImageController::class, 'getLastImage']);
    Route::get('/image-crop/{photo_type?}/{id?}', [ImageController::class, 'imageCrop']);
    Route::post('/image-crop/{photo_type?}/{id?}', [ImageController::class, 'imageCropPost']);
});

// Teams routes
Route::middleware(['auth'])->group(function () {
    Route::get('/teams/manage', [TeamsController::class, 'manage']);
    Route::get('/teams/training', [TeamsController::class, 'training']);
    Route::get('/teams/training_request/{gatekeeper}', [TeamsController::class, 'training_request']);
    Route::get('/teams/training_cancel/{request_id}', [TeamsController::class, 'training_cancel']);
    Route::get('/teams/training_pass/{request_id}', [TeamsController::class, 'training_pass']);
    Route::get('/teams/training_fail/{request_id}', [TeamsController::class, 'training_fail']);
    Route::get('/teams/{team_id}/requests/{request_type}', [TeamsController::class, 'requests']);
    Route::get('/teams/{team_id}/dashboard', [TeamsController::class, 'dashboard']);
    Route::resource('teams', TeamsController::class);
});

// Helpdesk routes
Route::middleware(['auth'])->group(function () {
    Route::resource('helpdesk', HelpdeskController::class);
});

// Slack route
Route::get('/slack/invite', SlackInviteController::class)->name('slack.invite');

// Deploy (from Github Actions) route
Route::post('/deploy', [DeployController::class, 'deploy']);
