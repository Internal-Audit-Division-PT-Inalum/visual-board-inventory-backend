<?php

namespace Tests\Feature\Hardening;

use App\Domains\Core\Models\User;
use App\Domains\HR\Models\Department;
use App\Domains\HR\Models\Employee;
use App\Domains\HR\Models\EmployeeAttendance;
use App\Domains\HR\Models\Position;
use App\Domains\Inventory\Models\AssetLoan;
use App\Domains\Inventory\Models\InventoryLedger;
use App\Domains\Inventory\Models\Item;
use App\Domains\Inventory\Models\Location;
use App\Domains\Portal\Models\Bulletin;
use App\Domains\Portal\Models\QuickLink;
use App\Domains\VisualBoard\Models\Abnormality;
use App\Domains\VisualBoard\Models\FiveREvaluation;
use App\Domains\VisualBoard\Models\GeneralDocument;
use App\Domains\VisualBoard\Models\InspectionCriteria;
use App\Domains\VisualBoard\Models\MasterWorkstationCriteria;
use App\Domains\VisualBoard\Models\MonthlySchedule;
use App\Domains\VisualBoard\Models\TrendAbnormality;
use App\Domains\VisualBoard\Models\Workstation;
use App\Domains\VisualBoard\Models\Zone;
use App\Filament\Resources\InventoryLedgers\InventoryLedgerResource;
use App\Filament\Resources\InventoryLedgers\Pages\ListInventoryLedgers;
use App\Filament\Resources\Items\Pages\ListItems;
use App\Filament\Resources\Users\Pages\EditUser;
use App\Filament\Resources\Users\UserResource;
use App\Filament\Widgets\PendingMonthlySchedulesTable;
use BezhanSalleh\FilamentShield\Resources\Roles\RoleResource;
use DomainException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class RBACAndImmutabilityHardeningTest extends TestCase
{
    use RefreshDatabase;

    protected User $superAdmin;

    protected User $supervisorA;

    protected User $supervisorB;

    protected Zone $zoneA;

    protected Zone $zoneB;

    protected function setUp(): void
    {
        parent::setUp();

        Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'auditor', 'guard_name' => 'web']);

        $this->superAdmin = User::factory()->create(['name' => 'Super Admin']);
        $this->superAdmin->assignRole('super_admin');

        $this->supervisorA = User::factory()->create(['name' => 'Pengawas Zona A', 'email' => 'spv.a@inalum.id']);
        $this->supervisorA->assignRole('auditor');
        $employeeA = Employee::factory()->create(['user_id' => $this->supervisorA->id]);

        $this->supervisorB = User::factory()->create(['name' => 'Pengawas Zona B', 'email' => 'spv.b@inalum.id']);
        $this->supervisorB->assignRole('auditor');
        $employeeB = Employee::factory()->create(['user_id' => $this->supervisorB->id]);

        $this->zoneA = Zone::factory()->create([
            'name' => 'Zona A - Smelter',
            'pic_utama_id' => $employeeA->id,
        ]);
        $this->zoneB = Zone::factory()->create([
            'name' => 'Zona B - Dermaga',
            'pic_utama_id' => $employeeB->id,
        ]);
    }

    public function test_inventory_ledger_is_strictly_immutable_and_cannot_be_created_edited_or_deleted_via_resource(): void
    {
        $ledger = InventoryLedger::factory()->create();

        $this->assertFalse(InventoryLedgerResource::canCreate());
        $this->assertFalse(InventoryLedgerResource::canEdit($ledger));
        $this->assertFalse(InventoryLedgerResource::canDelete($ledger));

        $this->actingAs($this->superAdmin);

        $listComponent = Livewire::test(ListInventoryLedgers::class);
        $this->assertNull($listComponent->instance()->getAction('create'));
    }

    public function test_stock_adjustment_requires_mandatory_audit_reason_and_user(): void
    {
        $item = Item::factory()->create([
            'name' => 'Safety Boots Red Wing',
            'current_stock' => 10,
            'unit' => 'Pasang',
        ]);

        $this->actingAs($this->superAdmin);

        Livewire::test(ListItems::class)
            ->callTableAction('adjust_stock', $item, [
                'counted_quantity' => 15,
                'reason' => '',
            ])
            ->assertHasTableActionErrors(['reason']);

        $this->assertEquals(10, $item->fresh()->current_stock);

        Livewire::test(ListItems::class)
            ->callTableAction('adjust_stock', $item, [
                'counted_quantity' => 15,
                'reason' => 'Stock opname kuartal 4 internal audit',
            ])
            ->assertHasNoTableActionErrors();

        $this->assertEquals(15, $item->fresh()->current_stock);

        $adjustmentLedger = InventoryLedger::where('item_id', $item->id)
            ->where('type', 'adjustment')
            ->latest('id')
            ->first();

        $this->assertNotNull($adjustmentLedger);
        $this->assertEquals(5, $adjustmentLedger->quantity);
        $this->assertEquals(10, $adjustmentLedger->stock_before);
        $this->assertEquals(15, $adjustmentLedger->stock_after);
        $this->assertEquals('Stock opname kuartal 4 internal audit', $adjustmentLedger->notes);
        $this->assertEquals($this->superAdmin->id, $adjustmentLedger->user_id);
    }

    public function test_cross_zone_approval_isolation_in_pending_widgets(): void
    {
        $scheduleA = MonthlySchedule::factory()->create([
            'zone_id' => $this->zoneA->id,
            'status' => 'draft',
            'created_by' => $this->supervisorA->id,
        ]);

        $this->actingAs($this->supervisorB);

        Livewire::test(PendingMonthlySchedulesTable::class)
            ->assertDontSee($scheduleA->id);

        $this->actingAs($this->supervisorA);

        Livewire::test(PendingMonthlySchedulesTable::class)
            ->assertSee($scheduleA->id);

        $this->actingAs($this->superAdmin);

        Livewire::test(PendingMonthlySchedulesTable::class)
            ->assertSee($scheduleA->id);
    }

    public function test_all_twenty_one_resource_models_have_strictly_registered_policies(): void
    {
        $models = [
            User::class,
            Role::class,
            Employee::class,
            Position::class,
            Department::class,
            EmployeeAttendance::class,
            Item::class,
            Location::class,
            AssetLoan::class,
            InventoryLedger::class,
            MonthlySchedule::class,
            Abnormality::class,
            Zone::class,
            Workstation::class,
            InspectionCriteria::class,
            MasterWorkstationCriteria::class,
            FiveREvaluation::class,
            TrendAbnormality::class,
            Bulletin::class,
            QuickLink::class,
            GeneralDocument::class,
        ];

        foreach ($models as $modelClass) {
            $policy = Gate::getPolicyFor($modelClass);
            $this->assertNotNull(
                $policy,
                "Model {$modelClass} wajib memiliki Policy terdaftar (Zero Unprotected Resource)."
            );
        }
    }

    public function test_user_and_role_resources_strictly_block_non_super_admin_access(): void
    {
        $operationalRoles = ['managerial_staff', 'staff_penyelia', 'pelaksana_5r'];

        foreach ($operationalRoles as $roleName) {
            Role::firstOrCreate(['name' => $roleName, 'guard_name' => 'web']);
            $user = User::factory()->create();
            $user->assignRole($roleName);

            $this->actingAs($user);

            $this->assertFalse(UserResource::canViewAny());
            $this->assertFalse(UserResource::canCreate());
            $this->assertFalse(RoleResource::canViewAny());
            $this->assertFalse(RoleResource::canCreate());
        }

        $this->actingAs($this->superAdmin);

        $this->assertTrue(UserResource::canViewAny());
        $this->assertTrue(UserResource::canCreate());
        $this->assertTrue(RoleResource::canViewAny());
        $this->assertTrue(RoleResource::canCreate());
    }

    public function test_anti_self_role_tampering_disables_roles_field_and_blocks_self_demotion(): void
    {
        $secondSuperAdmin = User::factory()->create(['name' => 'Super Admin Kedua']);
        $secondSuperAdmin->assignRole('super_admin');

        $pelaksanaRole = Role::firstOrCreate(['name' => 'pelaksana_5r', 'guard_name' => 'web']);

        $this->actingAs($this->superAdmin);

        Livewire::test(EditUser::class, ['record' => $this->superAdmin->getKey()])
            ->assertFormFieldIsDisabled('roles')
            ->set('data.roles', [(string) $pelaksanaRole->id])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertTrue($this->superAdmin->fresh()->hasRole('super_admin'));
        $this->assertFalse($this->superAdmin->fresh()->hasRole('pelaksana_5r'));
    }

    public function test_last_standing_super_admin_guard_blocks_demotion_and_deletion(): void
    {
        $this->assertEquals(1, User::role('super_admin')->count());
        $this->assertTrue($this->superAdmin->isLastStandingSuperAdmin());

        try {
            $this->superAdmin->removeRole('super_admin');
            $this->fail('Expected DomainException when removing super_admin from last standing admin.');
        } catch (DomainException $e) {
            $this->assertStringContainsString('minimal satu akun Super Admin aktif', $e->getMessage());
        }

        try {
            $this->superAdmin->syncRoles([]);
            $this->fail('Expected DomainException when syncing empty roles on last standing admin.');
        } catch (DomainException $e) {
            $this->assertStringContainsString('minimal satu akun Super Admin aktif', $e->getMessage());
        }

        try {
            $this->superAdmin->delete();
            $this->fail('Expected DomainException when deleting last standing super admin.');
        } catch (DomainException $e) {
            $this->assertStringContainsString('minimal satu akun Super Admin aktif', $e->getMessage());
        }

        $this->assertNotNull($this->superAdmin->fresh());
        $this->assertTrue($this->superAdmin->fresh()->hasRole('super_admin'));
    }

    public function test_user_role_mutation_emits_structured_security_audit_log(): void
    {
        Log::spy();

        $staffRole = Role::firstOrCreate(['name' => 'staff_penyelia', 'guard_name' => 'web']);
        $targetUser = User::factory()->create(['email' => 'target.audit@inalum.id']);

        $this->actingAs($this->superAdmin);

        $targetUser->assignRole($staffRole);

        Log::shouldHaveReceived('info')
            ->withArgs(function (string $message, array $context) use ($targetUser): bool {
                return $message === 'Security Audit: User roles modified'
                    && $context['event'] === 'user.role_attached'
                    && $context['actor_id'] === $this->superAdmin->id
                    && $context['target_user_id'] === $targetUser->id
                    && in_array('staff_penyelia', $context['roles_after'], true);
            })
            ->atLeast()
            ->once();
    }
}
