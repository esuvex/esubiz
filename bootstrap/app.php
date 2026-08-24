<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {

        $middleware->alias([
            'permission' => \App\Http\Middleware\CheckPermission::class,
            'account-mode' => \App\Http\Middleware\CheckAccountMode::class,

            'website-tenant' =>
                \App\Http\Middleware\ResolveWebsiteTenant::class,
        ]);

        /*
         * Tenant-aware routes.
         *
         * NeedsTenant invokes the configured WebsiteTenantFinder
         * and makes the resolved WebsiteTenant current.
         *
         * EnsureValidTenantSession protects tenant sessions from
         * crossing between different tenant websites.
         */
        $middleware->group('tenant', [
            \Spatie\Multitenancy\Http\Middleware\NeedsTenant::class,
            \Spatie\Multitenancy\Http\Middleware\EnsureValidTenantSession::class,
        ]);

        $middleware->validateCsrfTokens(except: [
            'oauth/token',
        ]);

    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
