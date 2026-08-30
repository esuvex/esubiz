<?php

namespace App\Http\Middleware;

use App\Models\Website;
use App\Services\Website\TenantBandwidthUsageService;
use Spatie\Multitenancy\Models\Tenant;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnforceTenantBandwidthLimit
{
    /*
     * ESUBIZ_TENANT_BANDWIDTH_ENFORCEMENT_V1
     *
     * Monthly bandwidth policy:
     *
     * < 80%  = normal
     * >= 80% = warning
     * >= 90% = critical
     * >=100% = public website blocked
     *
     * /admin always remains accessible so the website owner
     * can manage the website and purchase additional bandwidth.
     */
    public function handle(
        Request $request,
        Closure $next
    ): Response {
        /*
         * Never block tenant administration.
         */
        if (
            $request->is('admin')
            || $request->is('admin/*')
        ) {
            return $next($request);
        }

        try {
            /** @var \App\Models\WebsiteTenant|null $tenant */
            $tenant =
                Tenant::current();

            if (!$tenant) {
                return $next($request);
            }

            $website =
                Website::query()
                    ->find(
                        $tenant->website_id
                    );

            if (!$website) {
                return $next($request);
            }

            $usage =
                app(
                    TenantBandwidthUsageService::class
                )->currentMonth(
                    $website
                );

            $percentage =
                (float) (
                    $usage['percentage']
                    ?? 0
                );

            /*
             * At 100% the public website stops transferring
             * additional tenant content.
             */
            if ($percentage >= 100) {
                return response(
                    '<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Bandwidth Limit Reached</title>
<style>
body{margin:0;font-family:Arial,sans-serif;background:#f8fafc;color:#0f172a;display:flex;min-height:100vh;align-items:center;justify-content:center}
main{width:min(560px,calc(100% - 40px));background:#fff;border:1px solid #e2e8f0;border-radius:16px;padding:40px;text-align:center;box-shadow:0 10px 30px rgba(15,23,42,.08)}
h1{margin:0 0 12px;font-size:28px}
p{margin:0;color:#64748b;line-height:1.65}
</style>
</head>
<body>
<main>
<h1>Bandwidth Limit Reached</h1>
<p>This website has reached its monthly bandwidth allowance. Please check back later.</p>
</main>
</body>
</html>',
                    509,
                    [
                        'Content-Type' =>
                            'text/html; charset=UTF-8',

                        'Cache-Control' =>
                            'no-store, no-cache, must-revalidate',
                    ]
                );
            }

            /*
             * Make warning state available to downstream
             * tenant views without changing their layouts.
             */
            if ($percentage >= 90) {
                $request->attributes->set(
                    'esubiz_bandwidth_status',
                    'critical'
                );
            } elseif ($percentage >= 80) {
                $request->attributes->set(
                    'esubiz_bandwidth_status',
                    'warning'
                );
            } else {
                $request->attributes->set(
                    'esubiz_bandwidth_status',
                    'normal'
                );
            }

            $request->attributes->set(
                'esubiz_bandwidth_percentage',
                $percentage
            );
        } catch (\Throwable $e) {
            /*
             * A metering/enforcement failure must not cause
             * an otherwise healthy tenant website to fail.
             */
        }

        return $next($request);
    }
}
