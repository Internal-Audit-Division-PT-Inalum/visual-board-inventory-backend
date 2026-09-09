<?php

namespace Tests\Unit\Domains;

use App\Domains\Core\Models\User;
use App\Domains\HR\Models\Department;
use App\Domains\HR\Models\Employee;
use App\Domains\HR\Models\EmployeeAttendance;
use App\Services\HR\AttendanceService;
use App\Services\HR\DepartmentService;
use App\Services\HR\EmployeeService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

test('it can create a department using service', function () {
    $service = app(DepartmentService::class);

    $department = $service->createDepartment([
        'name' => 'IT Department',
        'is_active' => true,
    ]);

    expect($department)->toBeInstanceOf(Department::class)
        ->and($department->name)->toBe('IT Department')
        ->and($department->id)->not->toBeNull();
});

test('it can create an employee using service', function () {
    $user = User::factory()->create();
    $department = app(DepartmentService::class)->createDepartment(['name' => 'HR']);

    $service = app(EmployeeService::class);
    $employee = $service->createEmployee([
        'user_id' => $user->id,
        'department_id' => $department->id,
        'namecode' => 'EMP-12345',
        'position_title' => 'Manager',
    ]);

    expect($employee)->toBeInstanceOf(Employee::class)
        ->and($employee->namecode)->toBe('EMP-12345')
        ->and($employee->department->name)->toBe('HR')
        ->and($employee->user->email)->toBe($user->email);
});

test('it can record an employee attendance using service', function () {
    $user = User::factory()->create();
    $employee = app(EmployeeService::class)->createEmployee([
        'user_id' => $user->id,
        'namecode' => 'EMP-001',
    ]);

    $service = app(AttendanceService::class);
    $attendance = $service->recordAttendance([
        'employee_id' => $employee->id,
        'date' => '2026-09-09',
        'status' => 'present',
    ]);

    expect($attendance)->toBeInstanceOf(EmployeeAttendance::class)
        ->and($attendance->status)->toBe('present')
        ->and($attendance->employee->namecode)->toBe('EMP-001');
});
