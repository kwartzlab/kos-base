<?php

use App\Http\Controllers\Api\FormSubmissionOutboxController;
use App\Http\Controllers\Api\FormSubmissionsController;
use App\Http\Controllers\Api\TokensController;
use App\Http\Controllers\Api\UsersController;
use App\Http\Controllers\PublicInfoController;
use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

Route::middleware('api_token')->get('/token', [TokensController::class, 'show']);
Route::middleware('api_token')->get('/users', [UsersController::class, 'index']);
Route::middleware('api_token')->get('/users/{user}', [UsersController::class, 'show']);
Route::middleware('api_token')->get('/form_submissions/{form_submission}', [FormSubmissionsController::class, 'show']);
Route::middleware('api_token')->get('/form_submissions/outbox/next', [FormSubmissionOutboxController::class, 'next']);
Route::middleware('api_token')->post('/form_submissions/outbox/{form_submission_outbox}', [FormSubmissionOutboxController::class, 'markProcessed']);
Route::middleware('api_token')->get('/form_submissions/outbox/{form_submission_outbox}', [FormSubmissionOutboxController::class, 'show']);

Route::get('/public-info', [PublicInfoController::class, 'index']);
