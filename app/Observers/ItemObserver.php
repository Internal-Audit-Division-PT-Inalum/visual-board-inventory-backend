<?php

namespace App\Observers;

use App\Domains\Inventory\Models\Item;
use Illuminate\Support\Facades\Cache;

class ItemObserver
{
    private function clearKioskCache(): void
    {
        Cache::forget('inventory:kiosk:locations');
    }

    public function saved(Item $item): void
    {
        $this->clearKioskCache();
    }

    public function deleted(Item $item): void
    {
        $this->clearKioskCache();
    }

    public function restored(Item $item): void
    {
        $this->clearKioskCache();
    }

    public function forceDeleted(Item $item): void
    {
        $this->clearKioskCache();
    }
}
