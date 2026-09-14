<?php

namespace Database\Factories;

use App\Domains\VisualBoard\Models\SchedulePic;
use App\Domains\VisualBoard\Models\MonthlySchedule;
use App\Domains\HR\Models\Employee;
use Illuminate\Database\Eloquent\Factories\Factory;

class SchedulePicFactory extends Factory
{
    protected $model = SchedulePic::class;

    public function definition(): array
    {
        return [
            'monthly_schedule_id' => MonthlySchedule::factory(),
            'pic_id' => \App\Domains\Core\Models\User::factory(),
            'date' => $this->faker->date(),
            'pic_role' => $this->faker->randomElement(['utama', 'pengganti']),
        ];
    }
}
