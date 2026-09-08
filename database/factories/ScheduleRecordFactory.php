<?php

namespace Database\Factories;

use App\Domains\VisualBoard\Models\InspectionCriteria;
use App\Domains\VisualBoard\Models\MonthlySchedule;
use App\Domains\VisualBoard\Models\ScheduleRecord;
use Illuminate\Database\Eloquent\Factories\Factory;

class ScheduleRecordFactory extends Factory
{
    protected $model = ScheduleRecord::class;

    public function definition(): array
    {
        return [
            'monthly_schedule_id' => MonthlySchedule::factory(),
            'inspection_criteria_id' => InspectionCriteria::factory(),
            'days_data' => [],
        ];
    }
}
