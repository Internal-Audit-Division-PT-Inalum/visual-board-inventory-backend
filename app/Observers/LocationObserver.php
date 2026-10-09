<?php

namespace App\Observers;

use App\Domains\Inventory\Models\Location;
use Illuminate\Support\Facades\Cache;

class LocationObserver
{
    private function clearKioskCache(): void
    {
        Cache::forget('inventory:kiosk:locations');
    }

    public function saved(Location $location): void
    {
        $this->clearKioskCache();
    }

    public function deleted(Location $location): void
    {
        $this->clearKioskCache();
    }

    public function restored(Location $location): void
    {
        $this->clearKioskCache();
    }

    public function forceDeleted(Location $location): void
    {
        $this->clearKioskCache();
    }
}
