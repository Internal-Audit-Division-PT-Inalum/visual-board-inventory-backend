<?php

use App\Http\Controllers\Api\Inventory\ItemController;
use App\Http\Controllers\Api\Inventory\LocationController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum'])->group(function () {

    Route::apiResource('locations', LocationController::class);

    Route::apiResource('items', ItemController::class)->except(['update']);

    // Transactional endpoints — consumable
    Route::post('items/{item}/take', [ItemController::class, 'take']);
    Route::post('items/{item}/add', [ItemController::class, 'add']);

    // Transactional endpoints — asset
    Route::post('items/{item}/borrow', [ItemController::class, 'borrow']);
    Route::post('items/{item}/return', [ItemController::class, 'returnItem']);

    // Audit trail
    Route::get('items/{item}/ledger', [ItemController::class, 'ledger']);

    // Monitoring
    Route::get('reports/low-stock', [ItemController::class, 'lowStock']);
});
