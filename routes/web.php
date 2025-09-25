<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\NumberController;
use App\Http\Controllers\ServiceController;
use Illuminate\Support\Facades\Route;

Route::get('/login', [AuthController::class, 'index'])->name('login');

Route::get('/services', [ServiceController::class, 'index'])->name('services.index');

// Reverb test routes
Route::get('/send', [NumberController::class, 'sendPage'])->name('send');
Route::get('/receive', [NumberController::class, 'receivePage'])->name('receive');
Route::post('/send-number', [NumberController::class, 'sendNumber'])->name('send.number');
