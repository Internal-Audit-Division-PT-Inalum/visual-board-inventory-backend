<?php

namespace Database\Factories;

use App\Domains\HR\Models\EmployeeAttendance;
use App\Domains\HR\Models\Employee;
use Illuminate\Database\Eloquent\Factories\Factory;

class EmployeeAttendanceFactory extends Factory
{
    protected $model = EmployeeAttendance::class;

    public function definition(): array
    {
        return [
            'employee_id' => Employee::factory(),
            'date' => $this->faker->date(),
            'status' => $this->faker->randomElement(['present', 'leave', 'sick', 'business_trip']),
        ];
    }
}
