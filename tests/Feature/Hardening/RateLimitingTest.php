<?php

namespace Tests\Feature\Hardening;

use App\Domains\Core\Models\User;
use App\Domains\HR\Models\Employee;
use App\Repositories\Contracts\EmployeeAttendanceRepositoryInterface;
use App\Repositories\Contracts\EmployeeRepositoryInterface;
use App\Services\HR\AttendanceService;
use DomainException;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Mockery;

beforeEach(function () {
    foreach ([
        'kiosk-scan-ip:10.10.20.1',
        'kiosk-scan-id:10.10.20.1|EMP-RL-001',
        'kiosk-scan-id:10.10.20.1|EMP-RL-002',
        'kiosk-read:10.10.20.9',
    ] as $key) {
        RateLimiter::clear($key);
    }
});

test('kiosk attendance scan enforces per-identity throttle without locking out shared lobby terminal', function () {
    $emp1 = Employee::factory()->create(['namecode' => 'EMP-RL-001']);
    $emp2 = Employee::factory()->create(['namecode' => 'EMP-RL-002']);

    $serverVars = ['REMOTE_ADDR' => '10.10.20.1'];

    $first = $this->withServerVariables($serverVars)
        ->postJson('/api/v1/hr/kiosk/attendances/scan', [
            'namecode' => $emp1->namecode,
            'status' => 'present',
        ]);

    $first->assertCreated()
        ->assertJsonPath('success', true)
        ->assertJsonPath('data.status', 'present');

    // Duplicate scans on the same day return 422 cleanly (up to per-identity burst allowance of 6/min)
    for ($attempt = 2; $attempt <= 6; $attempt++) {
        $duplicate = $this->withServerVariables($serverVars)
            ->postJson('/api/v1/hr/kiosk/attendances/scan', [
                'namecode' => $emp1->namecode,
                'status' => 'present',
            ]);

        $duplicate->assertStatus(422)
            ->assertJsonPath('success', false);
    }

    // 7th rapid scan for the same employee triggers 429 RATE_LIMIT_EXCEEDED with Retry-After headers
    $throttled = $this->withServerVariables($serverVars)
        ->postJson('/api/v1/hr/kiosk/attendances/scan', [
            'namecode' => $emp1->namecode,
            'status' => 'present',
        ]);

    $throttled->assertStatus(429)
        ->assertHeader('Retry-After')
        ->assertHeader('X-RateLimit-Limit')
        ->assertJsonPath('success', false)
        ->assertJsonPath('meta.code', 'RATE_LIMIT_EXCEEDED');

    // Another employee scanning immediately from the exact same shared kiosk IP succeeds cleanly
    $secondEmployee = $this->withServerVariables($serverVars)
        ->postJson('/api/v1/hr/kiosk/attendances/scan', [
            'namecode' => $emp2->namecode,
            'status' => 'present',
        ]);

    $secondEmployee->assertCreated()
        ->assertJsonPath('success', true);
});

test('kiosk attendance scan rejects inactive employees cleanly', function () {
    $inactive = Employee::factory()->create([
        'namecode' => 'EMP-INACT-01',
        'is_active' => false,
    ]);

    $this->postJson('/api/v1/hr/kiosk/attendances/scan', [
        'namecode' => $inactive->namecode,
        'status' => 'present',
    ])
        ->assertStatus(422)
        ->assertJsonPath('success', false);
});

test('attendance service translates database unique race collision into clean domain exception', function () {
    $employee = Employee::factory()->create([
        'namecode' => 'EMP-RACE-01',
        'is_active' => true,
    ]);

    $partialService = Mockery::mock(AttendanceService::class, [
        app(EmployeeAttendanceRepositoryInterface::class),
        app(EmployeeRepositoryInterface::class),
    ])->makePartial();

    $partialService->shouldReceive('recordAttendance')
        ->once()
        ->andThrow(new UniqueConstraintViolationException('pgsql', 'insert into employee_attendances ...', [], new \PDOException('UNIQUE constraint failed')));

    expect(fn () => $partialService->processScan($employee->namecode, 'present'))
        ->toThrow(DomainException::class, 'Data kehadiran Anda untuk hari ini sudah tercatat sebelumnya. Terima kasih!');
});

test('public kiosk read endpoints expose kiosk-read rate limit headers', function () {
    $serverVars = ['REMOTE_ADDR' => '10.10.20.9'];

    $this->withServerVariables($serverVars)
        ->getJson('/api/v1/visual-board/kiosk')
        ->assertOk()
        ->assertHeader('X-RateLimit-Limit', '120');

    $this->withServerVariables($serverVars)
        ->getJson('/api/v1/portal/kiosk/bulletins')
        ->assertOk()
        ->assertHeader('X-RateLimit-Limit', '120');

    $this->withServerVariables($serverVars)
        ->getJson('/api/v1/hr/kiosk/attendance-summary')
        ->assertOk()
        ->assertHeader('X-RateLimit-Limit', '120');
});

test('authenticated pin change endpoint enforces dedicated pin-change limiter', function () {
    $user = User::factory()->create();
    Employee::factory()->create([
        'user_id' => $user->id,
        'pin' => Hash::make('918273'),
        'is_active' => true,
    ]);

    RateLimiter::clear('pin-change:' . $user->id);

    $response = $this->actingAs($user)
        ->postJson('/api/v1/auth/pin/change', [
            'current_pin' => '918273',
            'new_pin' => '482916',
            'new_pin_confirmation' => '482916',
        ]);

    $response->assertOk()
        ->assertHeader('X-RateLimit-Limit', '10');
});
