<?php

use App\Domains\Core\Models\User;
use App\Domains\VisualBoard\Models\InspectionCriteria;
use App\Domains\VisualBoard\Models\MonthlySchedule;
use App\Domains\VisualBoard\Models\ScheduleRecord;
use App\Domains\VisualBoard\Models\Zone;

beforeEach(function () {
    $this->user = User::factory()->create(['id' => 1]);
    $this->zone = Zone::factory()->create(['name' => 'Zona 1 - Produksi']);

    $this->criteria = InspectionCriteria::factory()->create([
        'zone_id' => $this->zone->id,
        'criteria_code' => 'R-1',
    ]);

    $this->schedule = MonthlySchedule::factory()->create([
        'zone_id' => $this->zone->id,
        'period_month' => now()->startOfMonth(),
    ]);

    $this->record = ScheduleRecord::factory()->create([
        'monthly_schedule_id' => $this->schedule->id,
        'inspection_criteria_id' => $this->criteria->id,
        'days_data' => [],
    ]);
});

it('prevents unauthenticated users from accessing the schedule', function () {
    $this->getJson("/api/v1/visual-board/schedules/{$this->schedule->id}")
        ->assertStatus(401);
});

it('fetches schedule details with standard enterprise JSON structure', function () {
    $this->actingAs($this->user)
        ->getJson("/api/v1/visual-board/schedules/{$this->schedule->id}")
        ->assertStatus(200)
        ->assertJsonPath('success', true)
        ->assertJsonPath('data.zone.name', 'Zona 1 - Produksi')
        ->assertJsonStructure([
            'data' => [
                'id',
                'period_month',
                'status',
                'approval_data',
                'zone' => ['id', 'name', 'area', 'pic_utama'],
                'records' => [
                    '*' => [
                        'record_id',
                        'criteria_group',
                        'criteria_code',
                        'criteria_desc',
                        'days_data',
                    ],
                ],
            ],
        ]);
});

it('updates daily 5R status safely and prevents race conditions', function () {
    $this->actingAs($this->user)
        ->patchJson("/api/v1/visual-board/schedule-records/{$this->record->id}/update-day", [
            'day' => 15,
            'status' => 'ok_5r',
        ])
        ->assertStatus(200)
        ->assertJsonPath('success', true)
        ->assertJsonPath('message', 'Status harian 5R berhasil diperbarui.');

    expect(ScheduleRecord::find($this->record->id)->days_data['15'])
        ->toBe('ok_5r');
});

it('rejects update request if day or status is out of bounds', function () {
    $this->actingAs($this->user)
        ->patchJson("/api/v1/visual-board/schedule-records/{$this->record->id}/update-day", [
            'day' => 35,
            'status' => 'ok',
        ])
        ->assertStatus(422)
        ->assertJsonValidationErrors(['day']);

    $this->actingAs($this->user)
        ->patchJson("/api/v1/visual-board/schedule-records/{$this->record->id}/update-day", [
            'day' => 10,
            'status' => 'hacked_status',
        ])
        ->assertStatus(422)
        ->assertJsonValidationErrors(['status']);
});
