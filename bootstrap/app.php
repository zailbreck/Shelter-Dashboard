<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Register middleware aliases
        $middleware->alias([
            'require2fa' => \App\Http\Middleware\Require2FA::class,
            'ensure2fa' => \App\Http\Middleware\Ensure2FASetup::class,
        ]);

        // Ensure web middleware includes CSRF protection
        $middleware->web(append: [
            \App\Http\Middleware\Ensure2FASetup::class,
        ]);

        // Remove default Sanctum auth from API routes
        // Agent API endpoints should be public (HWID-based auth in controllers)
        $middleware->api(remove: [
            \Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
