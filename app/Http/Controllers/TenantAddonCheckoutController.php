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
            'product_type' => ['nullable', 'in:addon,bundle,module,theme'],
            'product_id' => ['nullable', 'integer', 'min:1'],
            'addon_id' => ['nullable', 'integer', 'min:1'],
            'return_url' => ['nullable', 'string', 'max:2000'],
            'return_area' => ['nullable', 'string', 'max:100'],
        ]);

        /*
         * ESUBIZ_CORE_ADDON_BUNDLE_CHECKOUT_V1
         *
         * Shared Marketplace UI sends product_type + product_id.
         * addon_id remains supported for older Add-on purchase triggers.
         */
        $selectedType = (string) ($validated['product_type'] ?? 'addon');

        $selectedId = (int) (
            $validated['product_id']
            ?? $validated['addon_id']
            ?? 0
        );

        abort_unless(
            $selectedId > 0,
            422,
            'A Marketplace product is required.'
        );

        $isBundle = $selectedType === 'bundle';

        $table = $isBundle
            ? 'core_addon_bundles'
            : 'core_addons';

        $checkoutProductType = in_array($selectedType, ['module', 'theme'], true)
            ? $selectedType
            : ($isBundle ? 'core_bundle' : 'core_addon');

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
        if (in_array($selectedType, ['module', 'theme'], true)) {
            // Central validates the catalog identity, availability and price.
            // Off-server catalog IDs belong to Central, not the local database.
            $product = (object) ['id' => $selectedId];
        } else {
        $productQuery = DB::table($table)
            ->where('id', $selectedId)
            ->where('is_active', 1);

        if ($deploymentType === 'saas') {
            $productQuery->where('saas_available', 1);
        } else {
            $productQuery->where('off_server_available', 1);
        }

        $product = $productQuery->first();

        abort_unless(
            $product,
            404,
            $isBundle
                ? 'This Bundle is not available for this website.'
                : 'This Add-on is not available for this website.'
        );

        }

        /*
         * Reuse the CURRENT request.
         *
         * Do not create a synthetic Request here: Marketplace checkout
         * relies on the authenticated user/session/account mode already
         * attached to this request.
         */
        /*
         * The route website must be the Core website currently being used.
         * A browser-supplied website ID cannot select another destination.
         */
        abort_unless(
            (string) $request->route('subdomain') === (string) $website->subdomain,
            403,
            'This purchase does not belong to the current Core website.'
        );

        $siteSession = 'tenant_cms_sites.' . $website->id;
        $coreAuthenticated =
            (
                (bool) session($siteSession . '.authenticated')
                && (int) session($siteSession . '.user_id') > 0
            )
            || (
                (bool) session('tenant_cms_authenticated')
                && (int) session('tenant_cms_website_id') === (int) $website->id
                && (int) session('tenant_cms_user_id') > 0
            );

        abort_unless($coreAuthenticated, 403, 'Sign in to Core to purchase this product.');

        $origin = $request->getSchemeAndHttpHost();
        $candidate = trim((string) ($validated['return_url'] ?? ''));
        $returnUrl = $origin . '/admin';

        if (
            filter_var($candidate, FILTER_VALIDATE_URL)
            && strtolower((string) parse_url($candidate, PHP_URL_HOST))
                === strtolower((string) $request->getHost())
            && strtolower((string) parse_url($candidate, PHP_URL_SCHEME))
                === strtolower((string) $request->getScheme())
        ) {
            $returnUrl = $candidate;
        }

        $returnArea = (string) ($validated['return_area'] ?? 'addons');

        if ($deploymentType === 'saas') {
            $url = app(\App\Services\Marketplace\SaasCheckoutLinkService::class)
                ->create(
                    $website,
                    $checkoutProductType,
                    (int) $product->id,
                    $returnUrl,
                    $returnArea
                );

            return response()->json(['url' => $url]);
        }

        try {
            $central = app(\App\Services\Core\CoreCentralConnectionService::class);
            $response = $central->request(15)->post(
                $central->centralUrl() . '/api/v1/core/marketplace/checkout-link',
                [
                    'product_type' => $checkoutProductType,
                    'product_id' => (int) $product->id,
                    'quantity' => 1,
                    'return_url' => $returnUrl,
                    'return_area' => $returnArea,
                ]
            );

            if (!$response->successful() || !$response->json('checkout_url')) {
                return response()->json([
                    'message' => $response->json('message')
                        ?: 'Unable to start checkout for this Core website.',
                ], 422);
            }

            return response()->json(['url' => $response->json('checkout_url')]);
        } catch (\Throwable $exception) {
            report($exception);

            return response()->json([
                'message' => 'Unable to connect to Esubiz checkout. Please try again.',
            ], 422);
        }
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
