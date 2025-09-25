<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\NumberController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\StationController;
use Illuminate\Support\Facades\Route;

Route::get('/login', [AuthController::class, 'index'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::get('/logout', [AuthController::class, 'logout'])->name('logout');

Route::middleware(['auth'])->group(function () {
    Route::get('/station/default', [StationController::class, 'index'])->name('stations.index');
    Route::get('/station/vs', [StationController::class, 'vs'])->name('stations.vs');
    Route::get('/station/lab', [StationController::class, 'lab'])->name('stations.lab');
});

Route::get('/services', [ServiceController::class, 'index'])->name('services.index');

// Reverb test routes
Route::get('/send', [NumberController::class, 'sendPage'])->name('send');
Route::get('/receive', [NumberController::class, 'receivePage'])->name('receive');
Route::post('/send-number', [NumberController::class, 'sendNumber'])->name('send.number');
