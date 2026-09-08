<?php

use App\Domains\Core\Models\User;
use App\Domains\VisualBoard\Models\Abnormality;
use App\Domains\VisualBoard\Models\Zone;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->zone = Zone::factory()->create();
    $this->abnormality = Abnormality::factory()->create([
        'zone_id' => $this->zone->id,
        'problem_description' => 'Test Problem',
    ]);
});

it('can list abnormalities', function () {
    $response = $this->actingAs($this->user)->getJson('/api/v1/visual-board/abnormalities');

    $response->assertStatus(200)
        ->assertJsonStructure([
            'data' => [
                '*' => [
                    'id',
                    'zone_id',
                    'problem_description',
                    'status',
                ],
            ],
        ]);
});

it('can create abnormality', function () {
    $data = [
        'zone_id' => $this->zone->id,
        'date_found' => '2023-10-01',
        'problem_description' => 'New Problem found',
    ];

    $response = $this->actingAs($this->user)->postJson('/api/v1/visual-board/abnormalities', $data);

    $response->assertStatus(201)
        ->assertJsonPath('data.problem_description', 'New Problem found')
        ->assertJsonPath('data.status', 'open')
        ->assertJsonPath('data.progress_percentage', 0);

    $this->assertDatabaseHas('abnormalities', [
        'problem_description' => 'New Problem found',
    ]);
});

it('can update abnormality progress to in_progress', function () {
    $data = [
        'progress_percentage' => 50,
        'countermeasure_actual' => 'Fixing partially',
    ];

    $response = $this->actingAs($this->user)->patchJson("/api/v1/visual-board/abnormalities/{$this->abnormality->id}/progress", $data);

    $response->assertStatus(200)
        ->assertJsonPath('data.progress_percentage', 50)
        ->assertJsonPath('data.status', 'in_progress')
        ->assertJsonPath('data.pic_id', (string) $this->user->id); // Testing auto assign PIC

    $this->assertDatabaseHas('abnormalities', [
        'id' => $this->abnormality->id,
        'status' => 'in_progress',
        'progress_percentage' => 50,
    ]);
});

it('can update abnormality progress to resolved', function () {
    $data = [
        'progress_percentage' => 100,
        'countermeasure_actual' => 'Fixed completely',
    ];

    $response = $this->actingAs($this->user)->patchJson("/api/v1/visual-board/abnormalities/{$this->abnormality->id}/progress", $data);

    $response->assertStatus(200)
        ->assertJsonPath('data.progress_percentage', 100)
        ->assertJsonPath('data.status', 'resolved');

    $this->assertDatabaseHas('abnormalities', [
        'id' => $this->abnormality->id,
        'status' => 'resolved',
        'progress_percentage' => 100,
    ]);
});

it('can delete abnormality', function () {
    $response = $this->actingAs($this->user)->deleteJson("/api/v1/visual-board/abnormalities/{$this->abnormality->id}");

    $response->assertStatus(200);

    $this->assertSoftDeleted('abnormalities', [
        'id' => $this->abnormality->id,
    ]);
});
