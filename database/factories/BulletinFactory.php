<?php

namespace Database\Factories;

use App\Domains\Portal\Models\Bulletin;
use Illuminate\Database\Eloquent\Factories\Factory;

class BulletinFactory extends Factory
{
    protected $model = Bulletin::class;

    public function definition(): array
    {
        return [
            'title' => $this->faker->sentence(),
            'content' => $this->faker->paragraphs(3, true),
            'type' => $this->faker->randomElement(['general', 'health_safety']),
            'is_active' => $this->faker->boolean(80),
            'published_at' => $this->faker->optional()->dateTimeBetween('-1 month', '+1 month'),
            'created_by' => \App\Domains\Core\Models\User::factory(),
        ];
    }
}
