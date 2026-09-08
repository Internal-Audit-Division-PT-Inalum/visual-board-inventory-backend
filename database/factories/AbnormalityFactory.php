<?php

namespace Database\Factories;

use App\Domains\Core\Models\User;
use App\Domains\VisualBoard\Models\Abnormality;
use App\Domains\VisualBoard\Models\Zone;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Abnormality>
 */
class AbnormalityFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Abnormality::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'zone_id' => Zone::factory(),
            'monthly_schedule_id' => null,
            'inspection_criteria_id' => null,
            'date_found' => $this->faker->date(),
            'problem_description' => $this->faker->sentence(),
            'countermeasure_plan' => null,
            'countermeasure_actual' => null,
            'pic_id' => null,
            'status' => 'open',
            'progress_percentage' => 0,
            'is_kaizen' => false,
        ];
    }

    /**
     * Indicate that the abnormality is in progress.
     */
    public function inProgress(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'in_progress',
            'progress_percentage' => $this->faker->numberBetween(1, 99),
            'countermeasure_plan' => $this->faker->sentence(),
            'pic_id' => User::factory(),
        ]);
    }

    /**
     * Indicate that the abnormality is resolved.
     */
    public function resolved(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'resolved',
            'progress_percentage' => 100,
            'countermeasure_plan' => $this->faker->sentence(),
            'countermeasure_actual' => $this->faker->sentence(),
            'pic_id' => User::factory(),
        ]);
    }
}
