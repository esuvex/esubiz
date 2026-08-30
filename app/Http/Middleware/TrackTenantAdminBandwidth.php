<?php

namespace App\Http\Middleware;

use App\Models\Website;
use App\Services\Website\TenantBandwidthUsageService;
use App\Tenancy\Tenant;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TrackTenantAdminBandwidth
{
    /*
     * ESUBIZ_TENANT_ADMIN_BANDWIDTH_ACCOUNTING_V1
     *
     * Counts tenant /admin response transfer against the same
     * monthly website bandwidth entitlement.
     *
     * Public HTML, media and theme assets are accounted for by
     * their existing dedicated paths and are not counted here.
     */
    public function handle(
        Request $request,
        Closure $next
    ): Response {
        $response = $next($request);

        if (
            !$request->is('admin')
            && !$request->is('admin/*')
        ) {
            return $response;
        }

        try {
            /** @var \App\Models\WebsiteTenant|null $tenant */
            $tenant = Tenant::current();

            if (!$tenant) {
                return $response;
            }

            $website =
                Website::query()
                    ->find(
                        $tenant->website_id
                    );

            if (!$website) {
                return $response;
            }

            $bytes = 0;

            /*
             * Normal HTML / JSON / redirect responses.
             */
            if (
                method_exists(
                    $response,
                    'getContent'
                )
            ) {
                $content =
                    $response->getContent();

                if (
                    is_string(
                        $content
                    )
                ) {
                    $bytes =
                        strlen(
                            $content
                        );
                }
            }

            /*
             * If a response explicitly declares its size,
             * prefer that when no body size was available.
             */
            if (
                $bytes <= 0
                && $response->headers->has(
                    'Content-Length'
                )
            ) {
                $bytes =
                    (int) $response
                        ->headers
                        ->get(
                            'Content-Length'
                        );
            }

            if ($bytes > 0) {
                app(
                    TenantBandwidthUsageService::class
                )->recordBytes(
                    $website,
                    $bytes,
                    'tenant_admin',
                    null,
                    [
                        'path' =>
                            $request->path(),

                        'method' =>
                            $request->method(),
                    ]
                );
            }
        } catch (\Throwable $e) {
            /*
             * Resource accounting must never make the tenant
             * administration area unavailable.
             */
        }

        return $response;
    }
}
