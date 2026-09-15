<?php

namespace Tests\Feature\Api\VisualBoard;

use App\Domains\Core\Models\User;
use App\Domains\Inventory\Models\Item;
use App\Domains\Inventory\Models\Location;
use App\Domains\VisualBoard\Models\Workstation;
use App\Domains\VisualBoard\Models\Zone;
use Laravel\Sanctum\Sanctum;

beforeEach(function () {
    $this->admin = User::factory()->create();
    $this->zone = Zone::factory()->create();
});

it('can fetch workstation list', function () {
    Sanctum::actingAs($this->admin);
    Workstation::factory()->count(3)->create(['zone_id' => $this->zone->id]);

    $response = $this->getJson('/api/v1/visual-board/workstations');

    $response->assertStatus(200)
        ->assertJsonStructure([
            'data' => [
                '*' => ['id', 'name', 'zone'],
            ],
        ]);
});

it('can fetch single workstation data for kiosk scanner', function () {
    $workstation = Workstation::factory()->create(['zone_id' => $this->zone->id]);

    // Assign standard ATK
    $location = Location::factory()->create();
    $item = Item::factory()->create(['location_id' => $location->id]);
    $workstation->items()->attach($item->id, ['standard_quantity' => 2]);

    $response = $this->getJson("/api/v1/visual-board/kiosk/workstations/{$workstation->id}");

    $response->assertStatus(200)
        ->assertJsonPath('data.name', $workstation->name)
        ->assertJsonPath('data.items.0.standard_quantity', 2);
});
