<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\SearchStatController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SearcherController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::post('login', [AuthController::class, 'login']);
Route::get('/sendChartData', [App\Http\Controllers\HeartBeatController::class, 'sendChartData'])->name('sendChartData');
Route::get('/send-heart-beat-chart-data', [App\Http\Controllers\HeartBeatController::class, 'sendHeartbeatChartData'])->name('send-heartbeat-chart-data');
Route::get('/send-threshold-breach-data', [App\Http\Controllers\HeartBeatController::class, 'sendThresholdBreachData'])->name('send-threshold-breach-data');

Route::middleware('auth:sanctum')->group(function () {
    Route::post('logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');
    Route::get('user', [AuthController::class, 'user'])->middleware('auth:sanctum');
    Route::get('keywords', [SearchStatController::class, 'keywords']);
    Route::get('search-stats', [SearchStatController::class, 'listStats']);
    Route::get('raw-response/{id}', [SearchStatController::class, 'rawResponse']);

    Route::post('keywords', [SearcherController::class, 'upload']);
});
