<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Trust all proxies (Railway, Cloudflare, etc.)
        $middleware->trustProxies(at: '*');

        $middleware->web(append: [
            \App\Http\Middleware\HandleInertiaRequests::class,
            \App\Http\Middleware\CheckLocale::class,
            \App\Http\Middleware\TrackVisitors::class,
            \App\Http\Middleware\CheckBlockedUser::class,
        ]);

        $middleware->alias([
            'permission' => \App\Http\Middleware\CheckPermission::class,
            'readonly' => \App\Http\Middleware\CheckReadOnly::class,
            'menu.access' => \App\Http\Middleware\CheckMenuAccess::class,
            'blocked.admin' => \App\Http\Middleware\CheckBlockedAdmin::class,
        ]);


        $middleware->validateCsrfTokens(except: [
            'payment/notification',
            'gallery/*/track-view',
            'articles/views/*/time-spent',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Handle 419 CSRF token mismatch dengan response yang lebih user-friendly
        $exceptions->render(function (\Illuminate\Session\TokenMismatchException $e, $request) {
            if ($request->expectsJson() || $request->header('X-Inertia')) {
                return response()->json([
                    'message' => 'Token keamanan kadaluarsa. Halaman akan dimuat ulang.',
                ], 419);
            }

            // Redirect back with error message untuk non-AJAX requests
            return redirect()->back()->with('error', 'Token keamanan kadaluarsa. Silakan coba lagi.');
        });
    })->create();
