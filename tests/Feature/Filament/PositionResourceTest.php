<?php

namespace Tests\Feature\Filament;

use App\Domains\Core\Models\User;
use App\Domains\HR\Models\Position;
use App\Filament\Resources\Positions\Pages\CreatePosition;
use App\Filament\Resources\Positions\Pages\EditPosition;
use App\Filament\Resources\Positions\Pages\ListPositions;
use Database\Seeders\PositionSeeder;
use Livewire\Livewire;

test('authenticated user can view positions list page', function () {
    $user = User::factory()->create(['email' => 'admin@inalum.id']);
    $this->seed(PositionSeeder::class);

    $this->actingAs($user);

    Livewire::test(ListPositions::class)
        ->assertSuccessful()
        ->assertCanSeeTableRecords(Position::take(5)->get());
});

test('it can create a new position through filament form', function () {
    $user = User::factory()->create(['email' => 'admin@inalum.id']);
    $this->actingAs($user);

    Livewire::test(CreatePosition::class)
        ->fillForm([
            'name' => 'Auditor Utama',
            'hierarchy_level' => 3,
            'description' => 'Auditor spesialis investigasi korporasi',
            'is_active' => true,
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $this->assertDatabaseHas('positions', [
        'name' => 'Auditor Utama',
        'hierarchy_level' => 3,
        'description' => 'Auditor spesialis investigasi korporasi',
        'is_active' => true,
    ]);
});

test('it validates position name uniqueness and requirement', function () {
    $user = User::factory()->create(['email' => 'admin@inalum.id']);
    $this->actingAs($user);

    Position::factory()->create(['name' => 'Kepala Divisi']);

    Livewire::test(CreatePosition::class)
        ->fillForm([
            'name' => 'Kepala Divisi',
            'hierarchy_level' => 3,
        ])
        ->call('create')
        ->assertHasFormErrors(['name' => 'unique']);
});

test('it can edit an existing position', function () {
    $user = User::factory()->create(['email' => 'admin@inalum.id']);
    $this->actingAs($user);

    $position = Position::factory()->create([
        'name' => 'Staff Lama',
        'hierarchy_level' => 6,
    ]);

    Livewire::test(EditPosition::class, ['record' => $position->getKey()])
        ->fillForm([
            'name' => 'Staff Baru Diperbarui',
            'hierarchy_level' => 5,
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($position->fresh()->name)->toBe('Staff Baru Diperbarui')
        ->and($position->fresh()->hierarchy_level)->toBe(5);
});
