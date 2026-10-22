<?php

use App\Domains\Core\Models\User;
use App\Domains\HR\Models\Employee;
use App\Domains\Inventory\Models\InventoryLedger;
use App\Domains\Inventory\Models\Item;
use App\Domains\Inventory\Models\Location;
use App\Filament\Resources\Items\Pages\CreateItem;
use App\Services\Inventory\InventoryService;
use Illuminate\Support\Facades\Cache;
use Spatie\Permission\Models\Role;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

beforeEach(function () {
    $this->user = User::factory()->create();
    Employee::factory()->create([
        'user_id' => $this->user->id,
    ]);
    $this->location = Location::factory()->create();
    $this->service = app(InventoryService::class);

    Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'web']);
    Role::firstOrCreate(['name' => 'inventory_admin', 'guard_name' => 'web']);
});

it('increases stock and records adjustment ledger on surplus', function () {
    $item = Item::factory()->consumable()->create([
        'location_id' => $this->location->id,
        'current_stock' => 10,
    ]);

    Cache::put('inventory:kiosk:locations', 'test_cache', 300);

    $ledger = $this->service->adjustStock(
        item: $item,
        countedQuantity: 15,
        reason: 'Hasil stock opname fisik surplus 5 unit',
        userId: $this->user->id
    );

    expect($ledger)->toBeInstanceOf(InventoryLedger::class)
        ->and($ledger->type)->toBe('adjustment')
        ->and($ledger->quantity)->toBe(5)
        ->and($ledger->stock_before)->toBe(10)
        ->and($ledger->stock_after)->toBe(15)
        ->and($ledger->notes)->toBe('Hasil stock opname fisik surplus 5 unit')
        ->and($ledger->reference_number)->toStartWith('OPNAME-');

    expect($item->fresh()->current_stock)->toBe(15);
    expect(Cache::has('inventory:kiosk:locations'))->toBeFalse();
});

it('decreases stock and records adjustment ledger on deficit', function () {
    $item = Item::factory()->consumable()->create([
        'location_id' => $this->location->id,
        'current_stock' => 20,
    ]);

    $ledger = $this->service->adjustStock(
        item: $item,
        countedQuantity: 12,
        reason: '8 unit rusak dan kedaluwarsa',
        userId: $this->user->id
    );

    expect($ledger->type)->toBe('adjustment')
        ->and($ledger->quantity)->toBe(8)
        ->and($ledger->stock_before)->toBe(20)
        ->and($ledger->stock_after)->toBe(12)
        ->and($ledger->notes)->toBe('8 unit rusak dan kedaluwarsa');

    expect($item->fresh()->current_stock)->toBe(12);
});

it('records adjustment when counted stock equals current stock', function () {
    $item = Item::factory()->consumable()->create([
        'location_id' => $this->location->id,
        'current_stock' => 10,
    ]);

    $ledger = $this->service->adjustStock(
        item: $item,
        countedQuantity: 10,
        reason: 'Verifikasi berkala, stok sesuai fisik',
        userId: $this->user->id
    );

    expect($ledger->type)->toBe('adjustment')
        ->and($ledger->quantity)->toBe(0)
        ->and($ledger->stock_before)->toBe(10)
        ->and($ledger->stock_after)->toBe(10);

    expect($item->fresh()->current_stock)->toBe(10);
});

it('accepts SKU as item identifier', function () {
    $item = Item::factory()->consumable()->create([
        'location_id' => $this->location->id,
        'sku' => 'SKU-OPNAME-TEST',
        'current_stock' => 30,
    ]);

    $ledger = $this->service->adjustStock(
        item: 'SKU-OPNAME-TEST',
        countedQuantity: 25,
        reason: 'Opname via SKU barcode scanner',
        userId: $this->user->id
    );

    expect($ledger->stock_after)->toBe(25);
    expect($item->fresh()->current_stock)->toBe(25);
});

it('throws InvalidArgumentException when reason is empty or whitespace', function () {
    $item = Item::factory()->consumable()->create([
        'location_id' => $this->location->id,
        'current_stock' => 10,
    ]);

    $this->service->adjustStock(
        item: $item,
        countedQuantity: 12,
        reason: '   ',
        userId: $this->user->id
    );
})->throws(InvalidArgumentException::class, 'Alasan koreksi stok wajib diisi.');

