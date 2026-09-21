<?php

use App\Http\Controllers\Api\VisualBoard\AbnormalityController;
use App\Http\Controllers\Api\VisualBoard\KioskGeneralController;
use App\Http\Controllers\Api\VisualBoard\KioskWorkstationController;
use App\Http\Controllers\Api\VisualBoard\ScheduleController;
use App\Http\Controllers\Api\VisualBoard\VisualBoardController;
use App\Http\Controllers\Api\VisualBoard\WorkstationController;
use App\Http\Controllers\Api\VisualBoard\ZoneController;
use Illuminate\Support\Facades\Route;

// Read-only Kiosk Endpoints
Route::prefix('kiosk')->group(function () {
    Route::get('/', [VisualBoardController::class, 'index']);
    Route::get('/workstations/{id}', [KioskWorkstationController::class, 'show']);
    Route::get('/workstations/by-employee/{employee_id}', [KioskWorkstationController::class, 'showByEmployee']);
    Route::get('/trend-abnormality', [KioskGeneralController::class, 'trendAbnormality']);
    Route::get('/general-documents', [KioskGeneralController::class, 'generalDocuments']);
});

Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/zones/scan/{zonaId}', [ZoneController::class, 'scan']);

    Route::get('/schedules/{id}', [ScheduleController::class, 'show']);
    Route::patch('/schedule-records/{recordId}/update-day', [ScheduleController::class, 'updateDay']);

    // Abnormalities
    Route::apiResource('abnormalities', AbnormalityController::class);
    Route::patch('abnormalities/{id}/progress', [AbnormalityController::class, 'updateProgress']);

    Route::apiResource('workstations', WorkstationController::class);
});
