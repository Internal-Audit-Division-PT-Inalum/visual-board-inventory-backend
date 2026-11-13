<?php

$trustedProxiesRaw = trim((string) env('TRUSTED_PROXIES', '127.0.0.1,::1'));

return [
    'force_https' => (bool) env('APP_FORCE_HTTPS', false),

    'trusted_proxies' => $trustedProxiesRaw === '*'
        ? '*'
        : array_values(array_filter(array_map('trim', explode(',', $trustedProxiesRaw)))),

    'headers' => [
        'hsts_enabled' => (bool) env('SECURITY_HSTS_ENABLED', true),
        'hsts_max_age' => (int) env('SECURITY_HSTS_MAX_AGE', 31536000),
        'csp_enabled' => (bool) env('SECURITY_CSP_ENABLED', true),
        'csp_report_only' => (bool) env('SECURITY_CSP_REPORT_ONLY', false),
        'permissions_policy' => (string) env(
            'SECURITY_PERMISSIONS_POLICY',
            'camera=(), geolocation=(), microphone=(), payment=(), usb=(), display-capture=()'
        ),
        'referrer_policy' => (string) env('SECURITY_REFERRER_POLICY', 'strict-origin-when-cross-origin'),
        'web_extra_connect_src' => array_values(array_filter(array_map(
            'trim',
            explode(',', (string) env('SECURITY_WEB_CONNECT_SRC', ''))
        ))),
    ],
];
