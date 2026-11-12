<?php

namespace Tests\Feature\Hardening;

use App\Domains\Core\Models\User;
use App\Domains\Inventory\Models\AssetLoan;
use App\Domains\Inventory\Models\Item;
use App\Domains\VisualBoard\Exceptions\UnauthorizedVerificationException;
use App\Domains\VisualBoard\Models\Abnormality;
use App\Domains\VisualBoard\Models\MonthlySchedule;
use App\Domains\VisualBoard\Models\Zone;
use App\Filament\Resources\Abnormalities\AbnormalityResource;
use App\Filament\Resources\AssetLoans\AssetLoanResource;
use App\Filament\Resources\AssetLoans\Pages\ListAssetLoans;
use App\Filament\Resources\Bulletins\BulletinResource;
use App\Filament\Resources\Departments\DepartmentResource;
use App\Filament\Resources\EmployeeAttendances\EmployeeAttendanceResource;
use App\Filament\Resources\Employees\EmployeeResource;
use App\Filament\Resources\FiveREvaluations\FiveREvaluationResource;
use App\Filament\Resources\GeneralDocuments\GeneralDocumentResource;
use App\Filament\Resources\InspectionCriterias\InspectionCriteriaResource;
use App\Filament\Resources\InventoryLedgers\InventoryLedgerResource;
use App\Filament\Resources\Items\ItemResource;
use App\Filament\Resources\Items\Pages\ListItems;
use App\Filament\Resources\Locations\LocationResource;
use App\Filament\Resources\MasterWorkstationCriterias\MasterWorkstationCriteriaResource;
use App\Filament\Resources\MonthlySchedules\MonthlyScheduleResource;
use App\Filament\Resources\Positions\PositionResource;
use App\Filament\Resources\Users\Pages\CreateUser;
use App\Filament\Resources\Users\Pages\EditUser;
use App\Filament\Resources\Users\Pages\ListUsers;
use App\Filament\Resources\Users\UserResource;
use App\Filament\Resources\Workstations\WorkstationResource;
use App\Filament\Resources\Zones\ZoneResource;
use App\Services\VisualBoard\AbnormalityService;
use App\Services\VisualBoard\MonthlyScheduleService;
use BezhanSalleh\FilamentShield\Resources\Roles\RoleResource;
use Database\Seeders\RbacSeeder;
use DomainException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class RbacMatrixSecurityTest extends TestCase
{
    use RefreshDatabase;

    protected User $superAdmin;

    protected User $managerialStaff;

    protected User $staffPenyelia;

    protected User $pelaksana5R;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RbacSeeder::class);

        $this->superAdmin = User::factory()->create(['name' => 'Super Admin Inalum', 'email' => 'sa@inalum.id']);
        $this->superAdmin->assignRole('super_admin');

        $this->managerialStaff = User::factory()->create(['name' => 'Managerial Staff Inalum', 'email' => 'ms@inalum.id']);
        $this->managerialStaff->assignRole('managerial_staff');

        $this->staffPenyelia = User::factory()->create(['name' => 'Staff Penyelia Inalum', 'email' => 'sp@inalum.id']);
        $this->staffPenyelia->assignRole('staff_penyelia');

        $this->pelaksana5R = User::factory()->create(['name' => 'Pelaksana 5R Inalum', 'email' => 'p5r@inalum.id']);
        $this->pelaksana5R->assignRole('pelaksana_5r');
    }

    public function test_privilege_escalation_forced_browsing_to_user_and_role_management_is_blocked(): void
    {
        $nonSuperAdmins = [$this->managerialStaff, $this->staffPenyelia, $this->pelaksana5R];

        foreach ($nonSuperAdmins as $actor) {
            $this->actingAs($actor);

            $this->get(UserResource::getUrl('index'))->assertForbidden();
            $this->get(UserResource::getUrl('create'))->assertForbidden();
            $this->get(UserResource::getUrl('edit', ['record' => $this->superAdmin->getKey()]))->assertForbidden();
            $this->get(RoleResource::getUrl('index'))->assertForbidden();

            Livewire::test(ListUsers::class)->assertForbidden();
            Livewire::test(CreateUser::class)->assertForbidden();
        }
    }

    public function test_anti_self_role_tampering_blocks_livewire_state_injection(): void
    {
        $backupAdmin = User::factory()->create(['email' => 'backup.sa@inalum.id']);
        $backupAdmin->assignRole('super_admin');

        $pelaksanaRole = Role::findByName('pelaksana_5r', 'web');

        $this->actingAs($this->superAdmin);

        Livewire::test(EditUser::class, ['record' => $this->superAdmin->getKey()])
            ->assertFormFieldIsDisabled('roles')
            ->set('data.roles', [(string) $pelaksanaRole->id])
            ->call('save')
            ->assertHasNoFormErrors();

        $freshAdmin = $this->superAdmin->fresh();
        $this->assertTrue($freshAdmin->hasRole('super_admin'));
        $this->assertFalse($freshAdmin->hasRole('pelaksana_5r'));
    }

    public function test_last_standing_super_admin_cannot_be_demoted_or_deleted(): void
    {
        $this->assertEquals(1, User::role('super_admin')->count());
        $this->assertTrue($this->superAdmin->isLastStandingSuperAdmin());

        $pelaksanaRole = Role::findByName('pelaksana_5r', 'web');

        try {
            $this->superAdmin->syncRoles([$pelaksanaRole->id]);
            $this->fail('Expected DomainException when demoting last standing super_admin.');
        } catch (DomainException $e) {
            $this->assertStringContainsString('Operasi ditolak. Sistem wajib memiliki minimal satu akun Super Admin aktif.', $e->getMessage());
        }

        try {
            $this->superAdmin->removeRole('super_admin');
            $this->fail('Expected DomainException when removing super_admin role from last standing admin.');
        } catch (DomainException $e) {
            $this->assertStringContainsString('Operasi ditolak. Sistem wajib memiliki minimal satu akun Super Admin aktif.', $e->getMessage());
        }

        try {
            $this->superAdmin->delete();
            $this->fail('Expected DomainException when deleting last standing super_admin.');
        } catch (DomainException $e) {
            $this->assertStringContainsString('Operasi ditolak. Sistem wajib memiliki minimal satu akun Super Admin aktif.', $e->getMessage());
        }

        $this->assertTrue($this->superAdmin->fresh()->hasRole('super_admin'));
    }

    public function test_inventory_custodian_boundary_restricts_mutations_exclusively_to_super_admin(): void
    {
        $item = Item::factory()->asset()->create(['current_stock' => 10]);
        $loan = AssetLoan::factory()->create([
            'item_id' => $item->id,
            'quantity' => 2,
            'returned_quantity' => 0,
            'fully_returned_at' => null,
        ]);

        $this->actingAs($this->managerialStaff);
        Livewire::test(ListItems::class)
            ->assertTableActionHidden('adjust_stock', $item);
        Livewire::test(ListAssetLoans::class)
            ->assertTableActionHidden('return_asset', $loan);
        $this->assertFalse(ItemResource::canCreate());
        $this->assertFalse(ItemResource::canEdit($item));
        $this->assertFalse(ItemResource::canDelete($item));

        $this->actingAs($this->staffPenyelia);
        Livewire::test(ListItems::class)
            ->assertTableActionHidden('adjust_stock', $item);
        $this->get(AssetLoanResource::getUrl('index'))->assertForbidden();

        $this->actingAs($this->pelaksana5R);
        $this->get(ItemResource::getUrl('index'))->assertForbidden();
        $this->get(AssetLoanResource::getUrl('index'))->assertForbidden();

        $this->actingAs($this->superAdmin);
        Livewire::test(ListItems::class)
            ->assertTableActionVisible('adjust_stock', $item);
        Livewire::test(ListAssetLoans::class)
            ->assertTableActionVisible('return_asset', $loan);
        $this->assertTrue(ItemResource::canCreate());
    }

    public function test_monthly_schedule_approval_enforces_segregation_of_duties_across_roles(): void
    {
        $zone = Zone::factory()->create();
        $schedule = MonthlySchedule::factory()->create([
            'zone_id' => $zone->id,
            'created_by' => $this->pelaksana5R->id,
            'status' => 'draft',
            'approval_data' => [],
        ]);

        $service = app(MonthlyScheduleService::class);

        $resPic = $service->approveSchedule($schedule->id, $this->pelaksana5R);
        $this->assertTrue($resPic['success']);
        $this->assertEquals('pic', $resPic['level']);

        $resPicDuplicate = $service->approveSchedule($schedule->id, $this->pelaksana5R);
        $this->assertFalse($resPicDuplicate['success']);

        $resStaff = $service->approveSchedule($schedule->id, $this->staffPenyelia);
        $this->assertTrue($resStaff['success']);
        $this->assertEquals('staff', $resStaff['level']);
        $this->assertEquals('draft', $schedule->fresh()->status);

        $resStaffDuplicate = $service->approveSchedule($schedule->id, $this->staffPenyelia);
        $this->assertFalse($resStaffDuplicate['success']);

        $resManager = $service->approveSchedule($schedule->id, $this->managerialStaff);
        $this->assertTrue($resManager['success']);
        $this->assertEquals('manager', $resManager['level']);
        $this->assertEquals('in_progress', $schedule->fresh()->status);
    }

    public function test_abnormality_verification_enforces_role_hierarchy_and_blocks_pelaksana(): void
    {
        $zone = Zone::factory()->create();
        $abnormality = Abnormality::factory()->create([
            'zone_id' => $zone->id,
            'reported_by_id' => $this->pelaksana5R->id,
            'verified_by_staff_id' => null,
            'verified_by_ms_id' => null,
        ]);

        $service = app(AbnormalityService::class);

        try {
            $service->verifyAbnormality($abnormality->id, $this->pelaksana5R);
            $this->fail('Expected UnauthorizedVerificationException for pelaksana_5r.');
        } catch (UnauthorizedVerificationException) {
            $this->assertNull($abnormality->fresh()->verified_by_staff_id);
        }

        $resStaff = $service->verifyAbnormality($abnormality->id, $this->staffPenyelia);
        $this->assertTrue($resStaff['success']);
        $this->assertEquals($this->staffPenyelia->id, $abnormality->fresh()->verified_by_staff_id);
        $this->assertNull($abnormality->fresh()->verified_by_ms_id);

        $resManager = $service->verifyAbnormality($abnormality->id, $this->managerialStaff);
        $this->assertTrue($resManager['success']);
        $this->assertEquals($this->managerialStaff->id, $abnormality->fresh()->verified_by_ms_id);
    }

    public function test_navigation_and_resource_visibility_strictly_matches_four_role_matrix(): void
    {
        $this->actingAs($this->managerialStaff);
        $this->assertFalse(UserResource::canViewAny());
        $this->assertFalse(RoleResource::canViewAny());
        $this->assertTrue(EmployeeResource::canViewAny());
        $this->assertFalse(EmployeeResource::canCreate());
        $this->assertTrue(PositionResource::canViewAny());
        $this->assertFalse(PositionResource::canCreate());
        $this->assertTrue(DepartmentResource::canViewAny());
        $this->assertTrue(EmployeeAttendanceResource::canViewAny());
        $this->assertTrue(ItemResource::canViewAny());
        $this->assertTrue(LocationResource::canViewAny());
        $this->assertTrue(AssetLoanResource::canViewAny());
        $this->assertTrue(InventoryLedgerResource::canViewAny());
        $this->assertTrue(MonthlyScheduleResource::canViewAny());
        $this->assertTrue(AbnormalityResource::canViewAny());
        $this->assertTrue(FiveREvaluationResource::canCreate());
        $this->assertTrue(BulletinResource::canCreate());
        $this->assertTrue(GeneralDocumentResource::canCreate());

        $this->actingAs($this->staffPenyelia);
        $this->assertFalse(UserResource::canViewAny());
        $this->assertFalse(RoleResource::canViewAny());
        $this->assertTrue(EmployeeResource::canViewAny());
        $this->assertTrue(ItemResource::canViewAny());
        $this->assertTrue(LocationResource::canViewAny());
        $this->assertFalse(AssetLoanResource::canViewAny());
        $this->assertFalse(InventoryLedgerResource::canViewAny());
        $this->assertTrue(FiveREvaluationResource::canCreate());
        $this->assertFalse(BulletinResource::canCreate());
        $this->assertFalse(GeneralDocumentResource::canCreate());

        $this->actingAs($this->pelaksana5R);
        $this->assertFalse(UserResource::canViewAny());
        $this->assertFalse(RoleResource::canViewAny());
        $this->assertFalse(EmployeeResource::canViewAny());
        $this->assertFalse(PositionResource::canViewAny());
        $this->assertFalse(DepartmentResource::canViewAny());
        $this->assertFalse(EmployeeAttendanceResource::canViewAny());
        $this->assertFalse(ItemResource::canViewAny());
        $this->assertFalse(LocationResource::canViewAny());
        $this->assertFalse(AssetLoanResource::canViewAny());
        $this->assertFalse(InventoryLedgerResource::canViewAny());
        $this->assertTrue(MonthlyScheduleResource::canViewAny());
        $this->assertFalse(MonthlyScheduleResource::canCreate());
        $this->assertTrue(AbnormalityResource::canViewAny());
        $this->assertTrue(AbnormalityResource::canCreate());
        $this->assertTrue(ZoneResource::canViewAny());
        $this->assertFalse(ZoneResource::canCreate());
        $this->assertTrue(WorkstationResource::canViewAny());
        $this->assertFalse(WorkstationResource::canCreate());
        $this->assertTrue(InspectionCriteriaResource::canViewAny());
        $this->assertTrue(MasterWorkstationCriteriaResource::canViewAny());
        $this->assertTrue(FiveREvaluationResource::canViewAny());
        $this->assertFalse(FiveREvaluationResource::canCreate());
        $this->assertTrue(BulletinResource::canViewAny());
        $this->assertFalse(BulletinResource::canCreate());
        $this->assertTrue(GeneralDocumentResource::canViewAny());
        $this->assertFalse(GeneralDocumentResource::canCreate());
    }

    public function test_synthetic_pwa_user_accounts_are_strictly_blocked_from_filament_admin_panel(): void
    {
        $syntheticUser = User::factory()->create([
            'name' => 'Pegawai Lapangan PWA',
            'email' => '39214171@synthetic.inalum.id',
        ]);
        $syntheticUser->assignRole('super_admin');

        $panel = filament()->getPanel('admin');
        $this->assertFalse($syntheticUser->canAccessPanel($panel));
    }
}
