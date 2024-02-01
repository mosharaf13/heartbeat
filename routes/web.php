<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HeartbeatExportController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Auth::routes();

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

Route::get('/heartbeat/submit', [App\Http\Controllers\HeartBeatController::class, 'store'])->name('store');
Route::get('/heartbeat/update', [App\Http\Controllers\HeartBeatController::class, 'update'])->name('update');

Route::get('/heartbeat/latest', [App\Http\Controllers\HeartBeatController::class, 'latest'])->name('index');
Route::get('/heartbeat', [App\Http\Controllers\HeartBeatController::class, 'index'])->name('index');
Route::get('/heartbeats', [App\Http\Controllers\HeartBeatController::class, 'all'])->name('all');
Route::get('/populate/threshold', [App\Http\Controllers\HeartBeatController::class, 'populateThreshold'])->name('populate-threshold');
Route::get('/calculate-threshold-breach', [App\Http\Controllers\HeartBeatController::class, 'calculateThresholdBreach'])->name('calculate-threshold-breach');
Route::get('/calculate-gameplay-time', [App\Http\Controllers\HeartBeatController::class, 'calculateGameplayTime'])->name('calculate-threshold-breach');


Route::get('/heartbeat/threshold', [App\Http\Controllers\HeartBeatController::class, 'threshold'])->name('threshold');
Route::get('/export-heartbeats', [HeartbeatExportController::class, 'export']);
Route::post('/import-excel', [App\Http\Controllers\HeartBeatController::class, 'importExcel']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
    Route::get('/search-stats', [App\Http\Controllers\SearchStatController::class, 'index'])->name('search-stats');
    Route::post('/keywords', [App\Http\Controllers\SearcherController::class, 'upload'])->name('keywords.upload');
    Route::get('raw-response/{id}', [\App\Http\Controllers\SearchStatController::class, 'rawResponse']);
});
