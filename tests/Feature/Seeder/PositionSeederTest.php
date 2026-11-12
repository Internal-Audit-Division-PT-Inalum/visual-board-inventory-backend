<?php

namespace Tests\Feature\Seeder;

use App\Domains\Core\Models\User;
use App\Domains\HR\Models\Department;
use App\Domains\HR\Models\Employee;
use App\Domains\HR\Models\Position;
use Database\Seeders\PositionSeeder;

test('it seeds 9 standardized inalum positions with 7 hierarchy levels', function () {
    $this->seed(PositionSeeder::class);

    expect(Position::count())->toBe(9);

    $expectedPositions = [
        'Dewan Direksi' => 1,
        'Dewan Komisaris' => 1,
        'Kepala Grup' => 2,
        'Kepala Divisi' => 3,
        'Kepala Departemen' => 4,
        'Kepala Seksi' => 5,
        'Staff' => 6,
        'Operator' => 7,
        'Administrasi' => 7,
    ];

    foreach ($expectedPositions as $name => $level) {
        $pos = Position::where('name', $name)->first();
        expect($pos)->not->toBeNull()
            ->and($pos->hierarchy_level)->toBe($level)
            ->and($pos->is_active)->toBeTrue();
    }
});

test('it is idempotent and does not create duplicate positions on multiple runs', function () {
    $this->seed(PositionSeeder::class);
    $this->seed(PositionSeeder::class);

    expect(Position::count())->toBe(9);
});

test('it backfills existing unlinked employees to correct positions based on title', function () {
    $dept = Department::create(['name' => 'Internal Audit']);
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();
    $user3 = User::factory()->create();

    $empDivisi = Employee::create([
        'user_id' => $user1->id,
        'department_id' => $dept->id,
        'namecode' => '100001',
        'employee_code' => 'EMP-100001',
        'name' => 'Kepala Divisi Audit',
        'position_title' => 'Kepala Divisi Internal Audit',
        'hierarchy_level' => 1, // Sebelumnya salah level (1) di data lama
    ]);

    $empStaff = Employee::create([
        'user_id' => $user2->id,
        'department_id' => $dept->id,
        'namecode' => '100002',
        'employee_code' => 'EMP-100002',
        'name' => 'Staff Auditor Senior',
        'position_title' => 'Senior Auditor Pabrik',
        'hierarchy_level' => 3,
    ]);

    $empAdmin = Employee::create([
        'user_id' => $user3->id,
        'department_id' => $dept->id,
        'namecode' => '100003',
        'employee_code' => 'EMP-100003',
        'name' => 'Staff Administrasi',
        'position_title' => 'Administrasi & Logistik',
        'hierarchy_level' => 5,
    ]);

    $this->seed(PositionSeeder::class);

    $empDivisi->refresh();
    $empStaff->refresh();
    $empAdmin->refresh();

    expect($empDivisi->position_id)->not->toBeNull()
        ->and($empDivisi->position->name)->toBe('Kepala Divisi')
        ->and($empDivisi->hierarchy_level)->toBe(3)
        ->and($empStaff->position_id)->not->toBeNull()
        ->and($empStaff->position->name)->toBe('Staff')
        ->and($empStaff->hierarchy_level)->toBe(6)
        ->and($empAdmin->position_id)->not->toBeNull()
        ->and($empAdmin->position->name)->toBe('Administrasi')
        ->and($empAdmin->hierarchy_level)->toBe(7);
});
