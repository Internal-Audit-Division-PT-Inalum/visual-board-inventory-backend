<?php

namespace Tests\Feature\Seeder;

use App\Domains\Core\Models\User;
use App\Filament\Resources\AssetLoans\AssetLoanResource;
use App\Filament\Resources\Employees\EmployeeResource;
use App\Filament\Resources\Items\ItemResource;
use App\Filament\Resources\MonthlySchedules\MonthlyScheduleResource;
use App\Filament\Resources\Users\UserResource;
use Database\Seeders\DatabaseSeeder;
use Database\Seeders\RbacSeeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

test('rbac seeder provisions exactly four standardized roles and 252 permissions idempotently', function () {
    $this->seed(RbacSeeder::class);
    $this->seed(RbacSeeder::class);

    $roles = Role::pluck('name')->sort()->values()->all();

    expect($roles)->toBe([
        'managerial_staff',
        'pelaksana_5r',
        'staff_penyelia',
        'super_admin',
    ])->and(Permission::count())->toBe(252);
});

test('rbac seeder maps permissions accurately according to enterprise access matrix', function () {
    $this->seed(RbacSeeder::class);

    $managerial = User::factory()->create();
    $managerial->assignRole('managerial_staff');

    $penyelia = User::factory()->create();
    $penyelia->assignRole('staff_penyelia');

    $pelaksana = User::factory()->create();
    $pelaksana->assignRole('pelaksana_5r');

    $this->actingAs($managerial);
    expect(UserResource::canViewAny())->toBeFalse()
        ->and(EmployeeResource::canViewAny())->toBeTrue()
        ->and(EmployeeResource::canCreate())->toBeFalse()
        ->and(ItemResource::canViewAny())->toBeTrue()
        ->and(ItemResource::canCreate())->toBeFalse()
        ->and(AssetLoanResource::canViewAny())->toBeTrue()
        ->and(MonthlyScheduleResource::canViewAny())->toBeTrue();

    $this->actingAs($penyelia);
    expect(UserResource::canViewAny())->toBeFalse()
        ->and(EmployeeResource::canViewAny())->toBeTrue()
        ->and(ItemResource::canViewAny())->toBeTrue()
        ->and(AssetLoanResource::canViewAny())->toBeFalse()
        ->and(MonthlyScheduleResource::canViewAny())->toBeTrue();

    $this->actingAs($pelaksana);
    expect(UserResource::canViewAny())->toBeFalse()
        ->and(EmployeeResource::canViewAny())->toBeFalse()
        ->and(ItemResource::canViewAny())->toBeFalse()
        ->and(AssetLoanResource::canViewAny())->toBeFalse()
        ->and(MonthlyScheduleResource::canViewAny())->toBeTrue();
});

test('database seeder integrates rbac seeder and provisions default super admin', function () {
    $this->seed(DatabaseSeeder::class);

    $admin = User::where('email', 'admin@admin.com')->first();

    expect($admin)->not->toBeNull()
        ->and($admin->hasRole('super_admin'))->toBeTrue()
        ->and(Permission::count())->toBe(252);
});
