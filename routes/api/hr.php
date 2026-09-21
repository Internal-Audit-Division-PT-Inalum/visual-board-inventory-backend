<?php

use App\Http\Controllers\Api\HR\AttendanceController;
use App\Http\Controllers\Api\HR\DepartmentController;
use App\Http\Controllers\Api\HR\EmployeeController;
use App\Http\Controllers\Api\HR\KioskAttendanceController;
use App\Http\Controllers\Api\HR\KioskOrganizationController;
use Illuminate\Support\Facades\Route;

// Kiosk Read-Only Endpoints (No Auth required for TV/Scanner)
Route::prefix('kiosk')->group(function () {
    Route::get('/attendance-summary', [KioskAttendanceController::class, 'summary']);
    Route::get('/organization-documents', [KioskOrganizationController::class, 'documents']);
    Route::post('/attendances/scan', [KioskAttendanceController::class, 'scan']);
});

Route::middleware(['auth:sanctum'])->group(function () {
    Route::post('/attendances/scan', [AttendanceController::class, 'scan']);
    Route::apiResource('departments', DepartmentController::class);
    Route::apiResource('employees', EmployeeController::class);
});
