<?php

use App\Domains\Core\Models\User;
use App\Domains\HR\Models\Employee;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\Sanctum;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->employee = Employee::factory()->create([
        'user_id' => $this->user->id,
        'namecode' => 'EMP-TEST',
        'pin' => Hash::make('123456'),
        'must_change_pin' => true,
    ]);
});

it('prevents changing pin to a weak pin', function () {
    Sanctum::actingAs($this->user, ['*']);

    $response = $this->postJson('/api/v1/auth/pin/change', [
        'current_pin' => '123456',
        'new_pin' => '111111',
        'new_pin_confirmation' => '111111',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['new_pin']);
});

it('can change pin to a strong pin', function () {
    Sanctum::actingAs($this->user, ['*']);

    $response = $this->postJson('/api/v1/auth/pin/change', [
        'current_pin' => '123456',
        'new_pin' => '982736',
        'new_pin_confirmation' => '982736',
    ]);

    $response->assertStatus(200);
    $this->employee->refresh();
    expect($this->employee->must_change_pin)->toBeFalse();
    expect(Hash::check('982736', $this->employee->pin))->toBeTrue();
});
