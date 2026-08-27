<?php

namespace App\Services\Marketplace;

use App\Models\Website;
use App\Services\Licensing\OffServerApiCredentialService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use RuntimeException;

/**
 * ============================================================
 * UNIFIED MARKETPLACE CHECKOUT CONTEXT
 * ============================================================
 *
 * One authoritative identity contract for:
 *
 * 1. Central Esubiz checkout
 * 2. SaaS website checkout
 * 3. Off-server Core checkout
 *
 * Important:
 *
 * A SaaS user does NOT need to have entered the website through
 * Central SSO merely to purchase a product for that website.
 *
 * The signed SaaS handoff establishes the target website.
 * Central authentication may still be requested when required
 * for the actual buyer/payment account.
 *
 * Off-server Core never self-declares a trusted website_id.
 * Its bearer token resolves:
 *
 * token
 *   -> current installation
 *   -> Central website
 *   -> active domain-locked licence
 */
class MarketplaceCheckoutContextService
{
    public const ORIGIN_CENTRAL =
        'central_account';

    public const ORIGIN_SAAS =
        'saas_website';

    public const ORIGIN_OFF_SERVER =
        'off_server_website';


    /**
     * Build Central Esubiz checkout context.
     */
    public function central(
        ?int $userId = null,
        ?string $returnUrl = null,
        ?string $returnArea = null
    ): array {

        $userId =
            $userId
            ?: (
                auth()->check()
                    ? (int) auth()->id()
                    : null
            );


        return $this->normalize([
            'checkout_origin' =>
                static::ORIGIN_CENTRAL,

            /*
             * Central Esubiz is a checkout origin, not a deployment.
             *
             * A target website/product flow later resolves whether
             * fulfilment is SaaS or off-server.
             */
            'deployment_type' =>
                null,

            'website_id' =>
                null,

            'website_uuid' =>
                null,

            'buyer_user_id' =>
                $userId,

            'workspace_id' =>
                null,

            'return_url' =>
                $returnUrl,

            'return_area' =>
                $returnArea,

            'wallet_allowed' =>
                true,

            'identity_source' =>
                'central_session',
        ]);
    }


    /**
     * Build context for a SaaS website.
     *
     * Website identity is resolved centrally.
     *
     * Local tenant authentication and Central SSO are separate
     * concerns. A local-login user may start checkout because the
     * signed handoff identifies the SaaS website independently.
     */
    public function saas(
        Website $website,
        ?int $buyerUserId = null,
        ?string $returnUrl = null,
        ?string $returnArea = null
    ): array {

        if (!$website->isSaas()) {
            throw new RuntimeException(
                'Marketplace SaaS checkout requires a SaaS website.'
            );
        }


        if (!$website->isRegistryActive()) {
            throw new RuntimeException(
                'The SaaS website is not active in the Central website registry.'
            );
        }


        return $this->normalize([
            'checkout_origin' =>
                static::ORIGIN_SAAS,

            'deployment_type' =>
                Website::DEPLOYMENT_SAAS,

            'website_id' =>
                (int) $website->id,

            'website_uuid' =>
                $website->centralWebsiteIdentity(),

            /*
             * buyer_user_id is the Central Esubiz account when one
             * is already authenticated/known.
             *
             * It may be null during a local-login SaaS handoff.
             * The target website remains authoritative regardless.
             */
            'buyer_user_id' =>
                $buyerUserId,

            'workspace_id' =>
                $website->workspace_id
                ?? null,

            'return_url' =>
                $returnUrl,

            'return_area' =>
                $returnArea,

            'wallet_allowed' =>
                true,

            'identity_source' =>
                'signed_saas_handoff',
        ]);
    }


