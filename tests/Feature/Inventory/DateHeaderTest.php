<?php

test('api responses always contain rfc 7231 date header for client clock skew correction (Task B0.2.7)', function () {
    $response = $this->getJson('/api/v1/inventory/kiosk/locations');

    $response->assertHeader('Date');
    $dateHeader = $response->headers->get('Date');

    expect($dateHeader)->not->toBeNull();
    $timestamp = strtotime($dateHeader);
    expect($timestamp)->not->toBeFalse();
    // Header Date harus mendekati waktu sistem saat ini (+/- 5 detik)
    expect(abs(time() - $timestamp))->toBeLessThanOrEqual(5);
});
