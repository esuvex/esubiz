<?php

namespace App\Http\Controllers;

use App\Models\Website;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TenantAddonCheckoutController extends Controller
{
    /**
     * ESUBIZ_UNIVERSAL_ADDON_DIRECT_CENTRAL_CHECKOUT_V3
     *
     * Universal Add-on purchase handoff.
     *
     * The browser supplies only the selected Add-on.
     * Product availability, website ownership and checkout
     * destination remain server-authoritative.
     *
     * The live authenticated request is deliberately reused so
     * MarketplaceController::checkout() receives the same Laravel
     * user/session context as the tenant admin request.
     */
    public function create(
        Request $request,
        $website,
        MarketplaceController $marketplace
    ): JsonResponse {
        /*
         * ESUBIZ_UNIVERSAL_CHECKOUT_WEBSITE_RESOLUTION_V2
         *
         * Resolve website explicitly instead of relying on tenant-host
         * implicit route model binding.
         */
        /*
         * ESUBIZ_CHECKOUT_NAMED_WEBSITE_ROUTE_RESOLUTION_V1
         *
         * Resolve Website by the named route parameter.
         * Tenant routes also contain {subdomain}, so positional
         * controller arguments must not determine Website identity.
         */
        $websiteId = (int) $request->route('website');

        $website = \App\Models\Website::withoutGlobalScopes()
            ->findOrFail($websiteId);


        $validated = $request->validate([
            'addon_id' => ['required', 'integer', 'min:1'],
            'return_url' => ['nullable', 'string', 'max:2000'],
            'return_area' => ['nullable', 'string', 'max:100'],
        ]);

        /*
         * Deployment is derived from the registered website,
         * never trusted from browser input.
         */
        $deploymentType = strtolower(
            trim((string) ($website->deployment_type ?? 'saas'))
        );

        if ($deploymentType === '') {
            $deploymentType = 'saas';
        }

        abort_unless(
            in_array($deploymentType, ['saas', 'off_server'], true),
            422,
            'Unsupported website deployment type.'
        );

        /*
         * Universal availability check.
         * SaaS and off-server use their respective Admin-controlled
         * marketplace availability flags.
         */
        $addonQuery = DB::table('core_addons')
            ->where('id', (int) $validated['addon_id'])
            ->where('is_active', 1);

        if ($deploymentType === 'saas') {
            $addonQuery->where('saas_available', 1);
        } else {
            $addonQuery->where('off_server_available', 1);
        }

        $addon = $addonQuery->first();

        abort_unless(
            $addon,
            404,
            'This Add-on is not available for this website.'
        );

        /*
         * Reuse the CURRENT request.
         *
         * Do not create a synthetic Request here: Marketplace checkout
         * relies on the authenticated user/session/account mode already
         * attached to this request.
         */
        $request->merge([
            'product_type' => 'core_addon',
            'product_id' => (int) $addon->id,
            'deployment_type' => $deploymentType,
            'website_id' => (int) $website->id,

            'checkout_origin' => $deploymentType === 'saas'
                ? 'saas_website'
                : 'off_server_website',

            /*
             * Tenant-origin checkout may use the current authenticated
             * central session. MarketplaceController remains the final
             * authority for payment-method availability.
             */
            'wallet_allowed' => true,

            'return_area' => (string) ($validated['return_area'] ?? ''),
            'return_url' => (string) ($validated['return_url'] ?? ''),
        ]);

        /*
         * Enter the EXISTING unified Marketplace checkout creator.
         * This creates the checkout session/order and returns the
         * canonical /marketplace/checkout/{order} redirect.
         */
        $response = $marketplace->checkout($request);

        if (!method_exists($response, 'getTargetUrl')) {
            return response()->json([
                'message' => 'Unable to start checkout.',
            ], 422);
        }

        $targetUrl = $response->getTargetUrl();

        if (!$targetUrl) {
            return response()->json([
                'message' => 'Unable to start checkout.',
            ], 422);
        }

        /*
         * ESUBIZ_CENTRAL_MARKETPLACE_CHECKOUT_DESTINATION_V1
         *
         * Marketplace checkout always lives on Central Esubiz.
         * Preserve only the generated checkout path/query and attach
         * the authoritative Central hostname.
         */
        $parts = parse_url($targetUrl);

        $path = $parts['path'] ?? '/marketplace/checkout';

        $query = !empty($parts['query'])
            ? '?' . $parts['query']
            : '';

        $url = rtrim((string) config('app.url'), '/')
            . '/'
            . ltrim($path, '/')
            . $query;

        return response()->json([
            'url' => $url,
        ]);
    }

    /*
     * ESUBIZ_CENTRAL_UNIVERSAL_ADDON_CHECKOUT_ACTION_V1
     *
     * Universal Marketplace order starter for Add-on sales triggers.
     * Placement has no role here.
     *
     * SaaS and off-server both enter the existing Marketplace checkout()
     * pipeline, which creates the order before returning the final
     * /marketplace/checkout/{order} destination.
     */
    public function createCentral(
        \Illuminate\Http\Request $request,
        \App\Http\Controllers\MarketplaceController $marketplace
    ): \Illuminate\Http\JsonResponse {
        $validated = $request->validate([
            'addon_id' => ['required', 'integer', 'min:1'],
            'website_id' => ['required', 'integer', 'min:1'],
            'deployment_type' => ['required', 'in:saas,off_server'],
            'return_url' => ['nullable', 'string', 'max:2048'],
            'return_area' => ['nullable', 'string', 'max:100'],
        ]);

        $website = \App\Models\Website::withoutGlobalScopes()
            ->findOrFail((int) $validated['website_id']);

        $deploymentType = $validated['deployment_type'];

        /*
         * Do not trust the browser to change the website's deployment type.
         */
        $actualDeployment = strtolower(
            trim((string) ($website->deployment_type ?? 'saas'))
        );

        if ($actualDeployment === '') {
            $actualDeployment = 'saas';
        }

        abort_unless(
            $actualDeployment === $deploymentType,
            422,
            'Website deployment type does not match checkout request.'
        );

        $request->merge([
            'product_type' => 'addon',
            'product_id' => (int) $validated['addon_id'],
            'deployment_type' => $deploymentType,
            'website_id' => (int) $website->id,
            'checkout_origin' => $deploymentType === 'off_server'
                ? 'off_server_website'
                : 'saas_website',
            'wallet_allowed' => true,
            'return_area' => $validated['return_area'] ?? 'addon_purchase',
            'return_url' => $validated['return_url'] ?? null,
        ]);

        $response = $marketplace->checkout($request);

        if (!$response instanceof \Illuminate\Http\RedirectResponse) {
            return response()->json([
                'message' => 'Marketplace checkout did not return a checkout destination.',
            ], 422);
        }

        return response()->json([
            'url' => $response->getTargetUrl(),
        ]);
    }

}
