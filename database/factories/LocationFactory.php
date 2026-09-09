<?php

namespace Database\Factories;

use App\Domains\Inventory\Models\Location;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Location>
 */
class LocationFactory extends Factory
{
    protected $model = Location::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->unique()->words(3, true),
            'code' => strtoupper($this->faker->unique()->bothify('LOC-???-##')),
            'description' => $this->faker->optional()->sentence(),
            'is_active' => true,
        ];
    }
}
