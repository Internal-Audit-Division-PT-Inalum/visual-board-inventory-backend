<?php

use App\Domains\Core\Models\User;
use Illuminate\Support\Facades\Route;
use Laravel\Sanctum\Sanctum;

test('token can be created for user with ulid', function () {
    $user = User::factory()->create();

    $token = $user->createToken('test-token');

    expect($token->plainTextToken)->not->toBeEmpty();
    expect($user->tokens()->count())->toBe(1);
});

test('user can authenticate using sanctum token', function () {
    $user = User::factory()->create();

    Route::get('/api/test-auth', function () {
        return response()->json(['message' => 'authenticated']);
    })->middleware('auth:sanctum');

    Sanctum::actingAs($user, ['*']);

    $response = $this->getJson('/api/test-auth');

    $response->assertStatus(200)
        ->assertJson(['message' => 'authenticated']);
});
