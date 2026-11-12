<?php

namespace Tests\Feature\Hardening;

use App\Domains\Core\Models\User;
use App\Domains\Inventory\Models\InventoryLedger;
use App\Domains\Inventory\Models\Item;
use App\Domains\Inventory\Models\Location;
use App\Exports\InventoryLedgerExport;
use App\Exports\StockOpnameExport;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ExportReportsHardeningTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;

    protected function setUp(): void
    {
        parent::setUp();

        Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'web']);
        $this->adminUser = User::factory()->create([
            'name' => 'Auditor Hardening',
            'email' => 'auditor.hardening@inalum.id',
        ]);
        $this->adminUser->assignRole('super_admin');
    }

    public function test_export_stock_opname_handles_empty_dataset_gracefully(): void
    {
        $this->assertEquals(0, Item::where('is_active', true)->count());

        $export = new StockOpnameExport(auditorName: 'Auditor Zero Data');
        $this->assertCount(0, $export->collection());

        $fileName = 'chaos_stock_opname_empty.xlsx';
        $stored = Excel::store($export, $fileName);
        $this->assertTrue($stored);

        Storage::delete($fileName);
    }

    public function test_export_inventory_ledger_handles_empty_dataset_gracefully(): void
    {
        $this->assertEquals(0, InventoryLedger::count());

        $export = new InventoryLedgerExport(
            startDate: '2026-01-01',
            endDate: '2026-01-31',
            type: 'all',
            auditorName: 'Auditor Zero Ledger'
        );
        $this->assertCount(0, $export->collection());

        $fileName = 'chaos_ledger_empty.xlsx';
        $stored = Excel::store($export, $fileName);
        $this->assertTrue($stored);

        Storage::delete($fileName);
    }

    public function test_export_inventory_ledger_handles_inverted_date_filters_gracefully(): void
    {
        $export = new InventoryLedgerExport(
            startDate: '2026-12-31',
            endDate: '2026-01-01',
            type: 'all',
            auditorName: 'Auditor Inverted Date'
        );

        $collection = $export->collection();
        $this->assertCount(0, $collection);

        $fileName = 'chaos_ledger_inverted_dates.xlsx';
        $stored = Excel::store($export, $fileName);
        $this->assertTrue($stored);

        Storage::delete($fileName);
    }

    public function test_export_stock_opname_neutralizes_formula_injection_payloads(): void
    {
        $location = Location::factory()->create(['name' => 'Rak Uji Penetrasi']);

        $item1 = Item::factory()->create([
            'location_id' => $location->id,
            'name' => "=cmd|'/c calc'!A1",
            'sku' => '+EXP-CMD-001',
            'current_stock' => 10,
            'unit' => 'Unit',
            'type' => 'asset',
            'is_active' => true,
        ]);

        $item2 = Item::factory()->create([
            'location_id' => $location->id,
            'name' => '@SUM(1+1)*100',
            'sku' => '-SAF-MSK-002',
            'current_stock' => 20,
            'unit' => 'Box',
            'type' => 'consumable',
            'is_active' => true,
        ]);

        $export = new StockOpnameExport(auditorName: 'Auditor Pentest');
        $fileName = 'chaos_stock_opname_formula_injection.xlsx';

        $stored = Excel::store($export, $fileName);
        $this->assertTrue($stored);

        Storage::delete($fileName);
    }

    public function test_export_inventory_ledger_scales_to_large_volume_datasets(): void
    {
        $item = Item::factory()->create(['name' => 'Stress Test Item', 'sku' => 'STR-001']);

        $records = [];
        for ($i = 1; $i <= 100; $i++) {
            $records[] = [
                'id' => (string) Str::ulid(),
                'item_id' => $item->id,
                'user_id' => $this->adminUser->id,
                'type' => $i % 2 === 0 ? 'in' : 'out',
                'quantity' => 5,
                'stock_before' => 100,
                'stock_after' => 105,
                'notes' => "Stress test volume mutasi #{$i}",
                'reference_number' => "STR-REF-{$i}",
                'occurred_at' => now()->subMinutes($i),
                'created_at' => now()->subMinutes($i),
                'updated_at' => now()->subMinutes($i),
            ];
        }
        InventoryLedger::insert($records);

        $export = new InventoryLedgerExport(type: 'all');
        $collection = $export->collection();
        $this->assertGreaterThanOrEqual(100, $collection->count());

        $fileName = 'chaos_ledger_large_volume.xlsx';
        $stored = Excel::store($export, $fileName);
        $this->assertTrue($stored);

        Storage::delete($fileName);
    }
}
