<?php

namespace Tests\Feature\Hardening;

use App\Domains\Core\Models\User;
use App\Domains\HR\Models\Employee;
use App\Domains\Inventory\Models\AssetLoan;
use App\Domains\Inventory\Models\Item;
use App\Filament\Resources\AssetLoans\Pages\ListAssetLoans;
use App\Filament\Resources\Items\Pages\ListItems;
use App\Filament\Widgets\CriticalStockAlertTableWidget;
use App\Filament\Widgets\InventoryExecutiveStatsWidget;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class TelemetryThresholdHardeningTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;

    protected Employee $employee;

    protected function setUp(): void
    {
        parent::setUp();

        Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'web']);

        $this->adminUser = User::factory()->create([
            'name' => 'Admin Telemetri',
            'email' => 'admin.telemetri@inalum.id',
        ]);
        $this->adminUser->assignRole('super_admin');

        $this->employee = Employee::factory()->create(['namecode' => '109999']);
    }

    public function test_overdue_aging_boundary_is_strictly_applied_at_seven_days(): void
    {
        $item = Item::factory()->create(['type' => 'asset']);

        $loanNormal = AssetLoan::factory()->create([
            'item_id' => $item->id,
            'employee_id' => $this->employee->id,
            'quantity' => 1,
            'returned_quantity' => 0,
            'borrowed_at' => now()->subDays(6)->subHours(23),
            'fully_returned_at' => null,
        ]);

        $loanOverdue = AssetLoan::factory()->create([
            'item_id' => $item->id,
            'employee_id' => $this->employee->id,
            'quantity' => 1,
            'returned_quantity' => 0,
            'borrowed_at' => now()->subDays(7)->subHour(),
            'fully_returned_at' => null,
        ]);

        $this->actingAs($this->adminUser);

        Livewire::test(ListAssetLoans::class)
            ->set('tableFilters.status.value', 'overdue')
            ->assertCanSeeTableRecords([$loanOverdue])
            ->assertCanNotSeeTableRecords([$loanNormal]);

        Livewire::test(InventoryExecutiveStatsWidget::class)
            ->assertSee('Pinjaman Overdue (&gt;= 7 Hari)', false)
            ->assertSee('1 Transaksi');
    }

    public function test_critical_stock_boundary_threshold_comparison(): void
    {
        $itemCriticalExact = Item::factory()->create([
            'name' => 'Kabel UTP Cat6 Belden',
            'current_stock' => 5,
            'minimum_stock' => 5,
            'is_active' => true,
        ]);

        $itemSafe = Item::factory()->create([
            'name' => 'Konektor RJ45 AMP',
            'current_stock' => 6,
            'minimum_stock' => 5,
            'is_active' => true,
        ]);

        $itemCriticalZero = Item::factory()->create([
            'name' => 'Tang Crimping RJ45',
            'current_stock' => 0,
            'minimum_stock' => 5,
            'is_active' => true,
        ]);

        $this->actingAs($this->adminUser);

        Livewire::test(CriticalStockAlertTableWidget::class)
            ->assertCanSeeTableRecords([$itemCriticalExact, $itemCriticalZero])
            ->assertCanNotSeeTableRecords([$itemSafe]);

        Livewire::test(ListItems::class)
            ->set('tableFilters.critical_stock.value', '1')
            ->assertCanSeeTableRecords([$itemCriticalExact, $itemCriticalZero])
            ->assertCanNotSeeTableRecords([$itemSafe]);
    }
}
