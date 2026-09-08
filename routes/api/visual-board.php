<?php

use App\Http\Controllers\Api\VisualBoard\AbnormalityController;
use App\Http\Controllers\Api\VisualBoard\ScheduleController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/schedules/{id}', [ScheduleController::class, 'show']);
    Route::patch('/schedule-records/{recordId}/update-day', [ScheduleController::class, 'updateDay']);

    // Abnormalities
    Route::apiResource('abnormalities', AbnormalityController::class);
    Route::patch('abnormalities/{id}/progress', [AbnormalityController::class, 'updateProgress']);
});
