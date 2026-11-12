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

it('can list items with pagination', function () {
    Item::factory()->count(3)->create(['location_id' => $this->location->id]);

    $this->actingAs($this->user)
        ->getJson('/api/v1/inventory/items')
        ->assertOk()
        ->assertJsonCount(3, 'data');
});

it('can create a consumable item', function () {
    $data = [
        'location_id' => $this->location->id,
        'name' => 'Kertas HVS A4',
        'sku' => 'ATK-KRT-001',
        'type' => 'consumable',
        'unit' => 'rim',
        'current_stock' => 50,
        'minimum_stock' => 10,
    ];

    $this->actingAs($this->user)
        ->postJson('/api/v1/inventory/items', $data)
        ->assertStatus(201)
        ->assertJsonPath('data.name', 'Kertas HVS A4')
        ->assertJsonPath('data.type', 'consumable');

    $this->assertDatabaseHas('items', ['sku' => 'ATK-KRT-001']);
});

it('can create an asset item', function () {
    $data = [
        'location_id' => $this->location->id,
        'name' => 'Proyektor Epson',
        'sku' => 'AST-PRY-001',
        'type' => 'asset',
        'unit' => 'pcs',
        'current_stock' => 3,
        'minimum_stock' => 1,
    ];

    $this->actingAs($this->user)
        ->postJson('/api/v1/inventory/items', $data)
        ->assertStatus(201)
        ->assertJsonPath('data.type', 'asset');
});

it('can delete an item', function () {
    $item = Item::factory()->create(['location_id' => $this->location->id]);

    $this->actingAs($this->user)
        ->deleteJson("/api/v1/inventory/items/{$item->id}")
        ->assertOk();

    $this->assertSoftDeleted('items', ['id' => $item->id]);
});

it('can take a consumable item and ledger is recorded', function () {
    $item = Item::factory()->create([
        'location_id' => $this->location->id,
        'type' => 'consumable',
        'current_stock' => 20,
    ]);

    $this->actingAs($this->user)
        ->postJson("/api/v1/inventory/items/{$item->id}/take", [
            'quantity' => 5,
            'notes' => 'Untuk rapat direksi',
            'client_uuid' => Str::uuid()->toString(),
        ])
        ->assertStatus(201)
        ->assertJsonPath('data.type', 'out')
        ->assertJsonPath('data.quantity', 5)
        ->assertJsonPath('data.stock_before', 20)
        ->assertJsonPath('data.stock_after', 15);

    $this->assertDatabaseHas('items', ['id' => $item->id, 'current_stock' => 15]);
    $this->assertDatabaseHas('inventory_ledgers', ['item_id' => $item->id, 'type' => 'out']);
});

it('rejects take when stock is insufficient', function () {
    $item = Item::factory()->create([
        'location_id' => $this->location->id,
        'type' => 'consumable',
        'current_stock' => 3,
    ]);

    $this->actingAs($this->user)
        ->postJson("/api/v1/inventory/items/{$item->id}/take", [
            'quantity' => 10,
            'client_uuid' => Str::uuid()->toString(),
        ])
        ->assertStatus(422)
        ->assertJsonPath('success', false);

    $this->assertDatabaseHas('items', ['id' => $item->id, 'current_stock' => 3]);
});

it('can add stock to a consumable item', function () {
    $item = Item::factory()->create([
        'location_id' => $this->location->id,
        'type' => 'consumable',
        'current_stock' => 10,
    ]);

    $this->actingAs($this->user)
        ->postJson("/api/v1/inventory/items/{$item->id}/add", [
            'quantity' => 20,
            'client_uuid' => Str::uuid()->toString(),
        ])
        ->assertStatus(201)
        ->assertJsonPath('data.type', 'in')
        ->assertJsonPath('data.stock_after', 30);

    $this->assertDatabaseHas('items', ['id' => $item->id, 'current_stock' => 30]);
});

it('can borrow an asset item', function () {
    $item = Item::factory()->asset()->create([
        'location_id' => $this->location->id,
        'current_stock' => 5,
    ]);

    $this->actingAs($this->user)
        ->postJson("/api/v1/inventory/items/{$item->id}/borrow", [
            'quantity' => 2,
            'notes' => 'Untuk presentasi',
            'client_uuid' => Str::uuid()->toString(),
        ])
        ->assertStatus(201)
        ->assertJsonPath('data.type', 'borrow')
        ->assertJsonPath('data.stock_after', 3);

    $this->assertDatabaseHas('items', ['id' => $item->id, 'current_stock' => 3]);
});

it('can return a borrowed asset', function () {
    $item = Item::factory()->asset()->create([
        'location_id' => $this->location->id,
        'current_stock' => 5,
    ]);

    $this->actingAs($this->user)
        ->postJson("/api/v1/inventory/items/{$item->id}/borrow", [
            'quantity' => 2,
            'client_uuid' => Str::uuid()->toString(),
        ])
        ->assertStatus(201);

    $this->actingAs($this->user)
        ->postJson("/api/v1/inventory/items/{$item->id}/return", [
            'quantity' => 2,
            'client_uuid' => Str::uuid()->toString(),
        ])
        ->assertStatus(201)
        ->assertJsonPath('data.type', 'return')
        ->assertJsonPath('data.stock_after', 5);

    $this->assertDatabaseHas('items', ['id' => $item->id, 'current_stock' => 5]);
});

