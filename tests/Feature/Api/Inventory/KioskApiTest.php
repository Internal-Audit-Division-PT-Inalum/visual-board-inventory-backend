<?php

use App\Domains\Core\Models\User;
use App\Domains\HR\Models\Employee;
use App\Domains\Inventory\Models\Item;
use App\Domains\Inventory\Models\Location;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

beforeEach(function () {
    Cache::flush();
});

it('can fetch public kiosk locations with grouped items', function () {
    $location = Location::factory()->create([
        'name' => 'Kiosk Location',
        'code' => 'KLSK-001',
    ]);

    $item1 = Item::factory()->create([
        'location_id' => $location->id,
        'name' => 'Kertas A4',
        'type' => 'consumable',
        'unit' => 'rim',
        'current_stock' => 12,
        'minimum_stock' => 5,
        'is_active' => true,
    ]);

    $item2 = Item::factory()->asset()->create([
        'location_id' => $location->id,
        'name' => 'Proyektor',
        'type' => 'asset',
        'unit' => 'unit',
        'current_stock' => 2,
        'minimum_stock' => 1,
        'is_active' => true,
    ]);

    // Create inactive item to ensure it's not fetched
    Item::factory()->create([
        'location_id' => $location->id,
        'is_active' => false,
    ]);

    // Borrow 1 from item2
    $user = User::factory()->create();
    Employee::factory()->create(['user_id' => $user->id]);

    $this->actingAs($user)
        ->postJson("/api/v1/inventory/items/{$item2->id}/borrow", [
            'quantity' => 1,
            'client_uuid' => Str::uuid()->toString(),
        ])->assertStatus(201);

    // Invalidate cache explicitly just in case test logic didn't trigger observers cleanly
    // actually, ItemObserver will trigger during borrow via `update(['current_stock' => ...])`

    $response = $this->getJson('/api/v1/inventory/kiosk/locations')
        ->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.name', 'Kiosk Location')
        ->assertJsonCount(2, 'data.0.items');

    // Consumable checks
    $response->assertJsonPath('data.0.items.0.name', 'Kertas A4')
        ->assertJsonPath('data.0.items.0.type', 'consumable')
        ->assertJsonPath('data.0.items.0.stock_status', 'ok')
        ->assertJsonPath('data.0.items.0.borrowed_quantity', null);

    // Asset checks
    $response->assertJsonPath('data.0.items.1.name', 'Proyektor')
        ->assertJsonPath('data.0.items.1.type', 'asset')
        ->assertJsonPath('data.0.items.1.current_stock', 1)
        ->assertJsonPath('data.0.items.1.stock_status', 'low')
        ->assertJsonPath('data.0.items.1.borrowed_quantity', 1);
});

it('invalidates cache when item is saved', function () {
    $location = Location::factory()->create();
    Item::factory()->create(['location_id' => $location->id, 'is_active' => true, 'name' => 'Initial Name']);

    // Fetch once to prime cache
    $this->getJson('/api/v1/inventory/kiosk/locations')
        ->assertOk()
        ->assertJsonPath('data.0.items.0.name', 'Initial Name');

    // Update item
    $item = Item::first();
    $item->name = 'Updated Name';
    $item->save(); // Should trigger observer

    // Fetch again
    $this->getJson('/api/v1/inventory/kiosk/locations')
        ->assertOk()
        ->assertJsonPath('data.0.items.0.name', 'Updated Name');
});
