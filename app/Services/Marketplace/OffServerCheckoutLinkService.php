<?php

namespace App\Services\Marketplace;

use App\Models\ApiApplication;
use App\Models\Website;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use RuntimeException;

class OffServerCheckoutLinkService
{
    /**
     * Generate a short-lived signed Esubiz Marketplace checkout URL
     * for a registered off-server website.
     *
     * Pricing, deployment type and website ownership are NOT accepted
     * from the external website as authoritative values.
     */
    public function generate(
        Website $website,
        ApiApplication $application,
        string $productType,
        int $productId,
        ?string $returnUrl = null,
        ?string $returnArea = null,
        int $minutes = 15
    ): array {

        $this->assertApplicationBelongsToWebsite(
            $website,
            $application
        );

        $this->assertOffServerWebsite(
            $website
        );

        $validatedReturnUrl =
            $this->validateReturnUrl(
                $application,
                $returnUrl
            );

        $returnArea =
            $this->normalizeReturnArea(
                $returnArea,
                $productType
            );

        $tokenId =
            (string) Str::uuid();

        $expiresAt =
            now()->addMinutes(
                max(
                    1,
                    min(
                        $minutes,
                        30
                    )
                )
            );

        $url =
            URL::temporarySignedRoute(
                'marketplace.external.checkout',
                $expiresAt,
                [
                    /*
                     * UUID is used externally instead of exposing the
                     * sequential central website ID.
                     */
                    'website' =>
                        $website->uuid,

                    'application' =>
                        $application->uuid,

                    'product_type' =>
                        $productType,

                    'product_id' =>
                        $productId,

                    'return_area' =>
                        $returnArea,

                    'return_url' =>
                        $validatedReturnUrl,

                    'token_id' =>
                        $tokenId,
                ]
            );

        return [
            'url' =>
                $url,

            'token_id' =>
                $tokenId,

            'expires_at' =>
                $expiresAt,

            'website_id' =>
                $website->id,

            'website_uuid' =>
                $website->uuid,

            'deployment_type' =>
                'off_server',

            'checkout_origin' =>
                'off_server_website',

            'wallet_allowed' =>
                false,

            'return_area' =>
                $returnArea,

            'return_url' =>
                $validatedReturnUrl,
        ];
    }


    /**
     * Ensure the API application is the central application belonging
     * to this exact website.
     */
    protected function assertApplicationBelongsToWebsite(
        Website $website,
        ApiApplication $application
    ): void {

        if (
            (int) $application->website_id
            !== (int) $website->id
        ) {
            throw new RuntimeException(
                'API application does not belong to this website.'
            );
        }

        if (
            !$application->is_active
            || !$application->is_verified
        ) {
            throw new RuntimeException(
                'Website API application is not active and verified.'
            );
        }
    }


    /**
     * Central Esubiz determines whether this website belongs to the
     * developer/off-server side.
     *
     * The external caller cannot choose deployment_type.
     */
    protected function assertOffServerWebsite(
        Website $website
    ): void {

        if (
            empty(
                $website->developer_id
            )
        ) {
            throw new RuntimeException(
                'This checkout-link endpoint is only available to registered off-server websites.'
            );
        }
    }


    /**
     * Return destinations must already be registered against the
     * website's API application.
     *
     * An external website therefore cannot use Esubiz Marketplace as
     * an open redirect.
     */
    protected function validateReturnUrl(
        ApiApplication $application,
        ?string $returnUrl
    ): ?string {

        $returnUrl =
            trim(
                (string) $returnUrl
            );

        if ($returnUrl === '') {
            return null;
        }

        if (
            !filter_var(
                $returnUrl,
                FILTER_VALIDATE_URL
            )
        ) {
            throw new RuntimeException(
                'Invalid checkout return URL.'
            );
        }

        $scheme =
            strtolower(
                (string) parse_url(
                    $returnUrl,
                    PHP_URL_SCHEME
                )
            );

        if (
            !in_array(
                $scheme,
                [
                    'https',
                    'http',
                ],
                true
            )
        ) {
            throw new RuntimeException(
                'Unsupported checkout return URL scheme.'
            );
        }


        $registered =
            collect(
                $application->redirect_urls
                ?? []
            )
                ->filter()
                ->map(
                    fn ($url) =>
                        trim(
                            (string) $url
                        )
                );


        /*
         * Exact URL matches are accepted.
         *
         * We also accept another path on the same registered origin.
         * This allows one off-server website to return to:
         *
         * /admin/addons
         * /admin/themes
         * /admin/modules
         * /admin/ai
         * /admin/sms
         * etc.
         */
        $valid =
            $registered->contains(
                $returnUrl
            );


        if (!$valid) {

            $candidateScheme =
                strtolower(
                    (string) parse_url(
                        $returnUrl,
                        PHP_URL_SCHEME
                    )
                );

            $candidateHost =
                strtolower(
                    (string) parse_url(
                        $returnUrl,
                        PHP_URL_HOST
                    )
                );

            $candidatePort =
                parse_url(
                    $returnUrl,
                    PHP_URL_PORT
                );


            foreach ($registered as $registeredUrl) {

                $registeredScheme =
                    strtolower(
                        (string) parse_url(
                            $registeredUrl,
                            PHP_URL_SCHEME
                        )
                    );

                $registeredHost =
                    strtolower(
                        (string) parse_url(
                            $registeredUrl,
                            PHP_URL_HOST
                        )
                    );

                $registeredPort =
                    parse_url(
                        $registeredUrl,
                        PHP_URL_PORT
                    );


                if (
                    $candidateScheme === $registeredScheme
                    && $candidateHost === $registeredHost
                    && $candidatePort === $registeredPort
                ) {
                    $valid =
                        true;

                    break;
                }
            }
        }


        if (!$valid) {
            throw new RuntimeException(
                'Checkout return URL is not registered for this website.'
            );
        }

        return $returnUrl;
    }


    protected function normalizeReturnArea(
        ?string $returnArea,
        string $productType
    ): string {

        $returnArea =
            strtolower(
                trim(
                    (string) $returnArea
                )
            );


        $allowed = [
            'addons',
            'themes',
            'modules',
            'credits',
            'ai',
            'sms',
            'email',
            'whatsapp',
            'marketplace',
        ];


        if (
            in_array(
                $returnArea,
                $allowed,
                true
            )
        ) {
            return $returnArea;
        }


        return match ($productType) {

            'addon',
            'core_addon',
            'bundle',
            'core_bundle'
                => 'addons',

            'theme'
                => 'themes',

            'module'
                => 'modules',

            'ai_credits'
                => 'ai',

            'sms_credits'
                => 'sms',

            'email_credits'
                => 'email',

            'whatsapp_credits'
                => 'whatsapp',

            'credit',
            'credit_package'
                => 'credits',

            default
                => 'marketplace',
        };
    }
}
