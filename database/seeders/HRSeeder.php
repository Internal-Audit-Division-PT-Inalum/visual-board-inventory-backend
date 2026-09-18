<?php

namespace Database\Seeders;

use App\Domains\Core\Models\User;
use App\Domains\HR\Models\Department;
use App\Domains\HR\Models\Employee;
use App\Domains\HR\Models\EmployeeAttendance;
use Illuminate\Database\Seeder;

class HRSeeder extends Seeder
{
    public function run(): void
    {
        // Get some users to act as managers
        $users = User::all();
        if ($users->isEmpty()) {
            $users = User::factory(3)->create();
        }

        Department::factory(3)->create()->each(function (Department $department) use ($users) {
            // Assign random manager
            $department->update(['manager_id' => $users->random()->id]);

            Employee::factory(3)->create(['department_id' => $department->id])->each(function (Employee $employee) {
                // date must be unique per employee
                EmployeeAttendance::factory()->create(['employee_id' => $employee->id, 'date' => now()->subDays(1)]);
                EmployeeAttendance::factory()->create(['employee_id' => $employee->id, 'date' => now()->subDays(2)]);

                // Add data for TODAY so the UI has something to show!
                $statuses = ['present', 'present', 'present', 'sick', 'leave'];
                EmployeeAttendance::factory()->create([
                    'employee_id' => $employee->id,
                    'date' => now(), // TODAY
                    'status' => $statuses[array_rand($statuses)],
                ]);
            });
        });
    }
}
