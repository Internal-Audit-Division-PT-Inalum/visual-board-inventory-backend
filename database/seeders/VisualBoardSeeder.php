<?php

namespace Database\Seeders;

use App\Domains\HR\Models\Employee;
use App\Domains\Inventory\Models\Item;
use App\Domains\VisualBoard\Models\Abnormality;
use App\Domains\VisualBoard\Models\InspectionCriteria;
use App\Domains\VisualBoard\Models\MonthlySchedule;
use App\Domains\VisualBoard\Models\SchedulePic;
use App\Domains\VisualBoard\Models\ScheduleRecord;
use App\Domains\VisualBoard\Models\Workstation;
use App\Domains\VisualBoard\Models\Zone;
use Illuminate\Database\Seeder;

class VisualBoardSeeder extends Seeder
{
    public function run(): void
    {
        Zone::factory(2)->create()->each(function (Zone $zone) {
            InspectionCriteria::factory(3)->create(['zone_id' => $zone->id]);

            // Create workstations for this zone
            Workstation::factory(3)->create([
                'zone_id' => $zone->id,
                'employee_id' => Employee::inRandomOrder()->first()->id ?? null,
            ])->each(function (Workstation $workstation) {
                // Attach 2 random items as standard ATK
                $items = Item::inRandomOrder()->limit(2)->get();
                foreach ($items as $item) {
                    $workstation->items()->attach($item->id, ['standard_quantity' => rand(1, 3)]);
                }
            });

            $monthlySchedule = MonthlySchedule::factory()->create(['zone_id' => $zone->id]);

            SchedulePic::factory()->create(['monthly_schedule_id' => $monthlySchedule->id, 'date' => now()->subDays(1)]);
            SchedulePic::factory()->create(['monthly_schedule_id' => $monthlySchedule->id, 'date' => now()->subDays(2)]);

            ScheduleRecord::factory(5)->create(['monthly_schedule_id' => $monthlySchedule->id])->each(function (ScheduleRecord $record) use ($zone) {
                Abnormality::factory(1)->create([
                    'zone_id' => $zone->id,
                    'date_found' => now()->subDays(rand(1, 28)),
                ]);
            });
        });
    }
}
