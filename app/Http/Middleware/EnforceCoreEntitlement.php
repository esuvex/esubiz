<?php

namespace App\Http\Middleware;

use App\Models\Website;
use App\Services\Core\CoreEntitlementService;
use Closure;
use Illuminate\Http\Request;
use RuntimeException;
use Spatie\Multitenancy\Models\Tenant;
use Symfony\Component\HttpFoundation\Response;

class EnforceCoreEntitlement
{
    /*
     * ESUBIZ_UNIVERSAL_CORE_ENTITLEMENT_MIDDLEWARE_V1
     *
     * Universal server-side entitlement gateway.
     *
     * Used by both:
     * - SaaS tenant routes
     * - off-server/API Core requests
     *
     * No Add-on ID, product name or resource name is hardcoded.
     *
     * Feature mode:
     *   capability must be available to the website.
     *
     * Allocation mode:
     *   current usage must remain below the effective Core + Add-on
     *   allocation. Null allocation means unlimited.
     */
    public function handle(
        Request $request,
        Closure $next,
        string $capabilityKey,
        string $mode = 'auto'
    ): Response {
        /*
         * ESUBIZ_ENTITLEMENT_ADMIN_MANAGEMENT_BYPASS_V1
         *
         * Resource exhaustion must never lock the website owner out of
         * the tenant Admin area.
         *
         * Admin must remain available for:
         * - purchasing Add-ons
         * - billing/payment
         * - resource management
         * - configuration
         * - recovery
         *
         * The entitlement is enforced on the consuming/public function,
         * not on the management interface itself.
         */
        $path =
            trim(
                $request->path(),
                '/'
            );

        /*
         * ESUBIZ_ENTITLEMENT_ADMIN_SAFE_METHOD_BYPASS_V1
         *
         * Allocation-backed resources must still enforce consuming
         * Admin writes such as uploads.
         *
         * Read/manage pages remain accessible, and DELETE remains
         * available so an owner can free exhausted resources.
         *
         * Feature locks are never bypassed here.
         */
        $isAdminPath =
            $path === 'admin'
            || str_starts_with(
                $path,
                'admin/'
            )
            || str_contains(
                $path,
                '/admin/'
            );

        $isSafeAllocationMethod =
            in_array(
                strtoupper($request->method()),
                ['GET', 'HEAD', 'OPTIONS', 'DELETE'],
                true
            );

        if (
            $mode === 'allocation'
            && $isAdminPath
            && $isSafeAllocationMethod
        ) {
            return $next(
                $request
            );
        }

        $websiteId =
            $this->resolveWebsiteId(
                $request
            );

        /*
         * A protected tenant/off-server request must identify its website.
         * Failing closed prevents entitlement bypass by omitting website_id.
         */
        if ($websiteId === null) {
            return $this->blocked(
                $request,
                'Website entitlement context could not be resolved.',
                403
            );
        }

        try {
            $entitlements =
                app(
                    CoreEntitlementService::class
                );

            /*
             * ESUBIZ_AUTOMATIC_ENTITLEMENT_MODE_RESOLUTION_V1
             *
             * Normal usage only supplies the capability key:
             *
             * core.entitlement:storage
             * core.entitlement:panorama_360_pro
             *
             * The capability registry decides whether this is a
             * feature lock or allocation-backed entitlement.
             *
             * Explicit feature/allocation remains supported for
             * backwards compatibility.
             */
            if ($mode === 'auto') {
                try {
                    /*
                     * ESUBIZ_UNIVERSAL_ENTITLEMENT_FAMILY_EXECUTION_V1
                     *
                     * SaaS and off-server use the same central capability
                     * classification. Add-ons only provide Function
                     * Allocation; they never choose enforcement code.
                     */
                    $family =
                        app(
                            \App\Services\Core\CoreCapabilityRegistry::class
                        )->enforcementFamily(
                            $capabilityKey
                        );

                    $mode = match ($family) {
                        'unlimited' => 'unlimited',

                        'quantity',
                        'resource' => 'allocation',

                        /*
                         * Credits/accounts/services have their own central
                         * authorities. Until those authorities are connected
                         * to this gateway they remain feature/service access
                         * checks rather than being mistaken for quantities.
                         */
                        /*
                         * ESUBIZ_UNIVERSAL_CREDIT_FAMILY_MODE_V1
                         *
                         * Credit-backed capabilities use the existing
                         * central Esubiz credit authority. Middleware only
                         * performs a balance preflight; the consuming service
                         * remains responsible for the exact idempotent debit.
                         */
                        'credits' => 'credits',

                        /*
                         * Accounts and services remain separate families.
                         * They are not ordinary credit balances.
                         */
                        'accounts' => 'accounts',

                        /*
                         * ESUBIZ_UNIVERSAL_SERVICE_FAMILY_MODE_V1
                         *
                         * Platform-controlled services use the central
                         * service authority rather than ordinary feature
                         * entitlement fallback.
                         */
                        'service' => 'service',

                        default => 'feature',
                    };
                } catch (\Throwable $e) {
                    /*
                     * Child/Pro capabilities may exist in Function
                     * Allocation/core_feature_limits without being a
                     * top-level registry capability.
                     *
                     * Those remain feature locks unless their capability
                     * owner registers another enforcement contract.
                     */
                    $mode = 'feature';
                }
            }

            /*
             * Permanent unlimited Core capabilities never perform a usage
             * check. Their active Core configuration is the authority.
             */
            if ($mode === 'unlimited') {
                return $next($request);
            }

            /*
             * ESUBIZ_UNIVERSAL_CREDIT_PREFLIGHT_V1
             *
             * One central preflight for every credit-backed capability.
             *
             * SaaS and authenticated off-server requests both arrive here
             * with the resolved central website ID.
             *
             * This does NOT debit credits. Exact charging remains owned by
             * CentralServiceCreditConsumptionService::consume() at the
             * actual consuming operation.
             */
            if ($mode === 'credits') {
                $credits =
                    app(
                        \App\Services\CentralApi\CentralServiceCreditConsumptionService::class
                    );

                if (
                    !$credits->has(
                        $websiteId,
                        $capabilityKey,
                        1
                    )
                ) {
                    return $this->blocked(
                        $request,
                        "Insufficient {$capabilityKey} credits.",
                        403
                    );
                }

                return $next($request);
            }

            /*
             * ESUBIZ_UNIVERSAL_ACCOUNT_FAMILY_ENFORCEMENT_V1
             *
             * Account-backed capabilities resolve their account child
             * dynamically from Core configuration.
             *
             * Effective allowance:
             * Core account default + active Add-on Function Allocations.
             *
             * Actual account usage must come from the central trusted usage
             * authority. Ordinary request input is never accepted as usage.
             */
            if ($mode === 'accounts') {
                $registry =
                    app(
                        \App\Services\Core\CoreCapabilityRegistry::class
                    );

                $account =
                    $registry->accountAllocationCapability(
                        $capabilityKey
                    );

                if (!$account) {
                    return $this->blocked(
                        $request,
                        "Account entitlement for [{$capabilityKey}] "
                        . 'could not be resolved.',
                        403
                    );
                }

                /*
                 * Unlimited Core account allocation needs no usage check.
                 */
                if ($account['unlimited']) {
                    return $next($request);
                }

                $accountCapability =
                    $account['capability_key'];

                /*
                 * ESUBIZ_UNIVERSAL_ACCOUNT_USAGE_BRIDGE_V1
                 *
                 * Prefer an exact capability provider when the Core owner
                 * has one. Otherwise resolve through the generic accounts
                 * family authority.
                 *
                 * No Add-on, Email or WhatsApp-specific counter exists here.
                 */
                $usageRegistry =
                    app(
                        \App\Services\Core\CoreCapabilityUsageRegistry::class
                    );

                try {
                    if (
                        $usageRegistry->has(
                            $accountCapability
                        )
                    ) {
                        $usage =
                            $usageRegistry->usage(
                                $accountCapability,
                                $websiteId
                            );
                    } else {
                        $usage =
                            $usageRegistry->familyUsage(
                                'accounts',
                                $accountCapability,
                                $websiteId
                            );
                    }
                } catch (\RuntimeException $e) {
                    return $this->blocked(
                        $request,
                        "Usage for [{$accountCapability}] "
                        . 'could not be resolved.',
                        403
                    );
                }

                $entitlements->enforceAllocation(
                    $accountCapability,
                    $usage,
                    $websiteId,
                    $account['default']
                );

                return $next($request);
            }


            /*
             * ESUBIZ_UNIVERSAL_SERVICE_FAMILY_ENFORCEMENT_V2
             *
             * Service-family capabilities are platform/Core services.
             *
             * The universal Add-on entitlement layer does not invent a
             * second operational authority for registry-only services.
             * Their own implementation remains responsible for security,
             * licensing, active-state and service-specific authorization.
             *
             * Example: Central API already performs SaaS/off-server
             * credential, installation, licence and scope authorization.
             *
             * If a service later becomes allocation-backed, Function
             * Allocation remains the entitlement authority for that
             * upgrade rather than Add-on-specific middleware.
             */
            if ($mode === 'service') {
                return $next($request);
            }

            if ($mode === 'feature') {
                $entitlements->enforceCapability(
                    $capabilityKey,
                    $websiteId
                );

                return $next($request);
            }

            if ($mode === 'allocation') {
                $usage =
                    $this->resolveUsage(
                        $request,
                        $capabilityKey
                    );

                if ($usage === null) {
                    return $this->blocked(
                        $request,
                        "Usage for [{$capabilityKey}] "
                        . 'could not be resolved.',
                        403
                    );
                }

                $coreDefault =
                    $this->resolveCoreDefault(
                    $request,
                    $capabilityKey
                );

                $entitlements->enforceAllocation(
                    $capabilityKey,
                    $usage,
                    $websiteId,
                    $coreDefault
                );

                return $next($request);
            }

            return $this->blocked(
                $request,
                "Unsupported entitlement mode [{$mode}].",
                403
            );
        } catch (RuntimeException $e) {
            return $this->blocked(
                $request,
                $e->getMessage(),
                403
            );
        }
    }

