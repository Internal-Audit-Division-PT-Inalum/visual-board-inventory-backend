<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Domains\VisualBoard\Models\Zone;
use App\Domains\VisualBoard\Models\InspectionCriteria;
use App\Domains\VisualBoard\Models\MonthlySchedule;
use App\Domains\VisualBoard\Models\ScheduleRecord;
use App\Domains\VisualBoard\Models\SchedulePic;
use App\Domains\VisualBoard\Models\Abnormality;

class VisualBoardSeeder extends Seeder
{
    public function run(): void
    {
        Zone::factory(3)->create()->each(function (Zone $zone) {
            InspectionCriteria::factory(5)->create(['zone_id' => $zone->id]);
            
            $monthlySchedule = MonthlySchedule::factory()->create(['zone_id' => $zone->id]);
            
            SchedulePic::factory()->create(['monthly_schedule_id' => $monthlySchedule->id, 'date' => now()->subDays(1)]);
            SchedulePic::factory()->create(['monthly_schedule_id' => $monthlySchedule->id, 'date' => now()->subDays(2)]);
            
            ScheduleRecord::factory(10)->create(['monthly_schedule_id' => $monthlySchedule->id])->each(function (ScheduleRecord $record) use ($zone) {
                Abnormality::factory(2)->create([
                    'zone_id' => $zone->id,
                    'date_found' => now()->subDays(rand(1, 28)),
                ]);
            });
        });
    }
}
