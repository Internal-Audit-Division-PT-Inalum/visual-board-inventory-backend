<?php

namespace Database\Seeders;

use App\Domains\HR\Models\Department;
use App\Domains\HR\Models\Employee;
use App\Domains\HR\Models\EmployeeAttendance;
use Illuminate\Database\Seeder;

class HRSeeder extends Seeder
{
    public function run(): void
    {
        Department::factory(3)->create()->each(function (Department $department) {
            Employee::factory(3)->create(['department_id' => $department->id])->each(function (Employee $employee) {
                // date must be unique per employee
                EmployeeAttendance::factory()->create(['employee_id' => $employee->id, 'date' => now()->subDays(1)]);
                EmployeeAttendance::factory()->create(['employee_id' => $employee->id, 'date' => now()->subDays(2)]);
            });
        });
    }
}
