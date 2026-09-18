<?php

use App\Http\Controllers\Api\Portal\BulletinController;
use App\Http\Controllers\Api\Portal\QuickLinkController;
use Illuminate\Support\Facades\Route;

// Kiosk Read-Only Endpoints (No Auth required for TV/Scanner)
Route::prefix('kiosk')->group(function () {
    // Bulletin Routes
    Route::get('/bulletins', [BulletinController::class, 'kioskIndex']);
    Route::get('/bulletins/{bulletin}/document', [BulletinController::class, 'kioskDocument']);

    // QuickLink Routes
    Route::get('/quick-links', [QuickLinkController::class, 'kioskIndex']);
});
