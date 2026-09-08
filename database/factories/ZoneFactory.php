<?php

namespace Database\Factories;

use App\Domains\VisualBoard\Models\Zone;
use Illuminate\Database\Eloquent\Factories\Factory;

class ZoneFactory extends Factory
{
    protected $model = Zone::class;

    public function definition(): array
    {
        return [
            'name' => 'Zona ' . $this->faker->unique()->numberBetween(1, 10) . ' - ' . $this->faker->word(),
            'area' => 'Ruang ' . $this->faker->company(),
            'is_active' => true,
        ];
    }
}
