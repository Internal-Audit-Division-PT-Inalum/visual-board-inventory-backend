<?php

use App\Domains\Core\Models\User;
use App\Domains\HR\Models\Employee;
use Illuminate\Support\Facades\Hash;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->employee = Employee::factory()->create([
        'user_id' => $this->user->id,
        'namecode' => 'EMP-TEST',
        'pin' => Hash::make('123456'), // weak pin
        'is_active' => true,
        'must_change_pin' => false,
    ]);
});

it('can login with valid credentials', function () {
    $response = $this->postJson('/api/v1/auth/login', [
        'namecode' => 'EMP-TEST',
        'pin' => '123456',
    ]);

    $response->assertStatus(200)
        ->assertJsonPath('success', true)
        ->assertJsonStructure(['data' => ['token', 'must_change_pin', 'employee']]);
});

it('requires pin change if pin is weak', function () {
    $response = $this->postJson('/api/v1/auth/login', [
        'namecode' => 'EMP-TEST',
        'pin' => '123456',
    ]);

    $response->assertStatus(200)
        ->assertJsonPath('data.must_change_pin', true);
});

it('fails login with invalid pin and increments failed attempts', function () {
    $response = $this->postJson('/api/v1/auth/login', [
        'namecode' => 'EMP-TEST',
        'pin' => 'wrong',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['namecode']);

    $this->employee->refresh();
    expect($this->employee->pin_failed_attempts)->toBe(1);
});

it('locks account after 5 failed attempts', function () {
    for ($i = 0; $i < 5; $i++) {
        $this->postJson('/api/v1/auth/login', [
            'namecode' => 'EMP-TEST',
            'pin' => 'wrong',
        ]);
    }

    $this->employee->refresh();
    expect($this->employee->pin_locked_until)->not->toBeNull();

    $response = $this->postJson('/api/v1/auth/login', [
        'namecode' => 'EMP-TEST',
        'pin' => '123456',
    ]);

    $response->assertStatus(422)
        ->assertJsonPath('errors.namecode.0', 'Akun terkunci sementara. Silakan coba lagi nanti.');
});