    /**
     * SaaS resolves through the active tenant or route-bound Website.
     *
     * Off-server/API calls resolve through authenticated/request website
     * context supplied by the central Esubiz API integration.
     */
    protected function resolveWebsiteId(
        Request $request
    ): ?int {
        $routeWebsite =
            $request->route('website');

        if (
            is_object($routeWebsite)
            && isset($routeWebsite->id)
        ) {
            return (int) $routeWebsite->id;
        }

        if (
            is_numeric($routeWebsite)
            && (int) $routeWebsite > 0
        ) {
            return (int) $routeWebsite;
        }

        try {
            $tenant = Tenant::current();

            if (
                $tenant
                && isset($tenant->website_id)
                && (int) $tenant->website_id > 0
            ) {
                return (int) $tenant->website_id;
            }
        } catch (\Throwable $e) {
            // Off-server/API execution may not have a tenant context.
        }

        $attributeWebsite =
            $request->attributes->get(
                'esubiz_website_id'
            );

        if (
            is_numeric($attributeWebsite)
            && (int) $attributeWebsite > 0
        ) {
            return (int) $attributeWebsite;
        }

        $requestWebsite =
            $request->integer(
                'website_id'
            );

        return $requestWebsite > 0
            ? $requestWebsite
            : null;
    }

