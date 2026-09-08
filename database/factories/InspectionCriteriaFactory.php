<?php

namespace Database\Factories;

use App\Domains\VisualBoard\Models\InspectionCriteria;
use App\Domains\VisualBoard\Models\Zone;
use Illuminate\Database\Eloquent\Factories\Factory;

class InspectionCriteriaFactory extends Factory
{
    protected $model = InspectionCriteria::class;

    public function definition(): array
    {
        return [
            'zone_id' => Zone::factory(),
            'item_group' => $this->faker->randomElement(['Meja Kerja', 'Lantai', 'Plafon']),
            'criteria_code' => 'R-' . $this->faker->numberBetween(1, 3),
            'description' => $this->faker->sentence(),
            'is_active' => true,
        ];
    }
}
