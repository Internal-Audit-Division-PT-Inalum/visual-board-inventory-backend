<?php

use App\Domains\Portal\Models\Bulletin;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::call(function () {
    Bulletin::query()
        ->where('is_active', true)
        ->whereNotNull('expired_at')
        ->where('expired_at', '<', now())
        ->update(['is_active' => false]);
})->everyMinute()->name('deactivate-expired-bulletins')->withoutOverlapping();
