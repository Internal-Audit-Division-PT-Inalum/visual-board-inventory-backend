<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        //
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );

        $exceptions->render(function (\Illuminate\Validation\ValidationException $e, Request $request) {
            if ($request->is('api/*') || $request->expectsJson()) {
                return \App\Shared\Responses\ApiResponse::error(
                    'Validasi gagal.',
                    422,
                    $e->errors()
                );
            }
        });

        $exceptions->render(function (\Illuminate\Database\Eloquent\ModelNotFoundException $e, Request $request) {
            if ($request->is('api/*') || $request->expectsJson()) {
                return \App\Shared\Responses\ApiResponse::error(
                    'Data tidak ditemukan.',
                    404
                );
            }
        });

        $exceptions->render(function (\Symfony\Component\HttpKernel\Exception\NotFoundHttpException $e, Request $request) {
            if ($request->is('api/*') || $request->expectsJson()) {
                return \App\Shared\Responses\ApiResponse::error(
                    'Endpoint atau rute tidak ditemukan.',
                    404
                );
            }
        });

        $exceptions->render(function (\Illuminate\Auth\AuthenticationException $e, Request $request) {
            if ($request->is('api/*') || $request->expectsJson()) {
                return \App\Shared\Responses\ApiResponse::error(
                    'Unauthenticated. Silakan login terlebih dahulu.',
                    401
                );
            }
        });

        $exceptions->render(function (\Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException $e, Request $request) {
            if ($request->is('api/*') || $request->expectsJson()) {
                return \App\Shared\Responses\ApiResponse::error(
                    'Anda tidak memiliki akses ke sumber daya ini.',
                    403
                );
            }
        });

        // Catch-all for API route general exceptions (optional, to hide SQL errors in production)
        $exceptions->render(function (\Throwable $e, Request $request) {
            // Only mask the error if it's production, otherwise let Laravel show the stack trace during development
            if (app()->environment('production') && ($request->is('api/*') || $request->expectsJson())) {
                return \App\Shared\Responses\ApiResponse::error(
                    'Terjadi kesalahan internal server.',
                    500
                );
            }
        });
    })->create();
