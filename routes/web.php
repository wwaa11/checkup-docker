<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\NumberController;
use App\Http\Controllers\PatientController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\StationController;
use Illuminate\Support\Facades\Route;

Route::get('/test', [ServiceController::class, 'test']);

Route::get('/login', [AuthController::class, 'index'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::get('/logout', [AuthController::class, 'logout'])->name('logout');

Route::prefix('/sms/{HN}')->group(function () {
    Route::get('/', [PatientController::class, 'sms'])->name('patient.sms');
    Route::post('/check', [PatientController::class, 'smsCheck'])->name('patient.sms.check');
    Route::post('/check-in', [PatientController::class, 'smsCheckIn'])->name('patient.sms.check-in');
    Route::post('/check-number', [PatientController::class, 'smsCheckNumber'])->name('patient.sms.check-number');
});

Route::middleware(['auth'])->group(function () {
    Route::get('/services', [ServiceController::class, 'index'])->name('services.index');
    Route::get('/services/queue-status', [ServiceController::class, 'getQueueStatus'])->name('services.queue-status');
    Route::post('/services/dispatch/generate-number', [ServiceController::class, 'dispatchGenerateNumber'])->name('services.dispatch.generate-number');

    Route::get('/services/queue-details', [ServiceController::class, 'queueDetails'])->name('services.queue.details');
    Route::get('/services/queue-stats', [ServiceController::class, 'queueStats'])->name('services.queue.stats');
    Route::get('/services/job/{jobId}', [ServiceController::class, 'viewJob'])->name('services.job.view');

    Route::get('/stations', [StationController::class, 'index'])->name('stations.index');
    Route::get('/station/{station}', [StationController::class, 'staionIndex'])->name('stations.view');
    Route::get('/station/{station}/register', [StationController::class, 'register'])->name('stations.register');
});

// Reverb test routes
Route::get('/send', [NumberController::class, 'sendPage'])->name('send');
Route::get('/receive', [NumberController::class, 'receivePage'])->name('receive');
Route::post('/send-number', [NumberController::class, 'sendNumber'])->name('send.number');
