<?php

namespace Database\Factories;

use App\Domains\HR\Models\Position;
use Illuminate\Database\Eloquent\Factories\Factory;

class PositionFactory extends Factory
{
    protected $model = Position::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->unique()->jobTitle(),
            'hierarchy_level' => $this->faker->numberBetween(1, 7),
            'description' => $this->faker->sentence(),
            'is_active' => true,
        ];
    }
}
