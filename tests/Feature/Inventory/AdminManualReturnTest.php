<?php

namespace Tests\Feature\Inventory;

use App\Domains\Core\Models\User;
use App\Domains\Inventory\Models\AssetLoan;
use App\Domains\Inventory\Models\AssetLoanReturn;
use App\Domains\Inventory\Models\InventoryLedger;
use App\Domains\Inventory\Models\Item;
use App\Filament\Resources\AssetLoans\Pages\ListAssetLoans;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;

test('inventory admin can process manual asset return through filament table action', function () {
    $role = Role::firstOrCreate(['name' => 'super_admin']);
    $admin = User::factory()->create(['email' => 'admin@inalum.id']);
    $admin->assignRole($role);

    $item = Item::factory()->asset()->create([
        'name' => 'Laser Range Finder',
        'current_stock' => 5,
        'unit' => 'unit',
    ]);

    $loan = AssetLoan::factory()->create([
        'item_id' => $item->id,
        'quantity' => 2,
        'returned_quantity' => 0,
        'borrowed_at' => now()->subDays(2),
        'fully_returned_at' => null,
    ]);

    $this->actingAs($admin);

    Livewire::test(ListAssetLoans::class)
        ->callTableAction('return_asset', $loan, data: [
            'return_quantity' => 1,
            'notes' => 'Satu unit dikembalikan dalam kondisi prima.',
        ])
        ->assertHasNoTableActionErrors();

    // Verifikasi penambahan stok fisik barang
    expect($item->fresh()->current_stock)->toBe(6);

    // Verifikasi pembaruan data pinjaman
    $freshLoan = $loan->fresh();
    expect($freshLoan->returned_quantity)->toBe(1)
        ->and($freshLoan->fully_returned_at)->toBeNull();

    // Verifikasi pencatatan buku besar inventaris
    $ledger = InventoryLedger::where('item_id', $item->id)
        ->where('type', 'return')
        ->latest('created_at')
        ->first();

    expect($ledger)->not->toBeNull()
        ->and($ledger->quantity)->toBe(1)
        ->and($ledger->user_id)->toBe($admin->id)
        ->and($ledger->stock_before)->toBe(5)
        ->and($ledger->stock_after)->toBe(6);

    // Verifikasi pencatatan lini masa pengembalian
    $returnRecord = AssetLoanReturn::where('asset_loan_id', $loan->id)->first();
    expect($returnRecord)->not->toBeNull()
        ->and($returnRecord->quantity)->toBe(1)
        ->and($returnRecord->ledger_id)->toBe($ledger->id);
});

test('it marks loan as fully returned when all outstanding items are returned', function () {
    $role = Role::firstOrCreate(['name' => 'super_admin']);
    $admin = User::factory()->create(['email' => 'admin@inalum.id']);
    $admin->assignRole($role);

    $item = Item::factory()->asset()->create(['current_stock' => 10]);

    $loan = AssetLoan::factory()->create([
        'item_id' => $item->id,
        'quantity' => 2,
        'returned_quantity' => 0,
        'borrowed_at' => now()->subDays(3),
        'fully_returned_at' => null,
    ]);

    $this->actingAs($admin);

    Livewire::test(ListAssetLoans::class)
        ->callTableAction('return_asset', $loan, data: [
            'return_quantity' => 2,
            'notes' => 'Seluruh unit dikembalikan lengkap.',
        ])
        ->assertHasNoTableActionErrors();

    $freshLoan = $loan->fresh();
    expect($freshLoan->returned_quantity)->toBe(2)
        ->and($freshLoan->fully_returned_at)->not->toBeNull()
        ->and($item->fresh()->current_stock)->toBe(12);
});

test('it hides return_asset action when loan is already settled', function () {
    $role = Role::firstOrCreate(['name' => 'super_admin']);
    $admin = User::factory()->create(['email' => 'admin@inalum.id']);
    $admin->assignRole($role);

    $settledLoan = AssetLoan::factory()->settled()->create();

    $this->actingAs($admin);

    Livewire::test(ListAssetLoans::class)
        ->assertTableActionHidden('return_asset', $settledLoan);
});

test('it hides return_asset action for users without admin privileges', function () {
    // User tanpa role inventory_admin atau super_admin
    $regularUser = User::factory()->create(['email' => 'auditor@inalum.id']);

    $activeLoan = AssetLoan::factory()->create([
        'quantity' => 2,
        'returned_quantity' => 0,
        'fully_returned_at' => null,
    ]);

    $this->actingAs($regularUser);

    Livewire::test(ListAssetLoans::class)
        ->assertTableActionHidden('return_asset', $activeLoan);
});
