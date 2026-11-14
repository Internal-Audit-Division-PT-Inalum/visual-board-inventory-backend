<?php

namespace Tests\Feature\Hardening;

use App\Domains\Core\Models\User;
use App\Domains\Inventory\Models\Location;
use Illuminate\Support\Facades\Config;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'web']);
});

test('api responses include strict security headers without breaking date header', function () {
    $response = $this->getJson('/api/v1/inventory/kiosk/locations');

    $response->assertOk()
        ->assertHeader('Date')
        ->assertHeader('X-Content-Type-Options', 'nosniff')
        ->assertHeader('X-Frame-Options', 'DENY')
        ->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin');

    $csp = (string) $response->headers->get('Content-Security-Policy');
    expect($csp)
        ->toContain("default-src 'none'")
        ->toContain("frame-ancestors 'none'")
        ->toContain("base-uri 'none'")
        ->toContain("form-action 'none'");

    expect((string) $response->headers->get('Permissions-Policy'))
        ->toContain('camera=()')
        ->toContain('microphone=()');
});

test('api 404 responses still carry strict security headers', function () {
    $response = $this->getJson('/api/v1/inventory/non-existent-endpoint');

    $response->assertNotFound()
        ->assertHeader('X-Content-Type-Options', 'nosniff')
        ->assertHeader('X-Frame-Options', 'DENY')
        ->assertHeader('Content-Security-Policy');
});

test('filament web and print routes receive web-compatible security headers', function () {
    // Isolasi pengujian header HTTP dari keberadaan artefak build Vite lokal (public/build/manifest.json)
    $this->withoutVite();

    $loginResponse = $this->get('/admin/login');
    $loginResponse->assertOk()
        ->assertHeader('X-Content-Type-Options', 'nosniff')
        ->assertHeader('X-Frame-Options', 'SAMEORIGIN')
        ->assertHeader('Cross-Origin-Opener-Policy', 'same-origin');

    $webCsp = (string) $loginResponse->headers->get('Content-Security-Policy');
    expect($webCsp)
        ->toContain("default-src 'self'")
        ->toContain("object-src 'none'")
        ->toContain("frame-ancestors 'self'")
        ->toContain('https://fonts.bunny.net');

    $admin = User::factory()->create();
    $admin->assignRole('super_admin');
    $location = Location::factory()->create(['code' => 'RAK-HDR-01']);

    $printResponse = $this->actingAs($admin)
        ->get(route('filament.admin.print.locations', ['ids' => $location->id]));

    $printResponse->assertOk()
        ->assertHeader('X-Content-Type-Options', 'nosniff')
        ->assertHeader('X-Frame-Options', 'SAMEORIGIN');
});

test('hsts header is emitted only when https is active or forced by security config', function () {
    Config::set('security.force_https', false);
    Config::set('app.url', 'http://localhost');

    $plainHttp = $this->getJson('/api/v1/inventory/kiosk/locations');
    expect($plainHttp->headers->has('Strict-Transport-Security'))->toBeFalse();

    Config::set('security.force_https', true);

    $forcedHttps = $this->getJson('/api/v1/inventory/kiosk/locations');
    $forcedHttps->assertHeader('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
});

test('trusted proxy configuration resolves real client ip only from loopback reverse proxies', function () {
    expect(config('security.trusted_proxies'))->toBe(['127.0.0.1', '::1']);

    $untrustedAttempt = $this->withServerVariables([
        'REMOTE_ADDR' => '198.51.100.77',
    ])->withHeaders([
        'X-Forwarded-For' => '203.0.113.9',
    ])->get('/up');

    $untrustedAttempt->assertOk();
    expect(request()->ip())->toBe('198.51.100.77');

    $trustedLoopback = $this->withServerVariables([
        'REMOTE_ADDR' => '127.0.0.1',
    ])->withHeaders([
        'X-Forwarded-For' => '203.0.113.9',
    ])->get('/up');

    $trustedLoopback->assertOk();
    expect(request()->ip())->toBe('203.0.113.9');
});
