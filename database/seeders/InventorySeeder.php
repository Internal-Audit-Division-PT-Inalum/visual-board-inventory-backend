<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Domains\Inventory\Models\Location;
use App\Domains\Inventory\Models\Item;
use App\Domains\Inventory\Models\InventoryLedger;

class InventorySeeder extends Seeder
{
    public function run(): void
    {
        Location::factory(3)->create()->each(function (Location $location) {
            Item::factory(5)->create(['location_id' => $location->id])->each(function (Item $item) {
                InventoryLedger::factory(5)->create(['item_id' => $item->id]);
            });
        });
    }
}
