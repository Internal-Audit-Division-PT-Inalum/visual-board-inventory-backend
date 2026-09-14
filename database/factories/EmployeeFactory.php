<?php

namespace Database\Factories;

use App\Domains\Core\Models\User;
use App\Domains\HR\Models\Department;
use App\Domains\HR\Models\Employee;
use Illuminate\Database\Eloquent\Factories\Factory;

class EmployeeFactory extends Factory
{
    protected $model = Employee::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'department_id' => Department::factory(),
            'namecode' => $this->faker->unique()->numerify('######'),
            'position_title' => $this->faker->jobTitle(),
        ];
    }
}
