<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureDateHeader
{
    /**
     * Memastikan header 'Date' berstandar RFC 7231 selalu hadir pada setiap respons HTTP API.
     * Header ini menjadi jangkar waktu absolut bagi PWA offline frontend untuk mengoreksi
     * pergeseran jam perangkat (clock skew) dan memvalidasi timestamp transaksi.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if (! $response->headers->has('Date')) {
            $response->headers->set('Date', gmdate('D, d M Y H:i:s') . ' GMT');
        }

        return $response;
    }
}
