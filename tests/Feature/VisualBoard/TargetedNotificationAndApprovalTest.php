<?php

namespace Tests\Feature\VisualBoard;

use App\Domains\Core\Models\User;
use App\Domains\HR\Models\Employee;
use App\Domains\VisualBoard\Models\Abnormality;
use App\Domains\VisualBoard\Models\MonthlySchedule;
use App\Domains\VisualBoard\Models\SchedulePic;
use App\Domains\VisualBoard\Models\Zone;
use App\Filament\Widgets\PendingAbnormalitiesTable;
use App\Filament\Widgets\PendingMonthlySchedulesTable;
use App\Services\VisualBoard\AbnormalityService;
use App\Services\VisualBoard\MonthlyScheduleService;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'web']);
    Role::firstOrCreate(['name' => 'staff_penyelia', 'guard_name' => 'web']);
    Role::firstOrCreate(['name' => 'managerial_staff', 'guard_name' => 'web']);
    Role::firstOrCreate(['name' => 'pelaksana_5r', 'guard_name' => 'web']);
});

test('abnormality creation sends notifications only to assigned pic, zone pics, and super admins', function () {
    $superAdmin = User::factory()->create();
    $superAdmin->assignRole('super_admin');

    $unrelatedStaff = User::factory()->create();
    $unrelatedStaff->assignRole('staff_penyelia');

    $zonePicUser = User::factory()->create();
    $zonePicEmployee = Employee::factory()->create(['user_id' => $zonePicUser->id]);

    $assignedPicUser = User::factory()->create();

    $zone = Zone::factory()->create([
        'pic_utama_id' => $zonePicEmployee->id,
    ]);

    $service = app(AbnormalityService::class);
    $abnormality = $service->createAbnormality([
        'zone_id' => $zone->id,
        'date_found' => now()->toDateString(),
        'problem_description' => 'Kebocoran pelumas hidrolik di zona kompresor',
        'pic_id' => $assignedPicUser->id,
    ]);

    expect($abnormality)->not->toBeNull()
        ->and($zonePicUser->notifications()->count())->toBeGreaterThanOrEqual(1)
        ->and($assignedPicUser->notifications()->count())->toBe(1)
        ->and($superAdmin->notifications()->count())->toBe(1)
        ->and($unrelatedStaff->notifications()->count())->toBe(0);
});

test('monthly schedule approval sends notifications only to creator, schedule pics, and zone pics', function () {
    $approver = User::factory()->create();
    $approver->assignRole('managerial_staff');

    $unrelatedUser = User::factory()->create();
    $unrelatedUser->assignRole('pelaksana_5r');

    $creatorUser = User::factory()->create();
    $schedulePicUser = User::factory()->create();

    $zonePicUser = User::factory()->create();
    $zonePicEmployee = Employee::factory()->create(['user_id' => $zonePicUser->id]);

    $zone = Zone::factory()->create([
        'pic_utama_id' => $zonePicEmployee->id,
    ]);

    $schedule = MonthlySchedule::factory()->create([
        'zone_id' => $zone->id,
        'created_by' => $creatorUser->id,
        'status' => 'draft',
        'approval_data' => [
            'pic_signed' => ['user_id' => 'dummy', 'signed_at' => now()->toIso8601String()],
            'staff_signed' => ['user_id' => 'dummy', 'signed_at' => now()->toIso8601String()],
        ],
    ]);

    SchedulePic::create([
        'monthly_schedule_id' => $schedule->id,
        'date' => now()->toDateString(),
        'pic_id' => $schedulePicUser->id,
        'pic_role' => 'utama',
    ]);

    $service = app(MonthlyScheduleService::class);
    $result = $service->approveSchedule($schedule->id, $approver);

    expect($result['success'])->toBeTrue()
        ->and($zonePicUser->notifications()->count())->toBe(1)
        ->and($creatorUser->notifications()->count())->toBe(1)
        ->and($schedulePicUser->notifications()->count())->toBe(1)
        ->and($unrelatedUser->notifications()->count())->toBe(0);
});

test('pending abnormalities widget isolates records by assigned user responsibility while super admin sees all', function () {
    $superAdmin = User::factory()->create();
    $superAdmin->assignRole('super_admin');

    $userA = User::factory()->create();
    $employeeA = Employee::factory()->create(['user_id' => $userA->id]);
    $zoneA = Zone::factory()->create(['pic_utama_id' => $employeeA->id]);

    $userB = User::factory()->create();
    $employeeB = Employee::factory()->create(['user_id' => $userB->id]);
    $zoneB = Zone::factory()->create(['pic_utama_id' => $employeeB->id]);

    $abnormalityA = Abnormality::factory()->create([
        'zone_id' => $zoneA->id,
        'verified_by_staff_id' => null,
        'verified_by_ms_id' => null,
    ]);

    $abnormalityB = Abnormality::factory()->create([
        'zone_id' => $zoneB->id,
        'verified_by_staff_id' => null,
        'verified_by_ms_id' => null,
    ]);

    $this->actingAs($userA);
    Livewire::test(PendingAbnormalitiesTable::class)
        ->assertSuccessful()
        ->assertCanSeeTableRecords([$abnormalityA])
        ->assertCanNotSeeTableRecords([$abnormalityB]);

    $this->actingAs($userB);
    Livewire::test(PendingAbnormalitiesTable::class)
        ->assertSuccessful()
        ->assertCanSeeTableRecords([$abnormalityB])
        ->assertCanNotSeeTableRecords([$abnormalityA]);

    $this->actingAs($superAdmin);
    Livewire::test(PendingAbnormalitiesTable::class)
        ->assertSuccessful()
        ->assertCanSeeTableRecords([$abnormalityA, $abnormalityB]);
});

test('pending monthly schedules widget isolates records by user responsibility while super admin sees all', function () {
    $superAdmin = User::factory()->create();
    $superAdmin->assignRole('super_admin');

    $userA = User::factory()->create();
    $employeeA = Employee::factory()->create(['user_id' => $userA->id]);
    $zoneA = Zone::factory()->create(['pic_utama_id' => $employeeA->id]);

    $userB = User::factory()->create();
    $employeeB = Employee::factory()->create(['user_id' => $userB->id]);
    $zoneB = Zone::factory()->create(['pic_utama_id' => $employeeB->id]);

    $scheduleA = MonthlySchedule::factory()->create([
        'zone_id' => $zoneA->id,
        'created_by' => $userA->id,
        'status' => 'draft',
    ]);

    $scheduleB = MonthlySchedule::factory()->create([
        'zone_id' => $zoneB->id,
        'created_by' => $userB->id,
        'status' => 'draft',
    ]);

    $this->actingAs($userA);
    Livewire::test(PendingMonthlySchedulesTable::class)
        ->assertSuccessful()
        ->assertCanSeeTableRecords([$scheduleA])
        ->assertCanNotSeeTableRecords([$scheduleB]);

    $this->actingAs($userB);
    Livewire::test(PendingMonthlySchedulesTable::class)
        ->assertSuccessful()
        ->assertCanSeeTableRecords([$scheduleB])
        ->assertCanNotSeeTableRecords([$scheduleA]);

    $this->actingAs($superAdmin);
    Livewire::test(PendingMonthlySchedulesTable::class)
        ->assertSuccessful()
        ->assertCanSeeTableRecords([$scheduleA, $scheduleB]);
});
