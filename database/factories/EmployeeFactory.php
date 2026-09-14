<?php

namespace Database\Factories;

use App\Domains\HR\Models\Employee;
use App\Domains\HR\Models\Department;
use Illuminate\Database\Eloquent\Factories\Factory;

class EmployeeFactory extends Factory
{
    protected $model = Employee::class;

    public function definition(): array
    {
        return [
            'user_id' => \App\Domains\Core\Models\User::factory(),
            'department_id' => Department::factory(),
            'namecode' => $this->faker->unique()->numerify('######'),
            'position_title' => $this->faker->jobTitle(),
        ];
    }
}
