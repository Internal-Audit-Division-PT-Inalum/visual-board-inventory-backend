<?php

use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {

    Route::prefix('visual-board')->group(base_path('routes/api/visual-board.php'));

    Route::prefix('inventory')->group(base_path('routes/api/inventory.php'));

});
