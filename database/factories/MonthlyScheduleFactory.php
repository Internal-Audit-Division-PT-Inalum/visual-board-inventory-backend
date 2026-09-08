<?php

namespace Database\Factories;

use App\Domains\VisualBoard\Models\MonthlySchedule;
use App\Domains\VisualBoard\Models\Zone;
use Illuminate\Database\Eloquent\Factories\Factory;

class MonthlyScheduleFactory extends Factory
{
    protected $model = MonthlySchedule::class;

    public function definition(): array
    {
        return [
            'zone_id' => Zone::factory(),
            'period_month' => now()->startOfMonth(),
            'status' => 'draft',
            'approval_data' => [],
        ];
    }
}
