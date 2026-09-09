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

        // ESUBIZ_CORE_PERMISSION_MIDDLEWARE_ALIAS_RUNTIME_V2
        $middleware->alias([
            'core.permission' =>
                \App\Http\Middleware\CorePermissionMiddleware::class,
        ]);

            /*
             * ESUBIZ_CORE_PERMISSION_MIDDLEWARE_ALIAS_V1
             *
             * Universal Core feature/action authorization.
             *
             * Example:
             * ->middleware('core.permission:users.edit')
             */
            $middleware->alias([
                'core.permission' =>
                    \App\Http\Middleware\CorePermissionMiddleware::class,
            ]);


        $middleware->alias([
            'permission' => \App\Http\Middleware\CheckPermission::class,
            'account-mode' => \App\Http\Middleware\CheckAccountMode::class,

            'website-tenant' =>
                \App\Http\Middleware\ResolveWebsiteTenant::class,

            /*
             * ESUBIZ_UNIVERSAL_ENTITLEMENT_MIDDLEWARE_ALIAS_V1
             *
             * Shared server-side entitlement enforcement for SaaS
             * and off-server/API Core requests.
             *
             * Usage:
             * core.entitlement:capability_key,feature
             * core.entitlement:capability_key,allocation
             */
            'core.entitlement' =>
                \App\Http\Middleware\EnforceCoreEntitlement::class,
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
