<?php

namespace Database\Seeders;

use App\Domains\Inventory\Models\InventoryLedger;
use App\Domains\Inventory\Models\Item;
use App\Domains\Inventory\Models\Location;
use Illuminate\Database\Seeder;

class InventorySeeder extends Seeder
{
    public function run(): void
    {
        Location::factory(2)->create()->each(function (Location $location) {
            Item::factory(5)->create(['location_id' => $location->id])->each(function (Item $item) {
                InventoryLedger::factory(3)->create(['item_id' => $item->id]);
            });
        });
    }
}
