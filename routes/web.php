<?php

use App\Http\Controllers\Admin\PrintStickerController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->to('/admin');
});

Route::get('/login', function () {
    return redirect()->route('filament.admin.auth.login');
})->name('login');

Route::middleware(['auth'])->prefix('admin/print')->name('filament.admin.print.')->group(function () {
    Route::get('/locations', [PrintStickerController::class, 'printLocations'])->name('locations');
    Route::get('/items', [PrintStickerController::class, 'printItems'])->name('items');
});
