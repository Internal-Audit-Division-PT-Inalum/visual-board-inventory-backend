<?php

namespace Tests\Feature\Api\VisualBoard;

use App\Domains\Core\Models\User;
use App\Domains\VisualBoard\Models\Abnormality;
use App\Domains\VisualBoard\Models\Zone;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;

beforeEach(function () {
    Cache::flush();

    $this->pic1 = User::factory()->create();
    $this->pic2 = User::factory()->create();

    $this->zone1 = Zone::factory()->create([
        'pic_utama_id' => $this->pic1->id,
        'pic_pengganti_id' => $this->pic2->id,
    ]);

    $this->zone2 = Zone::factory()->create();

    // Create abnormalities for trend
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
                'organization_structure' => [
                    '*' => [
                        'id',
                        'name',
                        'pic_utama',
                        'pic_pengganti',
                    ],
                ],
                'abnormality_trend' => [
                    'month',
                    'summary' => [
                        'open',
                        'in_progress',
                        'resolved',
                    ],
                ],
                'open_problems' => [
                    '*' => [
                        'id',
                        'zone',
                        'problem_description',
                        'status',
                    ],
                ],
            ],
        ]);

    // Assert aggregation values
    $response->assertJsonPath('data.abnormality_trend.summary.open', 1)
        ->assertJsonPath('data.abnormality_trend.summary.in_progress', 1)
        ->assertJsonPath('data.abnormality_trend.summary.resolved', 1);

    // Should return 2 open problems (open + in_progress)
    $this->assertCount(2, $response->json('data.open_problems'));
});

it('caches the kiosk response', function () {
    $response1 = $this->getJson('/api/v1/visual-board/kiosk');
    $response1->assertStatus(200);

    // Delete all data to ensure cache is used
    Abnormality::query()->delete();

    $response2 = $this->getJson('/api/v1/visual-board/kiosk');

    // Values should still be 1 (from cache)
    $response2->assertJsonPath('data.abnormality_trend.summary.open', 1);
});
