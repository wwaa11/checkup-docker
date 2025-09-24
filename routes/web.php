<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\NumberController;

Route::get('/', function () {
    return view('welcome');
});

// Reverb test routes
Route::get('/send', [NumberController::class, 'sendPage'])->name('send');
Route::get('/receive', [NumberController::class, 'receivePage'])->name('receive');
Route::post('/send-number', [NumberController::class, 'sendNumber'])->name('send.number');
