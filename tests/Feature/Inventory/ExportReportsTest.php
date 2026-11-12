<?php

namespace Tests\Feature\Inventory;

use App\Domains\Core\Models\User;
use App\Domains\Inventory\Models\InventoryLedger;
use App\Domains\Inventory\Models\Item;
use App\Domains\Inventory\Models\Location;
use App\Exports\InventoryLedgerExport;
use App\Exports\StockOpnameExport;
use App\Filament\Resources\InventoryLedgers\Pages\ListInventoryLedgers;
use App\Filament\Resources\Items\Pages\ListItems;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Livewire\Livewire;
use Maatwebsite\Excel\Facades\Excel;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ExportReportsTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;

    protected function setUp(): void
    {
        parent::setUp();

        Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'web']);

        $this->adminUser = User::factory()->create([
            'name' => 'Auditor Senior Inalum',
            'email' => 'auditor.lead@inalum.id',
        ]);
        $this->adminUser->assignRole('super_admin');
    }

    public function test_stock_opname_export_collects_items_and_formats_properly(): void
    {
        $location = Location::factory()->create(['name' => 'Rak Gudang A-01']);

        Item::factory()->create([
            'location_id' => $location->id,
            'name' => 'Helm Safety MSA V-Gard',
            'sku' => 'SAF-HLM-001',
            'type' => 'asset',
            'unit' => 'Unit',
            'current_stock' => 25,
            'is_active' => true,
        ]);

        Item::factory()->create([
            'location_id' => $location->id,
            'name' => 'Sarung Tangan Karet Nitrile',
            'sku' => 'SAF-GLV-002',
            'type' => 'consumable',
            'unit' => 'Pasang',
            'current_stock' => 150,
            'is_active' => true,
        ]);

        $export = new StockOpnameExport(auditorName: 'Auditor Senior Inalum');
        $collection = $export->collection();

        $this->assertCount(2, $collection);
        $this->assertEquals('A5', $export->startCell());
        $this->assertEquals('Stock Opname Fisik', $export->title());

        $headings = $export->headings();
        $this->assertContains('KODE SKU', $headings);
        $this->assertContains('HITUNG FISIK (OPNAME)', $headings);
        $this->assertContains('SELISIH', $headings);

        $firstItem = $collection->first();
        $mapped = $export->map($firstItem);

        $this->assertEquals(1, $mapped[0]); // No
        $this->assertEquals($firstItem->sku, $mapped[1]);
        $this->assertEquals($firstItem->name, $mapped[2]);
        $this->assertStringContainsString('H6-G6', $mapped[8]);
    }

    public function test_inventory_ledger_export_filters_by_date_and_type(): void
    {
        $item = Item::factory()->create(['name' => 'Kertas A4 Sinar Dunia', 'sku' => 'ATK-KRT-001']);

        InventoryLedger::factory()->create([
            'item_id' => $item->id,
            'user_id' => $this->adminUser->id,
            'type' => 'in',
            'quantity' => 10,
            'stock_before' => 0,
            'stock_after' => 10,
            'occurred_at' => now()->subDay(),
        ]);

        InventoryLedger::factory()->create([
            'item_id' => $item->id,
            'user_id' => $this->adminUser->id,
            'type' => 'borrow',
            'quantity' => 2,
            'stock_before' => 10,
            'stock_after' => 8,
            'occurred_at' => now(),
        ]);

        InventoryLedger::factory()->create([
            'item_id' => $item->id,
            'user_id' => $this->adminUser->id,
            'type' => 'adjustment',
            'quantity' => 5,
            'stock_before' => 10,
            'stock_after' => 15,
            'occurred_at' => now()->subDays(10),
        ]);

        $exportBorrow = new InventoryLedgerExport(type: 'borrow');
        $this->assertCount(1, $exportBorrow->collection());
        $this->assertEquals('borrow', $exportBorrow->collection()->first()?->type);

        $exportRecent = new InventoryLedgerExport(
            startDate: now()->subDays(2)->format('Y-m-d'),
            endDate: now()->addDay()->format('Y-m-d'),
        );
        $this->assertCount(2, $exportRecent->collection());

        $mapped = $exportBorrow->map($exportBorrow->collection()->first());
        $this->assertEquals(1, $mapped[0]);
        $this->assertEquals('Peminjaman (Borrow)', $mapped[5]);
        $this->assertEquals(2, $mapped[6]);
    }

    public function test_admin_can_download_stock_opname_from_items_page(): void
    {
        Carbon::setTestNow(now());
        Excel::fake();

        $this->actingAs($this->adminUser);

        Livewire::test(ListItems::class)
            ->callAction('export_stock_opname');

        $expectedFileName = 'Berita_Acara_Stock_Opname_' . now()->format('Ymd_His') . '.xlsx';

        Excel::assertDownloaded($expectedFileName, function (StockOpnameExport $export) {
            return true;
        });

        Carbon::setTestNow();
    }

    public function test_admin_can_download_ledger_from_inventory_ledgers_page(): void
    {
        Carbon::setTestNow(now());
        Excel::fake();

        $this->actingAs($this->adminUser);

        Livewire::test(ListInventoryLedgers::class)
            ->callAction('export_ledger', [
                'start_date' => now()->startOfMonth()->format('Y-m-d'),
                'end_date' => now()->format('Y-m-d'),
                'type' => 'all',
            ]);

        $expectedFileName = 'Buku_Besar_Inventaris_' . now()->format('Ymd_His') . '.xlsx';

        Excel::assertDownloaded($expectedFileName, function (InventoryLedgerExport $export) {
            return true;
        });

        Carbon::setTestNow();
    }
}
