<?php

namespace Tests\Feature\Api\VisualBoard;

use App\Domains\HR\Models\Employee;
use App\Domains\VisualBoard\Models\Abnormality;
use App\Domains\VisualBoard\Models\Zone;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;

beforeEach(function () {
    Cache::flush();

    $this->pic1 = Employee::factory()->create();
    $this->pic2 = Employee::factory()->create();

    $this->zone1 = Zone::factory()->create([
        'pic_utama_id' => $this->pic1->id,
        'pic_pengganti_id' => $this->pic2->id,
    ]);

    $this->zone2 = Zone::factory()->create();

    Abnormality::factory()->create([
        'zone_id' => $this->zone1->id,
        'status' => 'open',
        'date_found' => Carbon::now(),
    ]);

    Abnormality::factory()->create([
        'zone_id' => $this->zone1->id,
        'status' => 'in_progress',
        'date_found' => Carbon::now(),
    ]);

    Abnormality::factory()->create([
        'zone_id' => $this->zone2->id,
        'status' => 'resolved',
        'date_found' => Carbon::now(),
    ]);
});

it('can fetch kiosk dashboard data with correct structure and aggregation', function () {
    $response = $this->getJson('/api/v1/visual-board/kiosk');

    $response->assertStatus(200)
        ->assertJsonStructure([
            'success',
            'message',
            'data' => [
                'open_abnormality_count',
                'abnormality_resolved_today',
                'abnormality_in_progress',
                'compliance_percentage',
                'compliance_mom_trend',
                'safety_streak_days',
                'safety_safe_shifts',
                'kaizen_implemented_count',
                'kaizen_cost_saving',
                'resolution_speed_avg_mins',
                'audit_pass_percentage',
                'audit_pass_grade',
                'oee_percentage',
                'oee_target',
                'abnormalities' => [
                    '*' => [
                        'id',
                        'zone_name',
                        'description',
                        'status',
                    ],
                ],
                'kaizen_champions',
                'schedule_matrix',
                'weekly_trend',
            ],
        ]);

    $response->assertJsonPath('data.open_abnormality_count', 2)
        ->assertJsonPath('data.abnormality_in_progress', 1)
        ->assertJsonPath('data.abnormality_resolved_today', 1);

    $this->assertCount(2, $response->json('data.abnormalities'));
});

it('caches the kiosk response', function () {
    $response1 = $this->getJson('/api/v1/visual-board/kiosk');
    $response1->assertStatus(200);

    Abnormality::query()->delete();

    $response2 = $this->getJson('/api/v1/visual-board/kiosk');
    $response2->assertStatus(200);
});
