<?php

namespace Tests\Feature\Filament;

use App\Domains\Core\Models\User;
use App\Domains\HR\Models\Department;
use App\Domains\HR\Models\Employee;
use App\Domains\HR\Models\Position;
use App\Filament\Resources\Employees\Pages\CreateEmployee;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;

test('employee form rejects namecode containing letters and requires numeric digits only', function () {
    $role = Role::firstOrCreate(['name' => 'super_admin']);
    $user = User::factory()->create(['email' => 'admin@inalum.id']);
    $user->assignRole($role);
    $dept = Department::create(['name' => 'Internal Audit']);
    $position = Position::factory()->create(['name' => 'Staff', 'hierarchy_level' => 6]);

    $this->actingAs($user);

    Livewire::test(CreateEmployee::class)
        ->fillForm([
            'department_id' => $dept->id,
            'position_id' => $position->id,
            'namecode' => 'EMP12345',
            'employee_code' => '12345',
            'name' => 'Budi Santoso',
            'is_active' => true,
        ])
        ->call('create')
        ->assertHasFormErrors(['namecode']);
});

test('employee form accepts pure numeric namecode and creates employee successfully', function () {
    $role = Role::firstOrCreate(['name' => 'super_admin']);
    $user = User::factory()->create(['email' => 'admin@inalum.id']);
    $user->assignRole($role);
    $dept = Department::create(['name' => 'Internal Audit']);
    $position = Position::factory()->create(['name' => 'Kepala Seksi', 'hierarchy_level' => 5]);

    $this->actingAs($user);

    Livewire::test(CreateEmployee::class)
        ->fillForm([
            'department_id' => $dept->id,
            'position_id' => $position->id,
            'namecode' => '39214171',
            'employee_code' => '39214171',
            'name' => 'Thaofan D. E. Saputro',
            'is_active' => true,
            'provision_pwa_account' => false,
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $employee = Employee::where('namecode', '39214171')->first();
    expect($employee)->not->toBeNull()
        ->and($employee->position_id)->toBe($position->id)
        ->and($employee->position_title)->toBe('Kepala Seksi')
        ->and($employee->hierarchy_level)->toBe(5);
});

test('employee creation with auto provision toggle provisions pwa user and default pin', function () {
    $role = Role::firstOrCreate(['name' => 'super_admin']);
    $user = User::factory()->create(['email' => 'admin@inalum.id']);
    $user->assignRole($role);
    $dept = Department::create(['name' => 'Internal Audit']);
    $position = Position::factory()->create(['name' => 'Auditor', 'hierarchy_level' => 6]);

    $this->actingAs($user);

    Livewire::test(CreateEmployee::class)
        ->fillForm([
            'department_id' => $dept->id,
            'position_id' => $position->id,
            'namecode' => '39517001',
            'employee_code' => '39517001',
            'name' => 'Ari Sutoro',
            'is_active' => true,
            'provision_pwa_account' => true,
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $employee = Employee::where('namecode', '39517001')->first();
    expect($employee)->not->toBeNull()
        ->and($employee->user_id)->not->toBeNull()
        ->and($employee->must_change_pin)->toBeTrue()
        ->and(Hash::check('123456', (string) $employee->pin))->toBeTrue();

    $pwaUser = User::find($employee->user_id);
    expect($pwaUser)->not->toBeNull()
        ->and($pwaUser->email)->toBe('39517001@synthetic.inalum.id')
        ->and($pwaUser->name)->toBe('Ari Sutoro');
});
