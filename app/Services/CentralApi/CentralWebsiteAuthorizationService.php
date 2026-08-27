<?php

namespace App\Services\CentralApi;

use App\Models\Website;
use App\Services\Licensing\OffServerApiCredentialService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use RuntimeException;


/**
 * ================================================================
 * CHECKPOINT 7 — CENTRAL WEBSITE SERVICE AUTHORIZATION
 * ================================================================
 *
 * One authoritative website identity layer for:
 *
 * 1. Central Esubiz
 * 2. Esubiz-hosted SaaS websites
 * 3. Licensed off-server Esubiz websites
 *
 * Central services such as:
 *
 * - Marketplace
 * - AI
 * - SMS
 * - Email
 * - WhatsApp
 * - Social integrations
 * - Gift Card validation/redeeming
 * - Payment connectivity
 * - Entitlements
 * - Updates
 *
 * must resolve the website through this service instead of trusting
 * a website_id supplied by an external browser/application.
 *
 * OFF-SERVER TRUST CHAIN
 *
 * bearer token
 *      -> installation token
 *      -> current installation
 *      -> registered website
 *      -> active domain-bound licence
 *
 * SAAS TRUST CHAIN
 *
 * trusted Central/SaaS context
 *      -> Central website registry
 *
 * CENTRAL TRUST CHAIN
 *
 * authenticated Central account
 *      -> owned Central website
 */
class CentralWebsiteAuthorizationService
{
    public const ORIGIN_CENTRAL =
        'central';

    public const ORIGIN_SAAS =
        'saas';

    public const ORIGIN_OFF_SERVER =
        'off_server';


    public function __construct(
        protected OffServerApiCredentialService $credentials
    ) {
    }


    /**
     * Resolve a Central Esubiz website owned by the authenticated user.
     */
    public function central(
        Website|int $website,
        int $userId
    ): array {

        $website =
            $website instanceof Website
                ? $website
                : Website::query()->findOrFail(
                    (int) $website
                );


        if (
            (int) $website->owner_id
            !== (int) $userId
        ) {
            throw new RuntimeException(
                'This Esubiz account does not own the requested website.'
            );
        }


        if (!$website->isRegistryActive()) {
            throw new RuntimeException(
                'This website is not active in the Central Esubiz registry.'
            );
        }


        return $this->context(
            $website,
            self::ORIGIN_CENTRAL,
            [
                'user_id' =>
                    (int) $userId,

                'identity_source' =>
                    'central_account',
            ]
        );
    }


    /**
     * Resolve a trusted Esubiz-hosted SaaS website.
     *
     * The caller must already have established the SaaS identity
     * through Central's trusted signed/session context.
     */
    public function saas(
        Website|int $website,
        ?int $userId = null
    ): array {

        $website =
            $website instanceof Website
                ? $website
                : Website::query()->findOrFail(
                    (int) $website
                );


        if (!$website->isSaas()) {
            throw new RuntimeException(
                'The requested website is not an Esubiz SaaS website.'
            );
        }


        if (!$website->isRegistryActive()) {
            throw new RuntimeException(
                'This SaaS website is not active in the Central Esubiz registry.'
            );
        }


        if (
            $userId !== null
            && (int) $website->owner_id
                !== (int) $userId
        ) {
            throw new RuntimeException(
                'The supplied account does not own this SaaS website.'
            );
        }


        return $this->context(
            $website,
            self::ORIGIN_SAAS,
            [
                'user_id' =>
                    $userId,

                'identity_source' =>
                    'trusted_saas_context',
            ]
        );
    }


    /**
     * Resolve an off-server request from its installation bearer token.
     *
     * IMPORTANT:
     *
     * website_id is NEVER accepted as the authority here.
     *
     * The website is derived from the bearer-token installation chain.
     */
    public function offServerRequest(
        Request $request,
        ?string $requiredScope = null
    ): array {

        $rawToken =
            $request->bearerToken();


        if (!$rawToken) {
            throw new RuntimeException(
                'A valid Esubiz installation access token is required.'
            );
        }


        /*
         * Checkpoint 4 service is authoritative for raw-token lookup.
         */
        $resolved =
            $this->credentials
                ->resolveBearerToken(
                    $rawToken
                );


        if (!$resolved) {
            throw new RuntimeException(
                'The Esubiz installation access token is invalid or revoked.'
            );
        }


        /*
         * Checkpoint 4 resolveBearerToken() is already authoritative.
         *
         * It validates:
         *
         * raw token
         *   -> active hashed token
         *   -> current installation
         *   -> active off-server website
         *   -> active domain-bound licence
         *
         * Reuse that resolved context rather than maintaining a
         * second parallel authorization implementation here.
         */
        $token =
            $resolved['token']
            ?? null;

        $installation =
            $resolved['installation']
            ?? null;

        $website =
            $resolved['website']
            ?? null;

        $license =
            $resolved['license']
            ?? null;

        $scopes =
            $resolved['scopes']
            ?? [];


        if (!$token) {
            throw new RuntimeException(
                'The Esubiz installation token could not be resolved.'
            );
        }


        if (!$installation) {
            throw new RuntimeException(
                'The Esubiz installation identity could not be resolved.'
            );
        }


        if (!$website instanceof Website) {
            throw new RuntimeException(
                'The installation does not resolve to a valid Central website.'
            );
        }


        if (!$license) {
            throw new RuntimeException(
                'The installation does not resolve to an active Esubiz licence.'
            );
        }


        /*
         * Defence in depth.
         *
         * The credential service has already checked these, but the
         * Central service boundary confirms that the resolved website
         * remains an active off-server registry record.
         */
        if (!$website->isOffServer()) {
            throw new RuntimeException(
                'The installation does not belong to an off-server website.'
            );
        }


        if (!$website->isRegistryActive()) {
            throw new RuntimeException(
                'This off-server website is not active in the Central registry.'
            );
        }


        /*
         * Domain identity must remain consistent.
         */
        $websiteDomain =
            $this->normalizeDomain(
                $website->registered_domain
                ?? ''
            );


        $installationDomain =
            $this->normalizeDomain(
                $installation->registered_domain
                ?? ''
            );


        $licenseDomain =
            $this->normalizeDomain(
                $license->registered_domain
                ?? ''
            );


        if (
            $websiteDomain === ''
            || $licenseDomain === ''
            || $websiteDomain !== $licenseDomain
        ) {
            throw new RuntimeException(
                'The registered website and licence domain do not match.'
            );
        }


        if (
            $installationDomain !== ''
            && $installationDomain !== $websiteDomain
        ) {
            throw new RuntimeException(
                'The current installation domain does not match the licensed domain.'
            );
        }


        if ($requiredScope !== null) {

            if (
                !in_array(
                    $requiredScope,
                    $scopes,
                    true
                )
            ) {
                throw new RuntimeException(
                    'This Esubiz installation is not authorized for the requested Central service.'
                );
            }
        }


        return $this->context(
            $website,
            self::ORIGIN_OFF_SERVER,
            [
                'user_id' =>
                    (int) $website->owner_id,

                'installation_id' =>
                    (int) $installation->id,

                'installation_uuid' =>
                    $installation->installation_uuid
                    ?? null,

                'license_registration_id' =>
                    (int) $license->id,

                'api_application_id' =>
                    $installation->api_application_id
                    ?? null,

                'identity_source' =>
                    'installation_bearer_token',

                'required_scope' =>
                    $requiredScope,
            ]
        );
    }


