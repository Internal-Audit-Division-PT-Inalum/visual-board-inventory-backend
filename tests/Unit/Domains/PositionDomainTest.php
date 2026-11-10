<?php

namespace Tests\Unit\Domains;

use App\Domains\Core\Models\User;
use App\Domains\HR\Models\Department;
use App\Domains\HR\Models\Employee;
use App\Domains\HR\Models\Position;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

test('it can create a position with ulid and valid attributes', function () {
    $position = Position::create([
        'name' => 'Kepala Divisi',
        'hierarchy_level' => 3,
        'description' => 'Pimpinan unit kerja divisi',
        'is_active' => true,
    ]);

    expect($position)->toBeInstanceOf(Position::class)
        ->and($position->id)->not->toBeNull()
        ->and(strlen($position->id))->toBe(26) // ULID length
        ->and($position->name)->toBe('Kepala Divisi')
        ->and($position->hierarchy_level)->toBe(3)
        ->and($position->is_active)->toBeTrue();
});

test('it enforces unique position name constraint', function () {
    Position::create([
        'name' => 'Kepala Departemen',
        'hierarchy_level' => 4,
    ]);

    expect(fn () => Position::create([
        'name' => 'Kepala Departemen',
        'hierarchy_level' => 4,
    ]))->toThrow(QueryException::class);
});

test('it supports soft deletes on positions', function () {
    $position = Position::create([
        'name' => 'Posisi Sementara',
        'hierarchy_level' => 6,
    ]);

    $position->delete();

    expect(Position::count())->toBe(0)
        ->and(Position::withTrashed()->count())->toBe(1)
        ->and(Position::withTrashed()->first()->name)->toBe('Posisi Sementara');
});

test('it defines hasMany relationship to employees', function () {
    $position = Position::create([
        'name' => 'Staff',
        'hierarchy_level' => 6,
    ]);

    $department = Department::create(['name' => 'Internal Audit']);
    $user = User::factory()->create();

    $employee = Employee::create([
        'user_id' => $user->id,
        'department_id' => $department->id,
        'position_id' => $position->id,
        'namecode' => '102934',
        'employee_code' => 'EMP-102934',
        'name' => 'Budi Santoso',
    ]);

    expect($position->employees)->toHaveCount(1)
        ->and($position->employees->first()->id)->toBe($employee->id)
        ->and($employee->position->id)->toBe($position->id);
});

test('it automatically synchronizes position_title and hierarchy_level when position_id is set on employee', function () {
    $position = Position::create([
        'name' => 'Kepala Seksi',
        'hierarchy_level' => 5,
    ]);

    $department = Department::create(['name' => 'Audit Pabrik']);
    $user = User::factory()->create();

    $employee = Employee::create([
        'user_id' => $user->id,
        'department_id' => $department->id,
        'position_id' => $position->id,
        'namecode' => '102935',
        'employee_code' => 'EMP-102935',
        'name' => 'Rahmat Hidayat',
    ]);

    // Refresh from database to verify persistence of synchronized fields
    $employee->refresh();

    expect($employee->position_title)->toBe('Kepala Seksi')
        ->and($employee->hierarchy_level)->toBe(5)
        ->and($employee->position_id)->toBe($position->id);
});

test('it automatically updates position_title and hierarchy_level when employee changes position_id', function () {
    $posStaff = Position::create([
        'name' => 'Staff',
        'hierarchy_level' => 6,
    ]);

    $posManager = Position::create([
        'name' => 'Kepala Departemen',
        'hierarchy_level' => 4,
    ]);

    $department = Department::create(['name' => 'Audit Operasional']);
    $user = User::factory()->create();

    $employee = Employee::create([
        'user_id' => $user->id,
        'department_id' => $department->id,
        'position_id' => $posStaff->id,
        'namecode' => '102936',
        'employee_code' => 'EMP-102936',
        'name' => 'Siti Aminah',
    ]);

    expect($employee->position_title)->toBe('Staff')
        ->and($employee->hierarchy_level)->toBe(6);

    // Promote to Kepala Departemen
    $employee->update(['position_id' => $posManager->id]);
    $employee->refresh();

    expect($employee->position_title)->toBe('Kepala Departemen')
        ->and($employee->hierarchy_level)->toBe(4)
        ->and($employee->position_id)->toBe($posManager->id);
});
