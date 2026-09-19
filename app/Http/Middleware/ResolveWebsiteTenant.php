<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Spatie\Multitenancy\TenantFinder\TenantFinder;
use Symfony\Component\HttpFoundation\Response;

class ResolveWebsiteTenant
{
    /**
     * Resolve and activate the Esubiz website tenant for the
     * current public tenant request.
     *
     * This works for every current/future *.esubiz.com tenant.
     */
    public function handle(
        Request $request,
        Closure $next
    ): Response {
        $tenant = app(TenantFinder::class)
            ->findForRequest($request);

        abort_unless(
            $tenant,
            404,
            'Website tenant not found.'
        );

        /*
         * This performs the configured Spatie tenant-switch tasks,
         * including changing the tenant database connection.
         */
        $tenant->makeCurrent();

        /*
         * ESUBIZ_CORE_MODULE_RUNTIME_AFTER_TENANT_V1
         *
         * Portable modules are website-scoped, so they must load only
         * after Spatie has established the current WebsiteTenant.
         */
        app(
            \App\Services\Core\Modules\CoreModuleLoader::class
        )->load();

        try {
            return $next($request);
        } finally {
            /*
             * Avoid leaking tenant context into another request,
             * especially important for queues / long-running workers.
             */
            $tenant->forget();
        }
    }
}