    /**
     * Generic usage input.
     *
     * Resource owners/meters publish usage into the request under:
     *
     * esubiz_entitlement_usage.<capability>
     *
     * This keeps the middleware generic and prevents resource-specific
     * code from accumulating here.
     */
    /*
     * ESUBIZ_UNIVERSAL_USAGE_AUTHORITY_RESOLUTION_V1
     *
     * Resolve usage from the central capability usage registry.
     *
     * Request-provided usage remains supported for authenticated
     * off-server/API integrations whose usage authority has already
     * been verified upstream.
     *
     * SaaS and centrally measurable resources resolve directly from
     * their registered Core provider.
     */
    protected function resolveUsage(
        Request $request,
        string $capabilityKey
    ): int|float|null {
        $websiteId =
            $this->resolveWebsiteId(
                $request
            );

        if ($websiteId === null) {
            return null;
        }

        $usageRegistry =
            app(
                \App\Services\Core\CoreCapabilityUsageRegistry::class
            );

        if (
            $usageRegistry->has(
                $capabilityKey
            )
        ) {
            return $usageRegistry->usage(
                $capabilityKey,
                $websiteId
            );
        }

        /*
         * Off-server capability owners may publish verified usage into
         * request attributes before this middleware executes.
         *
         * Do not read ordinary user input here. That prevents a client
         * from lowering its own usage value to bypass enforcement.
         */
        $usageMap =
            $request->attributes->get(
                'esubiz_entitlement_usage',
                []
            );

        if (
            is_array($usageMap)
            && array_key_exists(
                $capabilityKey,
                $usageMap
            )
            && is_numeric(
                $usageMap[$capabilityKey]
            )
        ) {
            return $usageMap[$capabilityKey] + 0;
        }

        return null;
    }

