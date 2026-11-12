<?php

use App\Http\Controllers\Api\Inventory\CatalogController;
use App\Http\Controllers\Api\Inventory\ItemController;
use App\Http\Controllers\Api\Inventory\KioskController;
use App\Http\Controllers\Api\Inventory\LoanController;
use App\Http\Controllers\Api\Inventory\LocationController;
use Illuminate\Support\Facades\Route;

Route::middleware('throttle:60,1')->group(function () {
    Route::get('kiosk/locations', [KioskController::class, 'locations']);
});

Route::middleware(['auth:sanctum'])->group(function () {
    Route::apiResource('locations', LocationController::class);
    Route::apiResource('items', ItemController::class)->except(['update']);

    Route::post('items/{item}/take', [ItemController::class, 'take']);
    Route::post('items/{item}/add', [ItemController::class, 'add']);

    Route::post('items/{item}/borrow', [ItemController::class, 'borrow']);
    Route::post('items/{item}/return', [ItemController::class, 'returnItem']);

    Route::get('items/{item}/ledger', [ItemController::class, 'ledger']);
    Route::get('reports/low-stock', [ItemController::class, 'lowStock']);

    Route::get('loans/mine', [LoanController::class, 'mine']);
    Route::get('catalog', [CatalogController::class, 'index']);
});
