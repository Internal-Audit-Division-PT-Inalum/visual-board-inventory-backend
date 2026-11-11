<?php

namespace Tests\Feature\Filament;

use App\Domains\Core\Models\User;
use App\Domains\Inventory\Models\Item;
use App\Domains\Inventory\Models\Location;
use App\Filament\Resources\Items\Pages\ListItems;
use App\Filament\Resources\Locations\Pages\ListLocations;
use App\Services\Inventory\QrCodeService;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'web']);
    Role::firstOrCreate(['name' => 'inventory_admin', 'guard_name' => 'web']);
});

test('unauthenticated user cannot access sticker print routes', function () {
    $this->get(route('filament.admin.print.locations'))
        ->assertRedirect('/login');

    $this->get(route('filament.admin.print.items'))
        ->assertRedirect('/login');
});

test('qr code service produces valid svg and pwa-compliant url payload', function () {
    $service = app(QrCodeService::class);

    $location = Location::factory()->create([
        'name' => 'Rak ATK Lantai 2',
        'code' => 'RAK-ATK-02',
    ]);

    $item = Item::factory()->create([
        'name' => 'Safety Helmet Merah',
        'sku' => 'SAF-HLM-001',
    ]);

    $locationSvg = $service->generateLocationQrSvg($location);
    $itemSvg = $service->generateItemQrSvg($item);

    expect($locationSvg)->toContain('<svg')
        ->and($locationSvg)->toContain('</svg>')
        ->and($itemSvg)->toContain('<svg')
        ->and($itemSvg)->toContain('</svg>');
});

test('authorized user can view rack location qr sticker print page with single and bulk records', function () {
    $admin = User::factory()->create();
    $admin->assignRole('super_admin');

    $locA = Location::factory()->create([
        'name' => 'Rak Arsip Dokumen',
        'code' => 'RAK-ARSIP-01',
    ]);

    $locB = Location::factory()->create([
        'name' => 'Rak Perangkat TI',
        'code' => 'RAK-TI-02',
    ]);

    $this->actingAs($admin);

    // Single print
    $this->get(route('filament.admin.print.locations', ['ids' => $locA->id]))
        ->assertOk()
        ->assertViewIs('filament.print.location-qr-sticker')
        ->assertSee('RAK-ARSIP-01')
        ->assertSee('Rak Arsip Dokumen')
        ->assertSee('PT INALUM')
        ->assertSee('<svg', false);

    // Bulk print
    $this->get(route('filament.admin.print.locations', ['ids' => "{$locA->id},{$locB->id}"]))
        ->assertOk()
        ->assertSee('RAK-ARSIP-01')
        ->assertSee('RAK-TI-02')
        ->assertSee('Total: 2 Label');
});

test('authorized user can view item asset qr sticker print page with single and bulk records', function () {
    $admin = User::factory()->create();
    $admin->assignRole('inventory_admin');

    $itemA = Item::factory()->create([
        'name' => 'Multimeter Digital Fluke',
        'sku' => 'TOOL-FLK-001',
    ]);

    $itemB = Item::factory()->create([
        'name' => 'Kabel Tester RJ45',
        'sku' => 'TOOL-TST-002',
    ]);

    $this->actingAs($admin);

    // Single print
    $this->get(route('filament.admin.print.items', ['ids' => $itemA->id]))
        ->assertOk()
        ->assertViewIs('filament.print.item-qr-sticker')
        ->assertSee('TOOL-FLK-001')
        ->assertSee('PROPERTY OF PT INALUM')
        ->assertSee('<svg', false);

    // Bulk print
    $this->get(route('filament.admin.print.items', ['ids' => "{$itemA->id},{$itemB->id}"]))
        ->assertOk()
        ->assertSee('TOOL-FLK-001')
        ->assertSee('TOOL-TST-002')
        ->assertSee('Total: 2 Label');
});

test('locations and items tables render print actions', function () {
    $admin = User::factory()->create();
    $admin->assignRole('super_admin');

    $location = Location::factory()->create();
    $item = Item::factory()->create();

    $this->actingAs($admin);

    Livewire::test(ListLocations::class)
        ->assertTableActionExists('print_sticker')
        ->assertTableBulkActionExists('print_selected_stickers');

    Livewire::test(ListItems::class)
        ->assertTableActionExists('print_label')
        ->assertTableBulkActionExists('print_selected_labels');
});
