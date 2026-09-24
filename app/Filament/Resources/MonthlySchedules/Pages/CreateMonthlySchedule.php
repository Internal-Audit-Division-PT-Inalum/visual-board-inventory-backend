<?php

namespace App\Filament\Resources\MonthlySchedules\Pages;

use App\Domains\VisualBoard\Models\InspectionCriteria;
use App\Domains\VisualBoard\Models\ScheduleRecord;
use App\Filament\Resources\MonthlySchedules\MonthlyScheduleResource;
use Filament\Resources\Pages\CreateRecord;

class CreateMonthlySchedule extends CreateRecord
{
    protected static string $resource = MonthlyScheduleResource::class;

    protected function afterCreate(): void
    {
        $monthlySchedule = $this->record;

        $activeCriteria = InspectionCriteria::where('zone_id', $monthlySchedule->zone_id)
            ->where('is_active', true)
            ->get();

        foreach ($activeCriteria as $criteria) {
            ScheduleRecord::create([
                'monthly_schedule_id' => $monthlySchedule->id,
                'inspection_criteria_id' => $criteria->id,
                'days_data' => [],
            ]);
        }
    }
}
