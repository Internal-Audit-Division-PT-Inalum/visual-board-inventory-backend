<?php

namespace Database\Factories;

use App\Domains\VisualBoard\Models\Workstation;
use Illuminate\Database\Eloquent\Factories\Factory;

class WorkstationFactory extends Factory
{
    protected $model = Workstation::class;

    public function definition(): array
    {
        return [
            'name' => 'Workstation ' . $this->faker->unique()->numberBetween(100, 999),
            'is_active' => true,
        ];
    }
}
