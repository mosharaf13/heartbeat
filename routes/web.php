<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

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

Route::get('/heartbeat/latesst', [App\Http\Controllers\HeartBeatController::class, 'latest'])->name('index');
Route::get('/heartbeat', [App\Http\Controllers\HeartBeatController::class, 'index'])->name('index');

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
    Route::get('/search-stats', [App\Http\Controllers\SearchStatController::class, 'index'])->name('search-stats');
    Route::post('/keywords', [App\Http\Controllers\SearcherController::class, 'upload'])->name('keywords.upload');
    Route::get('raw-response/{id}', [\App\Http\Controllers\SearchStatController::class, 'rawResponse']);
});
