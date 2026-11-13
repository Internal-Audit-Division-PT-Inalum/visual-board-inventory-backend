<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeaders
{
    /**
     * Enforces defense-in-depth HTTP security headers tailored separately for
     * JSON API responses (/api/*) and Web/Filament admin surfaces (/admin/*, /).
     */
    public function handle(Request $request, Closure $next): Response
    {
        /** @var Response $response */
        $response = $next($request);

        $response->headers->remove('X-Powered-By');
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set(
            'Referrer-Policy',
            (string) config('security.headers.referrer_policy', 'strict-origin-when-cross-origin')
        );
        $response->headers->set(
            'Permissions-Policy',
            (string) config(
                'security.headers.permissions_policy',
                'camera=(), geolocation=(), microphone=(), payment=(), usb=(), display-capture=()'
            )
        );

        if ($this->shouldSendHsts($request)) {
            $maxAge = max(0, (int) config('security.headers.hsts_max_age', 31536000));
            $response->headers->set('Strict-Transport-Security', "max-age={$maxAge}; includeSubDomains");
        }

        if ($request->is('api/*')) {
            $response->headers->set('X-Frame-Options', 'DENY');
            $this->applyCsp($response, $this->buildApiCsp());

            return $response;
        }

        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');
        $response->headers->set('Cross-Origin-Opener-Policy', 'same-origin');
        $this->applyCsp($response, $this->buildWebCsp());

        return $response;
    }

    private function shouldSendHsts(Request $request): bool
    {
        if (! (bool) config('security.headers.hsts_enabled', true)) {
            return false;
        }

        if ($request->isSecure() || (bool) config('security.force_https', false)) {
            return true;
        }

        return app()->environment('production')
            && str_starts_with((string) config('app.url', ''), 'https://');
    }

    private function applyCsp(Response $response, string $policy): void
    {
        if (! (bool) config('security.headers.csp_enabled', true)) {
            return;
        }

        $headerName = (bool) config('security.headers.csp_report_only', false)
            ? 'Content-Security-Policy-Report-Only'
            : 'Content-Security-Policy';

        $response->headers->set($headerName, $policy);
    }

    private function buildApiCsp(): string
    {
        return "default-src 'none'; frame-ancestors 'none'; base-uri 'none'; form-action 'none'";
    }

    private function buildWebCsp(): string
    {
        /** @var array<int, string> $extraConnectSrc */
        $extraConnectSrc = config('security.headers.web_extra_connect_src', []);
        $connectSrc = array_merge(["'self'"], $extraConnectSrc);

        if (app()->isLocal()) {
            $connectSrc = array_merge($connectSrc, [
                'http://localhost:*',
                'ws://localhost:*',
                'http://127.0.0.1:*',
                'ws://127.0.0.1:*',
            ]);
        }

        $directives = [
            "default-src 'self'",
            "base-uri 'self'",
            "object-src 'none'",
            "frame-ancestors 'self'",
            "form-action 'self'",
            "img-src 'self' data: blob:",
            "font-src 'self' https://fonts.bunny.net data:",
            "style-src 'self' 'unsafe-inline' https://fonts.bunny.net",
            "script-src 'self' 'unsafe-inline' 'unsafe-eval'",
            'connect-src ' . implode(' ', array_unique($connectSrc)),
        ];

        return implode('; ', $directives);
    }
}