    /**
     * Build trusted off-server context from the installation
     * bearer token.
     *
     * Never accept website_id from the remote Core as authority.
     */
    public function offServerBearer(
        string $bearerToken,
        ?int $buyerUserId = null,
        ?string $returnUrl = null,
        ?string $returnArea = null
    ): array {

        $resolved =
            app(
                OffServerApiCredentialService::class
            )->resolveBearerToken(
                $bearerToken
            );


        /** @var Website $website */
        $website =
            $resolved['website'];


        if (!$website->isOffServer()) {
            throw new RuntimeException(
                'Marketplace off-server checkout requires an off-server website.'
            );
        }


        $installation =
            $resolved['installation'];

        $license =
            $resolved['license'];


        return $this->normalize([
            'checkout_origin' =>
                static::ORIGIN_OFF_SERVER,

            'deployment_type' =>
                Website::DEPLOYMENT_OFF_SERVER,

            'website_id' =>
                (int) $website->id,

            'website_uuid' =>
                $website->centralWebsiteIdentity(),

            'buyer_user_id' =>
                $buyerUserId,

            'workspace_id' =>
                $website->workspace_id
                ?? null,

            'return_url' =>
                $returnUrl,

            'return_area' =>
                $returnArea,

            /*
             * Off-server websites cannot use a website-local wallet
             * as if it were the Central Esubiz wallet.
             *
             * Central payment methods remain available separately.
             */
            'wallet_allowed' =>
                false,

            'installation_id' =>
                (int) $installation->id,

            'installation_uuid' =>
                (string) $installation->installation_uuid,

            'license_registration_id' =>
                (int) $license->id,

            'identity_source' =>
                'off_server_bearer_token',
        ]);
    }


    /**
     * Resolve bearer token from an incoming Core API request.
     */
    public function offServerRequest(
        Request $request,
        ?int $buyerUserId = null,
        ?string $returnUrl = null,
        ?string $returnArea = null
    ): array {

        $token =
            trim(
                (string) $request->bearerToken()
            );


        if ($token === '') {
            throw new RuntimeException(
                'Authenticated Esubiz Core installation token is required.'
            );
        }


        return $this->offServerBearer(
            $token,
            $buyerUserId,
            $returnUrl,
            $returnArea
        );
    }


    /**
     * Read authoritative context from an existing checkout session.
     *
     * Fulfilment should prefer this persisted context rather than
     * reconstructing website identity from order references.
     */
    public function fromCheckoutSession(
        object $session
    ): array {

        $website = null;


        if (!empty($session->website_id)) {

            $website =
                Website::query()
                    ->find(
                        (int) $session->website_id
                    );


            if (!$website) {
                throw new RuntimeException(
                    'Checkout target website no longer exists.'
                );
            }
        }


        $origin =
            (string) (
                $session->checkout_origin
                ?? static::ORIGIN_CENTRAL
            );


        if (
            $origin === static::ORIGIN_SAAS
            || $origin === static::ORIGIN_OFF_SERVER
        ) {

            if (!$website) {
                throw new RuntimeException(
                    'Website checkout session is missing its Central website identity.'
                );
            }


            if (!$website->isRegistryActive()) {
                throw new RuntimeException(
                    'Checkout target website is no longer active.'
                );
            }
        }


        if (
            $origin === static::ORIGIN_SAAS
            && !$website->isSaas()
        ) {
            throw new RuntimeException(
                'Checkout deployment identity does not match the SaaS website.'
            );
        }


        if (
            $origin === static::ORIGIN_OFF_SERVER
            && !$website->isOffServer()
        ) {
            throw new RuntimeException(
                'Checkout deployment identity does not match the off-server website.'
            );
        }


        return $this->normalize([
            'checkout_origin' =>
                $origin,

            'deployment_type' =>
                $session->deployment_type
                ?? (
                    $website
                        ? $website->deployment_type
                        : 'central'
                ),

            'website_id' =>
                $website
                    ? (int) $website->id
                    : null,

            'website_uuid' =>
                $website
                    ? $website->centralWebsiteIdentity()
                    : null,

            'buyer_user_id' =>
                $session->user_id
                ?? null,

            'workspace_id' =>
                $session->workspace_id
                ?? null,

            'return_url' =>
                $session->return_url
                ?? null,

            'return_area' =>
                $session->return_area
                ?? null,

            'wallet_allowed' =>
                isset($session->wallet_allowed)
                    ? (bool) $session->wallet_allowed
                    : $origin !== static::ORIGIN_OFF_SERVER,

            'identity_source' =>
                'checkout_session',
        ]);
    }