    /**
     * Resolve the Core base allocation from the existing central
     * feature-limit configuration.
     */
    /*
     * ESUBIZ_UNIVERSAL_BASE_ALLOCATION_RESOLUTION_V1
     *
     * First ask the capability's registered base-allocation authority.
     *
     * Storage/Bandwidth therefore use the website's configured Core
     * allocation, including the existing zero-value default semantics.
     *
     * Other capabilities continue to use core_feature_limits until
     * their capability owner registers a dedicated provider.
     */
    protected function resolveCoreDefault(
        Request $request,
        string $capabilityKey
    ): int|float|null {
        $websiteId =
            $this->resolveWebsiteId(
                $request
            );

        if ($websiteId !== null) {
            $registry =
                app(
                    \App\Services\Core\CoreCapabilityBaseAllocationRegistry::class
                );

            if (
                $registry->has(
                    $capabilityKey
                )
            ) {
                return $registry->allocation(
                    $capabilityKey,
                    $websiteId
                );
            }
        }

        return $this->resolveCoreDefaultFromFeatureLimit(
            $request,
            $capabilityKey
        );
    }

    protected function resolveCoreDefaultFromFeatureLimit(
        string $capabilityKey
    ): ?int {
        $row =
            \Illuminate\Support\Facades\DB::table(
                'core_feature_limits'
            )
                ->where(
                    'limit_key',
                    $capabilityKey
                )
                ->where(
                    'is_active',
                    true
                )
                ->whereNull(
                    'deleted_at'
                )
                ->first([
                    'default_value',
                    'value_type',
                    'is_unlimited',
                ]);

        if (!$row) {
            return 0;
        }

        if (
            (bool) $row->is_unlimited
            || $row->value_type === 'unlimited'
        ) {
            return null;
        }

        return $row->default_value === null
            ? 0
            : (int) $row->default_value;
    }

    protected function blocked(
        Request $request,
        string $message,
        int $status
    ): Response {
        if (
            $request->expectsJson()
            || $request->is('api/*')
        ) {
            return response()->json([
                'ok' => false,
                'code' => 'ENTITLEMENT_BLOCKED',
                'message' => $message,
            ], $status);
        }

        return response(
            '<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Feature Unavailable</title>
<style>
body{margin:0;font-family:Arial,sans-serif;background:#f8fafc;color:#0f172a;display:flex;min-height:100vh;align-items:center;justify-content:center}
main{width:min(560px,calc(100% - 40px));background:#fff;border:1px solid #e2e8f0;border-radius:18px;padding:40px;text-align:center;box-shadow:0 12px 34px rgba(15,23,42,.08)}
h1{margin:0 0 12px;font-size:26px}
p{margin:0;color:#64748b;line-height:1.65}
</style>
</head>
<body>
<main>
<h1>Feature Unavailable</h1>
<p>'
            . e($message)
            . '</p>
</main>
</body>
</html>',
            $status,
            [
                'Content-Type' =>
                    'text/html; charset=UTF-8',

                'Cache-Control' =>
                    'no-store, no-cache, must-revalidate',
            ]
        );
    }
}
