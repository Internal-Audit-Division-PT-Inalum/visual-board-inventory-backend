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

it('can fetch active catalog items with borrowed quantities', function () {
    $item = Item::factory()->asset()->create([
        'location_id' => $this->location->id,
        'current_stock' => 10,
        'is_active' => true,
    ]);

    // Borrow 3
    $this->actingAs($this->user)
        ->postJson("/api/v1/inventory/items/{$item->id}/borrow", [
            'quantity' => 3,
            'client_uuid' => Str::uuid()->toString(),
        ])->assertStatus(201);

    // Fetch catalog
    $response = $this->actingAs($this->user)
        ->getJson('/api/v1/inventory/catalog')
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.id', $item->id)
        ->assertJsonPath('data.0.borrowed_quantity', 3)
        ->assertJsonStructure([
            'success',
            'data' => [
                '*' => [
                    'id', 'sku', 'name', 'type', 'unit', 'location_id', 'current_stock',
                    'minimum_stock', 'image_thumb_url', 'updated_at', 'borrowed_quantity',
                ],
            ],
            'meta' => ['generated_at'],
        ]);

    expect($response->headers->get('ETag'))->not->toBeNull();
});

it('returns 304 if ETag matches', function () {
    $item = Item::factory()->asset()->create([
        'location_id' => $this->location->id,
        'is_active' => true,
    ]);

    $response = $this->actingAs($this->user)
        ->getJson('/api/v1/inventory/catalog');

    $etag = $response->headers->get('ETag');

    $this->actingAs($this->user)
        ->getJson('/api/v1/inventory/catalog', ['If-None-Match' => $etag])
        ->assertStatus(304);
});