    /**
     * Persist context fields supported by the current
     * marketplace_checkout_sessions schema.
     */
    public function sessionValues(
        array $context
    ): array {

        $context =
            $this->normalize(
                $context
            );


        $values = [];


        $map = [
            'checkout_origin',
            'deployment_type',
            'website_id',
            'workspace_id',
            'return_url',
            'return_area',
            'wallet_allowed',
        ];


        foreach ($map as $column) {

            if (
                array_key_exists(
                    $column,
                    $context
                )
                &&
                \Illuminate\Support\Facades\Schema::hasColumn(
                    'marketplace_checkout_sessions',
                    $column
                )
            ) {
                $values[$column] =
                    $context[$column];
            }
        }


        /*
         * Preserve buyer identity when the existing schema exposes
         * either user_id or buyer_user_id.
         */
        if (
            \Illuminate\Support\Facades\Schema::hasColumn(
                'marketplace_checkout_sessions',
                'buyer_user_id'
            )
        ) {
            $values['buyer_user_id'] =
                $context['buyer_user_id'];
        } elseif (
            \Illuminate\Support\Facades\Schema::hasColumn(
                'marketplace_checkout_sessions',
                'user_id'
            )
        ) {
            $values['user_id'] =
                $context['buyer_user_id'];
        }


        return $values;
    }


    /**
     * Common normalized contract consumed by checkout,
     * payment and fulfilment.
     */
    protected function normalize(
        array $context
    ): array {

        $origin =
            $context['checkout_origin']
            ?? static::ORIGIN_CENTRAL;


        if (
            !in_array(
                $origin,
                [
                    static::ORIGIN_CENTRAL,
                    static::ORIGIN_SAAS,
                    static::ORIGIN_OFF_SERVER,
                ],
                true
            )
        ) {
            throw new RuntimeException(
                'Unsupported Marketplace checkout origin.'
            );
        }


        return [
            'checkout_origin' =>
                $origin,

            'deployment_type' =>
                $context['deployment_type']
                ?? null,

            'website_id' =>
                isset($context['website_id'])
                    && $context['website_id'] !== null
                        ? (int) $context['website_id']
                        : null,

            'website_uuid' =>
                $context['website_uuid']
                ?? null,

            'buyer_user_id' =>
                isset($context['buyer_user_id'])
                    && $context['buyer_user_id'] !== null
                        ? (int) $context['buyer_user_id']
                        : null,

            'workspace_id' =>
                $context['workspace_id']
                ?? null,

            'return_url' =>
                $this->normalizeReturnUrl(
                    $context['return_url']
                    ?? null
                ),

            'return_area' =>
                $context['return_area']
                ?? null,

            'wallet_allowed' =>
                (bool) (
                    $context['wallet_allowed']
                    ?? true
                ),

            'installation_id' =>
                $context['installation_id']
                ?? null,

            'installation_uuid' =>
                $context['installation_uuid']
                ?? null,

            'license_registration_id' =>
                $context['license_registration_id']
                ?? null,

            'identity_source' =>
                $context['identity_source']
                ?? 'unknown',
        ];
    }


    protected function normalizeReturnUrl(
        ?string $url
    ): ?string {

        $url =
            trim(
                (string) $url
            );


        if ($url === '') {
            return null;
        }


        if (
            !filter_var(
                $url,
                FILTER_VALIDATE_URL
            )
        ) {
            throw new RuntimeException(
                'Marketplace return URL is invalid.'
            );
        }


        $scheme =
            strtolower(
                (string) parse_url(
                    $url,
                    PHP_URL_SCHEME
                )
            );


        if (
            !in_array(
                $scheme,
                [
                    'http',
                    'https',
                ],
                true
            )
        ) {
            throw new RuntimeException(
                'Marketplace return URL must use HTTP or HTTPS.'
            );
        }


        return $url;
    }
}