it('rejects take on asset item', function () {
    $item = Item::factory()->asset()->create([
        'location_id' => $this->location->id,
        'current_stock' => 5,
    ]);

    $this->actingAs($this->user)
        ->postJson("/api/v1/inventory/items/{$item->id}/take", [
            'quantity' => 1,
            'client_uuid' => Str::uuid()->toString(),
        ])
        ->assertStatus(422)
        ->assertJsonPath('success', false);
});

it('rejects borrow on consumable item', function () {
    $item = Item::factory()->create([
        'location_id' => $this->location->id,
        'type' => 'consumable',
        'current_stock' => 10,
    ]);

    $this->actingAs($this->user)
        ->postJson("/api/v1/inventory/items/{$item->id}/borrow", [
            'quantity' => 1,
            'client_uuid' => Str::uuid()->toString(),
        ])
        ->assertStatus(422)
        ->assertJsonPath('success', false);
});

it('prevents stock from going negative with sequential takes', function () {
    $item = Item::factory()->create([
        'location_id' => $this->location->id,
        'type' => 'consumable',
        'current_stock' => 5,
    ]);

    $this->actingAs($this->user)
        ->postJson("/api/v1/inventory/items/{$item->id}/take", [
            'quantity' => 3,
            'client_uuid' => Str::uuid()->toString(),
        ])
        ->assertStatus(201);

    $this->actingAs($this->user)
        ->postJson("/api/v1/inventory/items/{$item->id}/take", [
            'quantity' => 3,
            'client_uuid' => Str::uuid()->toString(),
        ])
        ->assertStatus(422);

    $item->refresh();
    expect($item->current_stock)->toBeGreaterThanOrEqual(0);
    expect($item->current_stock)->toBe(2);
});

it('returns replayed response for identical client_uuid and user', function () {
    $item = Item::factory()->create([
        'location_id' => $this->location->id,
        'type' => 'consumable',
        'current_stock' => 20,
    ]);

    $clientUuid = Str::uuid()->toString();

    $this->actingAs($this->user)
        ->postJson("/api/v1/inventory/items/{$item->id}/take", [
            'quantity' => 5,
            'client_uuid' => $clientUuid,
        ])
        ->assertStatus(201)
        ->assertJsonPath('meta.replayed', false);

    $this->assertDatabaseHas('items', ['id' => $item->id, 'current_stock' => 15]);

    $this->actingAs($this->user)
        ->postJson("/api/v1/inventory/items/{$item->id}/take", [
            'quantity' => 5,
            'client_uuid' => $clientUuid,
        ])
        ->assertStatus(200)
        ->assertJsonPath('meta.replayed', true)
        ->assertJsonPath('data.client_uuid', $clientUuid);

    $this->assertDatabaseHas('items', ['id' => $item->id, 'current_stock' => 15]);
});

it('returns 409 conflict if identical client_uuid used by different user', function () {
    $item = Item::factory()->create([
        'location_id' => $this->location->id,
        'type' => 'consumable',
        'current_stock' => 20,
    ]);

    $clientUuid = Str::uuid()->toString();

    $this->actingAs($this->user)
        ->postJson("/api/v1/inventory/items/{$item->id}/take", [
            'quantity' => 5,
            'client_uuid' => $clientUuid,
        ])
        ->assertStatus(201);

    $userB = User::factory()->create();
    $this->actingAs($userB)
        ->postJson("/api/v1/inventory/items/{$item->id}/take", [
            'quantity' => 5,
            'client_uuid' => $clientUuid,
        ])
        ->assertStatus(409)
        ->assertJsonPath('meta.code', 'IDEMPOTENCY_CONFLICT');
});

it('can fetch ledger history for an item', function () {
    $item = Item::factory()->create([
        'location_id' => $this->location->id,
        'type' => 'consumable',
        'current_stock' => 50,
    ]);

    $this->actingAs($this->user)
        ->postJson("/api/v1/inventory/items/{$item->id}/take", [
            'quantity' => 5,
            'client_uuid' => Str::uuid()->toString(),
        ]);
    $this->actingAs($this->user)
        ->postJson("/api/v1/inventory/items/{$item->id}/add", [
            'quantity' => 10,
            'client_uuid' => Str::uuid()->toString(),
        ]);

    $this->actingAs($this->user)
        ->getJson("/api/v1/inventory/items/{$item->id}/ledger")
        ->assertOk()
        ->assertJsonCount(2, 'data');
});

it('prevents unauthenticated access to items', function () {
    $this->getJson('/api/v1/inventory/items')
        ->assertStatus(401);
});
