<?php

use App\Domains\Core\Models\User;
use App\Domains\HR\Models\Employee;
use App\Domains\Inventory\Models\Item;
use App\Domains\Inventory\Models\Location;
use Illuminate\Support\Str;

beforeEach(function () {
    $this->user = User::factory()->create();
    Employee::factory()->create([
        'user_id' => $this->user->id,
    ]);
    $this->location = Location::factory()->create();
});

it('can fetch open loans by default', function () {
    $item = Item::factory()->asset()->create([
        'location_id' => $this->location->id,
        'current_stock' => 10,
    ]);

    // Borrow item
    $this->actingAs($this->user)
        ->postJson("/api/v1/inventory/items/{$item->id}/borrow", [
            'quantity' => 2,
            'client_uuid' => Str::uuid()->toString(),
        ])->assertStatus(201);

    $this->actingAs($this->user)
        ->getJson('/api/v1/inventory/loans/mine')
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.status', 'borrowed')
        ->assertJsonPath('data.0.outstanding_quantity', 2);
});

it('can fetch all loans including returned ones', function () {
    $item = Item::factory()->asset()->create([
        'location_id' => $this->location->id,
        'current_stock' => 10,
    ]);

    // Borrow item
    $this->actingAs($this->user)
        ->postJson("/api/v1/inventory/items/{$item->id}/borrow", [
            'quantity' => 2,
            'client_uuid' => Str::uuid()->toString(),
        ])->assertStatus(201);

    // Return item fully
    $this->actingAs($this->user)
        ->postJson("/api/v1/inventory/items/{$item->id}/return", [
            'quantity' => 2,
            'client_uuid' => Str::uuid()->toString(),
        ])->assertStatus(201);

    // Open loans should be 0
    $this->actingAs($this->user)
        ->getJson('/api/v1/inventory/loans/mine')
        ->assertOk()
        ->assertJsonCount(0, 'data');

    // All loans should be 1
    $this->actingAs($this->user)
        ->getJson('/api/v1/inventory/loans/mine?status=all')
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.status', 'returned');
});
