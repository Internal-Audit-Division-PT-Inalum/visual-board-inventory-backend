<?php

use App\Http\Controllers\Api\HR\AttendanceController;
use App\Http\Controllers\Api\HR\DepartmentController;
use App\Http\Controllers\Api\HR\EmployeeController;
use App\Http\Controllers\Api\HR\KioskAttendanceController;
use App\Http\Controllers\Api\HR\KioskOrganizationController;
use Illuminate\Support\Facades\Route;

Route::prefix('kiosk')->group(function () {
    Route::middleware(['throttle:kiosk-read'])->group(function () {
        Route::get('/attendance-summary', [KioskAttendanceController::class, 'summary']);
        Route::get('/organization-documents', [KioskOrganizationController::class, 'documents']);
    });

    Route::post('/attendances/scan', [KioskAttendanceController::class, 'scan'])
        ->middleware('throttle:kiosk-scan');
});

Route::middleware(['auth:sanctum', 'throttle:api-authenticated'])->group(function () {
    Route::post('/attendances/scan', [AttendanceController::class, 'scan']);
    Route::apiResource('departments', DepartmentController::class);
    Route::apiResource('employees', EmployeeController::class);
});
