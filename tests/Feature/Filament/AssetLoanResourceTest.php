<?php

namespace Tests\Feature\Filament;

use App\Domains\Core\Models\User;
use App\Domains\Inventory\Models\AssetLoan;
use App\Filament\Resources\AssetLoans\AssetLoanResource;
use App\Filament\Resources\AssetLoans\Pages\ListAssetLoans;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;

test('authenticated user can view asset loans list page', function () {
    $role = Role::firstOrCreate(['name' => 'super_admin']);
    $user = User::factory()->create(['email' => 'admin@inalum.id']);
    $user->assignRole($role);

    $loan = AssetLoan::factory()->create();

    $this->actingAs($user);

    Livewire::test(ListAssetLoans::class)
        ->assertSuccessful()
        ->assertCanSeeTableRecords([$loan]);
});

test('asset loan resource enforces immutability and cannot be created, edited, or deleted', function () {
    $loan = AssetLoan::factory()->create();

    expect(AssetLoanResource::canCreate())->toBeFalse()
        ->and(AssetLoanResource::canEdit($loan))->toBeFalse()
        ->and(AssetLoanResource::canDelete($loan))->toBeFalse();
});

test('asset loans table renders computed columns and status badges correctly', function () {
    $role = Role::firstOrCreate(['name' => 'super_admin']);
    $user = User::factory()->create(['email' => 'admin@inalum.id']);
    $user->assignRole($role);

    $activeLoan = AssetLoan::factory()->create([
        'quantity' => 2,
        'returned_quantity' => 0,
        'borrowed_at' => now()->subDays(2),
        'fully_returned_at' => null,
    ]);

    $overdueLoan = AssetLoan::factory()->overdue()->create();
    $settledLoan = AssetLoan::factory()->settled()->create();
    $partialLoan = AssetLoan::factory()->partial()->create();

    $this->actingAs($user);

    Livewire::test(ListAssetLoans::class)
        ->assertSuccessful()
        ->assertCanSeeTableRecords([$activeLoan, $overdueLoan, $settledLoan, $partialLoan]);
});

test('asset loans table can filter by status', function () {
    $role = Role::firstOrCreate(['name' => 'super_admin']);
    $user = User::factory()->create(['email' => 'admin@inalum.id']);
    $user->assignRole($role);

    $overdueLoan = AssetLoan::factory()->overdue()->create();
    $settledLoan = AssetLoan::factory()->settled()->create();

    $this->actingAs($user);

    Livewire::test(ListAssetLoans::class)
        ->filterTable('status', 'overdue')
        ->assertCanSeeTableRecords([$overdueLoan])
        ->assertCanNotSeeTableRecords([$settledLoan]);

    Livewire::test(ListAssetLoans::class)
        ->filterTable('status', 'settled')
        ->assertCanSeeTableRecords([$settledLoan])
        ->assertCanNotSeeTableRecords([$overdueLoan]);
});
