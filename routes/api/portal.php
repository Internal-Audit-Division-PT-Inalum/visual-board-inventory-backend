<?php

use App\Http\Controllers\Api\Portal\BulletinController;
use App\Http\Controllers\Api\Portal\QuickLinkController;
use Illuminate\Support\Facades\Route;

Route::prefix('kiosk')->group(function () {
    Route::get('/bulletins', [BulletinController::class, 'kioskIndex']);
    Route::get('/bulletins/{bulletin}/document', [BulletinController::class, 'kioskDocument']);
    Route::get('/quick-links', [QuickLinkController::class, 'kioskIndex']);
});
