<?php

namespace Tests\Feature\Hardening;

use App\Domains\Core\Models\User;
use App\Domains\Inventory\Models\Item;
use App\Domains\Inventory\Models\Location;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class PrintStickersSecurityTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;

    protected function setUp(): void
    {
        parent::setUp();

        Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'web']);
        $this->adminUser = User::factory()->create([
            'name' => 'Admin Keamanan',
            'email' => 'admin.keamanan@inalum.id',
        ]);
        $this->adminUser->assignRole('super_admin');
    }

    public function test_unauthenticated_request_is_strictly_redirected_to_login(): void
    {
        $responseLocation = $this->get('/admin/print/locations?ids=some-id');
        $responseLocation->assertRedirect('/login');

        $responseItem = $this->get('/admin/print/items?ids=some-id');
        $responseItem->assertRedirect('/login');
    }

    public function test_malformed_and_non_existent_ids_are_handled_gracefully(): void
    {
        $this->actingAs($this->adminUser);

        $responseLocation = $this->get('/admin/print/locations?ids=non-existent-ulid-1,fake-id-2');
        $responseLocation->assertNotFound();

        $responseItem = $this->get('/admin/print/items?ids=invalid-item-ulid');
        $responseItem->assertNotFound();
    }

    public function test_database_strictly_enforces_location_integrity_for_items(): void
    {
        $this->expectException(QueryException::class);

        Item::factory()->create([
            'location_id' => null,
            'name' => 'Barang Tanpa Rak',
            'sku' => 'AST-INV-ERR',
        ]);
    }

    public function test_bulk_printing_handles_dozens_of_items_efficiently(): void
    {
        $location = Location::factory()->create(['name' => 'Rak Bulk Test']);
        $items = Item::factory()->count(25)->create([
            'location_id' => $location->id,
            'type' => 'asset',
            'is_active' => true,
        ]);

        $idsString = $items->pluck('id')->join(',');

        $this->actingAs($this->adminUser);

        $response = $this->get("/admin/print/items?ids={$idsString}");
        $response->assertOk();

        foreach ($items->take(5) as $item) {
            $response->assertSee($item->sku);
        }
    }
}