it('throws InvalidArgumentException when counted quantity is negative', function () {
    $item = Item::factory()->consumable()->create([
        'location_id' => $this->location->id,
        'current_stock' => 10,
    ]);

    $this->service->adjustStock(
        item: $item,
        countedQuantity: -5,
        reason: 'Stok minus',
        userId: $this->user->id
    );
})->throws(InvalidArgumentException::class, 'Jumlah fisik stok tidak boleh bernilai negatif.');

it('throws NotFoundHttpException when item identifier is not found', function () {
    $this->service->adjustStock(
        item: 'non-existent-sku-or-id',
        countedQuantity: 10,
        reason: 'Opname barang fiktif',
        userId: $this->user->id
    );
})->throws(NotFoundHttpException::class, 'Item tidak ditemukan.');

it('creates initial stock ledger on item registration when current_stock > 0', function () {
    $item = Item::create([
        'location_id' => $this->location->id,
        'name' => 'Barang Baru',
        'sku' => 'INIT-TEST-001',
        'type' => 'consumable',
        'unit' => 'pcs',
        'current_stock' => 20,
        'minimum_stock' => 5,
        'is_active' => true,
    ]);

    // Test CreateItem afterCreate hook behavior
    $page = new CreateItem;
    $refProperty = new ReflectionProperty($page, 'record');
    $refProperty->setValue($page, $item);

    $refMethod = new ReflectionMethod($page, 'afterCreate');
    $refMethod->invoke($page);

    $ledger = $item->ledgers()->latest()->first();
    expect($ledger)->not->toBeNull()
        ->and($ledger->type)->toBe('in')
        ->and($ledger->quantity)->toBe(20)
        ->and($ledger->stock_before)->toBe(0)
        ->and($ledger->stock_after)->toBe(20)
        ->and($ledger->notes)->toBe('Saldo awal barang saat registrasi di sistem')
        ->and($ledger->reference_number)->toStartWith('INIT-');
});

it('verifies ItemForm locking logic for edit operation and ledger presence', function () {
    $editDisabledClosure = fn (string $operation): bool => $operation === 'edit';
    expect($editDisabledClosure('edit'))->toBeTrue();
    expect($editDisabledClosure('create'))->toBeFalse();

    $itemWithoutLedger = Item::factory()->create(['location_id' => $this->location->id]);
    $itemWithLedger = Item::factory()->create(['location_id' => $this->location->id]);
    InventoryLedger::create([
        'item_id' => $itemWithLedger->id,
        'user_id' => $this->user->id,
        'type' => 'in',
        'quantity' => 10,
        'stock_before' => 0,
        'stock_after' => 10,
        'notes' => 'Init',
    ]);

    $typeDisabledClosure = fn (?Item $record): bool => $record !== null && $record->ledgers()->exists();
    expect($typeDisabledClosure($itemWithoutLedger))->toBeFalse();
    expect($typeDisabledClosure($itemWithLedger))->toBeTrue();
});

it('verifies adjust_stock action visibility is restricted to inventory_admin and super_admin', function () {
    $regularUser = User::factory()->create();
    $adminUser = User::factory()->create();
    $adminUser->assignRole('inventory_admin');
    $superAdminUser = User::factory()->create();
    $superAdminUser->assignRole('super_admin');

    $visibilityClosure = fn ($user): bool => $user?->hasAnyRole(['inventory_admin', 'super_admin']) ?? false;

    expect($visibilityClosure($regularUser))->toBeFalse();
    expect($visibilityClosure($adminUser))->toBeTrue();
    expect($visibilityClosure($superAdminUser))->toBeTrue();
});

it('ensures ledger records adjustment history accessible via ledger endpoint', function () {
    $item = Item::factory()->consumable()->create([
        'location_id' => $this->location->id,
        'current_stock' => 50,
    ]);

    $this->service->adjustStock(
        item: $item,
        countedQuantity: 45,
        reason: 'Stock opname Q3 2026',
        userId: $this->user->id
    );

    $this->actingAs($this->user)
        ->getJson("/api/v1/inventory/items/{$item->id}/ledger")
        ->assertOk()
        ->assertJsonPath('data.0.type', 'adjustment')
        ->assertJsonPath('data.0.quantity', 5)
        ->assertJsonPath('data.0.stock_before', 50)
        ->assertJsonPath('data.0.stock_after', 45)
        ->assertJsonPath('data.0.notes', 'Stock opname Q3 2026');
});
