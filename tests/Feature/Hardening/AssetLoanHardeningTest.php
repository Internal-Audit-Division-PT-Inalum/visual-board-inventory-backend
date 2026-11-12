<?php

namespace Tests\Feature\Hardening;

use App\Domains\Core\Models\User;
use App\Domains\HR\Models\Employee;
use App\Domains\Inventory\Exceptions\InvalidItemOperationException;
use App\Domains\Inventory\Exceptions\LoanExceedsOutstandingException;
use App\Domains\Inventory\Models\AssetLoan;
use App\Domains\Inventory\Models\Item;
use App\Filament\Resources\AssetLoans\Pages\ListAssetLoans;
use App\Services\Inventory\InventoryService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AssetLoanHardeningTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;

    protected Employee $employee;

    protected Item $assetItem;

    protected function setUp(): void
    {
        parent::setUp();

        Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'web']);

        $this->adminUser = User::factory()->create([
            'name' => 'Super Admin Inalum',
            'email' => 'admin@inalum.id',
        ]);
        $this->adminUser->assignRole('super_admin');

        $empUser = User::factory()->create(['name' => 'Budi Auditor']);
        $this->employee = Employee::factory()->create([
            'user_id' => $empUser->id,
            'name' => 'Budi Auditor',
            'namecode' => '102001',
        ]);

        $this->assetItem = Item::factory()->create([
            'name' => 'Laser Distance Meter Bosch',
            'sku' => 'AST-LSR-001',
            'type' => 'asset',
            'current_stock' => 5,
            'unit' => 'Unit',
            'is_active' => true,
        ]);
    }

    public function test_over_return_is_strictly_blocked_by_filament_modal_and_service(): void
    {
        $loan = AssetLoan::factory()->create([
            'item_id' => $this->assetItem->id,
            'employee_id' => $this->employee->id,
            'quantity' => 2,
            'returned_quantity' => 0,
            'borrowed_at' => now()->subDays(2),
            'fully_returned_at' => null,
        ]);

        $initialStock = $this->assetItem->current_stock;

        $this->actingAs($this->adminUser);

        Livewire::test(ListAssetLoans::class)
            ->callTableAction('return_asset', $loan, [
                'return_quantity' => 3,
                'notes' => 'Mencoba kembalikan lebih banyak dari pinjaman',
            ])
            ->assertHasTableActionErrors(['return_quantity']);

        $this->assertEquals($initialStock, $this->assetItem->fresh()->current_stock);
        $this->assertEquals(0, $loan->fresh()->returned_quantity);

        $this->expectException(LoanExceedsOutstandingException::class);

        app(InventoryService::class)->returnItem(
            identifier: (string) $this->assetItem->id,
            quantity: 5,
            userId: (string) $this->adminUser->id,
            notes: 'Direct attack to service layer',
            employeeId: (string) $this->employee->id
        );
    }

    public function test_zero_and_negative_quantities_are_rejected(): void
    {
        $loan = AssetLoan::factory()->create([
            'item_id' => $this->assetItem->id,
            'employee_id' => $this->employee->id,
            'quantity' => 3,
            'returned_quantity' => 0,
            'borrowed_at' => now()->subDay(),
            'fully_returned_at' => null,
        ]);

        $this->actingAs($this->adminUser);

        Livewire::test(ListAssetLoans::class)
            ->callTableAction('return_asset', $loan, [
                'return_quantity' => 0,
                'notes' => 'Return 0 quantity',
            ])
            ->assertHasTableActionErrors(['return_quantity']);

        $this->expectException(InvalidItemOperationException::class);

        app(InventoryService::class)->returnItem(
            identifier: (string) $this->assetItem->id,
            quantity: -2,
            userId: (string) $this->adminUser->id,
            notes: 'Negative return attack',
            employeeId: (string) $this->employee->id
        );
    }

    public function test_multi_step_partial_return_tracks_lifecycle_accurately(): void
    {
        $loan = AssetLoan::factory()->create([
            'item_id' => $this->assetItem->id,
            'employee_id' => $this->employee->id,
            'quantity' => 10,
            'returned_quantity' => 0,
            'borrowed_at' => now()->subDays(5),
            'fully_returned_at' => null,
        ]);

        $startStock = (int) $this->assetItem->current_stock;
        $this->actingAs($this->adminUser);

        Livewire::test(ListAssetLoans::class)
            ->callTableAction('return_asset', $loan, [
                'return_quantity' => 3,
                'notes' => 'Pengembalian tahap 1: 3 unit aman',
            ])
            ->assertHasNoTableActionErrors();

        $loan->refresh();
        $this->assertEquals(3, $loan->returned_quantity);
        $this->assertNull($loan->fully_returned_at);
        $this->assertEquals($startStock + 3, $this->assetItem->fresh()->current_stock);

        Livewire::test(ListAssetLoans::class)
            ->callTableAction('return_asset', $loan, [
                'return_quantity' => 4,
                'notes' => 'Pengembalian tahap 2: 4 unit aman',
            ])
            ->assertHasNoTableActionErrors();

        $loan->refresh();
        $this->assertEquals(7, $loan->returned_quantity);
        $this->assertNull($loan->fully_returned_at);
        $this->assertEquals($startStock + 7, $this->assetItem->fresh()->current_stock);

        Livewire::test(ListAssetLoans::class)
            ->callTableAction('return_asset', $loan, [
                'return_quantity' => 3,
                'notes' => 'Pengembalian tahap 3: pelunasan 3 unit terakhir',
            ])
            ->assertHasNoTableActionErrors();

        $loan->refresh();
        $this->assertEquals(10, $loan->returned_quantity);
        $this->assertNotNull($loan->fully_returned_at);
        $this->assertEquals($startStock + 10, $this->assetItem->fresh()->current_stock);

        Livewire::test(ListAssetLoans::class)
            ->assertTableActionHidden('return_asset', $loan);
    }

    public function test_soft_deleted_item_can_still_be_safely_returned_without_crashing(): void
    {
        $loan = AssetLoan::factory()->create([
            'item_id' => $this->assetItem->id,
            'employee_id' => $this->employee->id,
            'quantity' => 2,
            'returned_quantity' => 0,
            'borrowed_at' => now()->subDay(),
            'fully_returned_at' => null,
        ]);

        $this->assetItem->delete();
        $this->assertTrue($this->assetItem->fresh()->trashed());

        $this->actingAs($this->adminUser);

        Livewire::test(ListAssetLoans::class)
            ->callTableAction('return_asset', $loan, [
                'return_quantity' => 2,
                'notes' => 'Mengembalikan aset yang sudah soft deleted di sistem',
            ])
            ->assertHasNoTableActionErrors();

        $loan->refresh();
        $this->assertEquals(2, $loan->returned_quantity);
        $this->assertNotNull($loan->fully_returned_at);
    }
}
