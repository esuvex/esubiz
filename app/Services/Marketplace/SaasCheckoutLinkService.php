<?php

namespace App\Services\Marketplace;

use App\Models\Website;
use Illuminate\Support\Facades\URL;
use RuntimeException;

/**
 * ============================================================
 * ESUBIZ SAAS CENTRAL MARKETPLACE CHECKOUT HANDOFF
 * ============================================================
 *
 * SaaS tenant pages must never POST their tenant CSRF token
 * across to the central esubiz.com domain.
 *
 * Instead the tenant generates a short-lived signed Central
 * Marketplace handoff URL.
 *
 * Central Esubiz then validates:
 *
 * - signature
 * - authenticated Esubiz user
 * - SaaS website ownership
 * - product/deployment context
 *
 * and enters the normal unified Marketplace checkout.
 *
 * This is NOT the off-server checkout-link mechanism.
 */
class SaasCheckoutLinkService
{
    public function create(
        Website $website,
        string $productType,
        int $productId,
        ?string $returnUrl = null,
        ?string $returnArea = null
    ): string {

        if ($productId <= 0) {
            throw new RuntimeException(
                'Marketplace product is invalid.'
            );
        }


        /*
         * SaaS websites are centrally managed Esubiz websites.
         */
        $deploymentType =
            strtolower(
                trim(
                    (string) (
                        $website->deployment_type
                        ?? 'saas'
                    )
                )
            );


        if (
            $deploymentType !== ''
            && $deploymentType !== 'saas'
        ) {
            throw new RuntimeException(
                'This checkout handoff is only available to SaaS websites.'
            );
        }


        /*
         * The return URL is presentation/navigation context only.
         * Product fulfilment remains bound to website_id centrally.
         */
        $parameters = [
            'website_id' =>
                (int) $website->id,

            'product_type' =>
                trim($productType),

            'product_id' =>
                $productId,

            'deployment_type' =>
                'saas',

            'checkout_origin' =>
                'saas_website',

            'return_area' =>
                trim(
                    (string) $returnArea
                ),

            'return_url' =>
                trim(
                    (string) $returnUrl
                ),
        ];


        /*
         * forceRootUrl is deliberately avoided.
         *
         * The signed route itself is generated from the Central
         * Esubiz application URL so the signature belongs to the
         * same host that validates it.
         */
        $previousRoot =
            URL::getFacadeRoot();


        /*
         * Generate a RELATIVE signed URL so the tenant hostname
         * is not included in the signature.
         *
         * Then explicitly attach the Central Esubiz hostname.
         *
         * Example:
         *
         * Tenant:
         *   https://esuvex.esubiz.com
         *
         * Checkout:
         *   https://esubiz.com/marketplace/saas-checkout
         */
        $signedPath =
            URL::temporarySignedRoute(
                'marketplace.saas-checkout',
                now()->addMinutes(15),
                $parameters,
                false
            );


        return rtrim(
            (string) config('app.url'),
            '/'
        )
        . '/'
        . ltrim(
            $signedPath,
            '/'
        );
    }
}
