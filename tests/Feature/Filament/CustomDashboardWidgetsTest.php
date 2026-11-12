<?php

namespace Tests\Feature\Filament;

use App\Domains\Core\Models\User;
use App\Domains\Inventory\Models\AssetLoan;
use App\Domains\Inventory\Models\Item;
use App\Domains\Inventory\Models\Location;
use App\Filament\Pages\CustomDashboard;
use App\Filament\Resources\Items\Pages\ListItems;
use App\Filament\Widgets\CriticalStockAlertTableWidget;
use App\Filament\Widgets\InventoryExecutiveStatsWidget;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'web']);
});

test('inventory executive stats widget displays accurate database metrics and links', function () {
    $admin = User::factory()->create();
    $admin->assignRole('super_admin');

    Item::factory()->create([
        'is_active' => true,
        'current_stock' => 1,
        'minimum_stock' => 5,
    ]);

    Item::factory()->create([
        'is_active' => true,
        'current_stock' => 10,
        'minimum_stock' => 5,
    ]);

    Item::factory()->create([
        'is_active' => false,
        'current_stock' => 0,
        'minimum_stock' => 5,
    ]);

    AssetLoan::factory()->create([
        'quantity' => 5,
        'returned_quantity' => 2,
        'borrowed_at' => now()->subDays(2),
        'fully_returned_at' => null,
    ]);

    AssetLoan::factory()->overdue()->create([
        'quantity' => 4,
        'returned_quantity' => 1,
        'borrowed_at' => now()->subDays(10),
        'fully_returned_at' => null,
    ]);

    AssetLoan::factory()->settled()->create();

    $this->actingAs($admin);

    Livewire::test(InventoryExecutiveStatsWidget::class)
        ->assertSuccessful()
        ->assertSee('Total Katalog Barang')
        ->assertSee('2')
        ->assertSee('Barang Stok Kritis')
        ->assertSee('1')
        ->assertSee('Aset Sedang Dipinjam')
        ->assertSee('6 Unit')
        ->assertSee('Pinjaman Overdue (>= 7 Hari)')
        ->assertSee('1 Transaksi');
});

test('critical stock alert table widget displays low stock items and excludes safe or inactive items', function () {
    $admin = User::factory()->create();
    $admin->assignRole('super_admin');

    $location = Location::factory()->create(['name' => 'Rak A-01']);

    $criticalItemA = Item::factory()->create([
        'name' => 'Kabel UTP Cat 6',
        'location_id' => $location->id,
        'current_stock' => 1,
        'minimum_stock' => 10,
        'is_active' => true,
    ]);

    $criticalItemB = Item::factory()->create([
        'name' => 'RJ-45 Modular Plug',
        'location_id' => $location->id,
        'current_stock' => 5,
        'minimum_stock' => 20,
        'is_active' => true,
    ]);

    $safeItem = Item::factory()->create([
        'name' => 'Kertas A4 Sinar Dunia',
        'location_id' => $location->id,
        'current_stock' => 50,
        'minimum_stock' => 10,
        'is_active' => true,
    ]);

    $inactiveCriticalItem = Item::factory()->create([
        'name' => 'Barang Usang Non-Aktif',
        'location_id' => $location->id,
        'current_stock' => 0,
        'minimum_stock' => 5,
        'is_active' => false,
    ]);

    $this->actingAs($admin);

    Livewire::test(CriticalStockAlertTableWidget::class)
        ->assertSuccessful()
        ->assertCanSeeTableRecords([$criticalItemA, $criticalItemB])
        ->assertCanNotSeeTableRecords([$safeItem, $inactiveCriticalItem]);
});

test('critical stock alert table widget allows authorized admin to adjust stock with audit trail', function () {
    $admin = User::factory()->create();
    $admin->assignRole('super_admin');

    $item = Item::factory()->create([
        'name' => 'Harddisk Eksternal 2TB',
        'current_stock' => 1,
        'minimum_stock' => 5,
        'unit' => 'Unit',
        'is_active' => true,
    ]);

    $this->actingAs($admin);

    Livewire::test(CriticalStockAlertTableWidget::class)
        ->callTableAction('adjust_stock', $item, [
            'counted_quantity' => 12,
            'reason' => 'Stock opname kuartal 4 hasil verifikasi fisik',
        ])
        ->assertHasNoTableActionErrors();

    $item->refresh();
    expect($item->current_stock)->toBe(12);

    $this->assertDatabaseHas('inventory_ledgers', [
        'item_id' => $item->id,
        'type' => 'adjustment',
        'quantity' => 11,
        'stock_before' => 1,
        'stock_after' => 12,
    ]);
});

test('items table can filter by critical stock using ternary filter', function () {
    $admin = User::factory()->create();
    $admin->assignRole('super_admin');

    $criticalItem = Item::factory()->create([
        'current_stock' => 2,
        'minimum_stock' => 10,
        'is_active' => true,
    ]);

    $safeItem = Item::factory()->create([
        'current_stock' => 30,
        'minimum_stock' => 5,
        'is_active' => true,
    ]);

    $this->actingAs($admin);

    Livewire::test(ListItems::class)
        ->filterTable('critical_stock', '1')
        ->assertCanSeeTableRecords([$criticalItem])
        ->assertCanNotSeeTableRecords([$safeItem]);
});

test('custom dashboard page renders successfully with all registered widgets', function () {
    $admin = User::factory()->create();
    $admin->assignRole('super_admin');

    $this->actingAs($admin);

    $dashboard = new CustomDashboard;
    $widgets = $dashboard->getWidgets();

    expect($widgets)->toContain(InventoryExecutiveStatsWidget::class)
        ->and($widgets)->toContain(CriticalStockAlertTableWidget::class);

    Livewire::test(CustomDashboard::class)
        ->assertSuccessful();
});