    /**
     * Shared normalized Central service context.
     */
    protected function context(
        Website $website,
        string $origin,
        array $extra = []
    ): array {

        return array_merge(
            [
                'origin' =>
                    $origin,

                'website_id' =>
                    (int) $website->id,

                'website_uuid' =>
                    $website->website_uuid
                    ?? $website->uuid
                    ?? null,

                'owner_id' =>
                    (int) $website->owner_id,

                'deployment_type' =>
                    $website->deployment_type,

                'registered_domain' =>
                    $website->registered_domain,

                'registry_status' =>
                    $website->registry_status,

                'registry_active' =>
                    $website->isRegistryActive(),

                'is_saas' =>
                    $website->isSaas(),

                'is_off_server' =>
                    $website->isOffServer(),
            ],
            $extra
        );
    }


    /**
     * Verify that the installation token contains a service scope.
     */
    protected function assertScope(
        mixed $token,
        string $requiredScope
    ): void {

        $scopes =
            is_array($token)
                ? ($token['scopes'] ?? [])
                : ($token->scopes ?? []);


        if (is_string($scopes)) {

            $decoded =
                json_decode(
                    $scopes,
                    true
                );

            $scopes =
                is_array($decoded)
                    ? $decoded
                    : preg_split(
                        '/[\s,]+/',
                        $scopes,
                        -1,
                        PREG_SPLIT_NO_EMPTY
                    );
        }


        if (!is_array($scopes)) {
            $scopes = [];
        }


        if (
            !in_array(
                $requiredScope,
                $scopes,
                true
            )
        ) {
            throw new RuntimeException(
                'This Esubiz installation is not authorized for the requested Central service.'
            );
        }
    }


    /**
     * Normalize a licensed host without changing its identity.
     */
    protected function normalizeDomain(
        ?string $domain
    ): string {

        $domain =
            strtolower(
                trim(
                    (string) $domain
                )
            );


        if ($domain === '') {
            return '';
        }


        if (
            str_contains(
                $domain,
                '://'
            )
        ) {

            $host =
                parse_url(
                    $domain,
                    PHP_URL_HOST
                );

            if ($host) {
                $domain =
                    strtolower(
                        (string) $host
                    );
            }
        }


        $domain =
            preg_replace(
                '/:\d+$/',
                '',
                $domain
            );


        return trim(
            (string) $domain,
            ". \t\n\r\0\x0B"
        );
    }


    /**
     * CHECKPOINT_7_SERVICE_SCOPE_AUTHORIZATION
     *
     * Shared authorization entry point for protected Central services.
     *
     * Off-server:
     *     required scope is enforced against the current installation
     *     bearer token.
     *
     * SaaS / Central:
     *     identity is already trusted by Central and the scope identifies
     *     which service is being requested.
     *
     * Product entitlement / credit balance checks happen separately
     * inside the actual service engine.
     */
    public function authorizeService(
        string $scope,
        string $origin,
        mixed $identity,
        ?int $userId = null
    ): array {

        $scope =
            \App\Support\CentralApi\CentralServiceScopeRegistry::assertKnown(
                $scope
            );


        return match ($origin) {

            self::ORIGIN_CENTRAL =>
                array_merge(
                    $this->central(
                        $identity,
                        (int) $userId
                    ),
                    [
                        'service_scope' =>
                            $scope,
                    ]
                ),


            self::ORIGIN_SAAS =>
                array_merge(
                    $this->saas(
                        $identity,
                        $userId
                    ),
                    [
                        'service_scope' =>
                            $scope,
                    ]
                ),


            self::ORIGIN_OFF_SERVER =>
                array_merge(
                    $this->offServerRequest(
                        $identity,
                        $scope
                    ),
                    [
                        'service_scope' =>
                            $scope,
                    ]
                ),


            default =>
                throw new RuntimeException(
                    'Unsupported Esubiz Central service origin.'
                ),
        };
    }

}
