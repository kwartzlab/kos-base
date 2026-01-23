<?php

use App\Http\Controllers\Api\ApiTokenController;
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
Route::middleware('api_token')->get('/token', [ApiTokenController::class, 'show']);

Route::get('/public-info', [PublicInfoController::class, 'index']);
Route::middleware('api_token')->get('/hello', function () {
    return response()->json(['message' => 'Hello World']);
});
