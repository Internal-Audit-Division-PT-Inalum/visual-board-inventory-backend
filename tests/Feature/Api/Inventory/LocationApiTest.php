<?php

use App\Domains\Core\Models\User;
use App\Domains\Inventory\Models\Location;

beforeEach(function () {
    $this->user = User::factory()->create();
});

it('can list all locations', function () {
    Location::factory()->count(3)->create();

    $this->actingAs($this->user)
        ->getJson('/api/v1/inventory/locations')
        ->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonCount(3, 'data');
});

it('can create a location', function () {
    $data = [
        'name' => 'Gudang ATK Lantai 2',
        'code' => 'GDG-ATK-L2',
        'description' => 'Gudang penyimpanan ATK lantai 2',
    ];

    $this->actingAs($this->user)
        ->postJson('/api/v1/inventory/locations', $data)
        ->assertStatus(201)
        ->assertJsonPath('data.name', 'Gudang ATK Lantai 2')
        ->assertJsonPath('data.code', 'GDG-ATK-L2');

    $this->assertDatabaseHas('locations', ['code' => 'GDG-ATK-L2']);
});

it('prevents duplicate location codes', function () {
    Location::factory()->create(['code' => 'GDG-001']);

    $this->actingAs($this->user)
        ->postJson('/api/v1/inventory/locations', [
            'name' => 'Gudang Baru',
            'code' => 'GDG-001',
        ])
        ->assertStatus(422);
});

it('can show a location', function () {
    $location = Location::factory()->create();

    $this->actingAs($this->user)
        ->getJson("/api/v1/inventory/locations/{$location->id}")
        ->assertOk()
        ->assertJsonPath('data.id', $location->id);
});

it('can update a location', function () {
    $location = Location::factory()->create();

    $this->actingAs($this->user)
        ->putJson("/api/v1/inventory/locations/{$location->id}", [
            'name' => 'Updated Name',
        ])
        ->assertOk()
        ->assertJsonPath('data.name', 'Updated Name');
});

it('can delete a location', function () {
    $location = Location::factory()->create();

    $this->actingAs($this->user)
        ->deleteJson("/api/v1/inventory/locations/{$location->id}")
        ->assertOk()
        ->assertJsonPath('success', true);

    $this->assertSoftDeleted('locations', ['id' => $location->id]);
});

it('prevents unauthenticated access', function () {
    $this->getJson('/api/v1/inventory/locations')
        ->assertStatus(401);
});
