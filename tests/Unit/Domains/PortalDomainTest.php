<?php

namespace Tests\Unit\Domains;

use App\Domains\Core\Models\User;
use App\Domains\Portal\Models\Bulletin;
use App\Services\Portal\BulletinService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

test('it can create a bulletin using service', function () {
    $user = User::factory()->create();
    $service = app(BulletinService::class);

    $bulletin = $service->createBulletin([
        'title' => 'Safety First 2026',
        'content' => 'Please wear helmets.',
        'type' => 'health_safety',
        'is_active' => true,
        'created_by' => $user->id,
    ]);

    expect($bulletin)->toBeInstanceOf(Bulletin::class)
        ->and($bulletin->title)->toBe('Safety First 2026')
        ->and($bulletin->type)->toBe('health_safety')
        ->and($bulletin->author->id)->toBe($user->id);
});
