<?php

use App\Http\Controllers\Api\Portal\BulletinController;
use Illuminate\Support\Facades\Route;

// Kiosk Read-Only Endpoints (No Auth required for TV/Scanner)
Route::prefix('kiosk')->group(function () {
    Route::get('/bulletins', [BulletinController::class, 'kioskIndex']);
});
