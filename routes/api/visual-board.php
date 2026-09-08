<?php

use App\Http\Controllers\Api\VisualBoard\AbnormalityController;
use App\Http\Controllers\Api\VisualBoard\ScheduleController;
use App\Http\Controllers\Api\VisualBoard\VisualBoardController;
use Illuminate\Support\Facades\Route;

// Kiosk Read-Only Endpoints (No Auth required for TV)
Route::prefix('kiosk')->group(function () {
    Route::get('/', [VisualBoardController::class, 'index']);
});

Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/schedules/{id}', [ScheduleController::class, 'show']);
    Route::patch('/schedule-records/{recordId}/update-day', [ScheduleController::class, 'updateDay']);

    // Abnormalities
    Route::apiResource('abnormalities', AbnormalityController::class);
    Route::patch('abnormalities/{id}/progress', [AbnormalityController::class, 'updateProgress']);
});
