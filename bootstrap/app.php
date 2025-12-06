<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Spatie\Permission\Middleware\RoleMiddleware;
use Spatie\Permission\Middleware\PermissionMiddleware;
use Spatie\Permission\Middleware\RoleOrPermissionMiddleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
    $middleware->web(\App\Http\Middleware\RememberMiddleware::class);
    $middleware->web(\App\Http\Middleware\SetSpatieTeamContext::class);
    $middleware->web(\App\Http\Middleware\SetLocale::class);
    $middleware->web(\App\Http\Middleware\RequireTwoFactor::class);
    $middleware->web(\App\Http\Middleware\EnsureEmailIsVerified::class);
        $middleware->alias([
            'role' => RoleMiddleware::class,
            'permission' => \Spatie\Permission\Middleware\PermissionMiddleware::class,
            'role_or_permission' => RoleOrPermissionMiddleware::class,
        ]);
    // $middleware->encryptCookies(['remember_me']); // Lasciato in chiaro per gestione custom
    })

    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
