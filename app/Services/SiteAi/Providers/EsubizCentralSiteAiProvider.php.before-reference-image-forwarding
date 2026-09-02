<?php

namespace App\Services\SiteAi\Providers;

use App\Services\Ai\CentralAiEngine;
use App\Services\SiteAi\Contracts\SiteAiProvider;
use RuntimeException;

/**
 * ============================================================
 * ESUBIZ CENTRAL SITE AI PROVIDER
 * ============================================================
 *
 * SaaS tenants execute AI directly through the CentralAiEngine
 * because they already run inside the Esubiz platform.
 *
 * The browser and tenant feature never receive:
 *
 * - OpenAI API credentials
 * - provider secrets
 * - model credentials
 * - billing authority
 *
 * CentralAiEngine remains the sole execution + billing authority.
 *
 * Off-server websites will use the external authenticated Esubiz
 * AI API later, but that must still terminate in this same engine.
 */
class EsubizCentralSiteAiProvider implements SiteAiProvider
{
    public function __construct(
        protected CentralAiEngine $engine
    ) {
    }


    public function generate(
        array $request
    ): array {

        /*
         * ========================================================
         * CHECKPOINT_7_AI_CENTRAL_AUTHORIZATION
         * ========================================================
         *
         * AI execution remains inside CentralAiEngine.
         *
         * This authorization layer only establishes the trusted
         * Central website identity before credits/provider/model
         * execution begins.
         *
         * Supported website origins:
         *
         * - SaaS
         * - Off-server
         *
         * Central user/admin AI may continue using its existing
         * direct Central execution path.
         */

        $websiteId =
            (int) (
                $request['website_id']
                ?? 0
            );


        if ($websiteId <= 0) {
            throw new RuntimeException(
                'Central AI request has no valid website identity.'
            );
        }


        $website =
            \App\Models\Website::query()
                ->find(
                    $websiteId
                );


        if (!$website) {
            throw new RuntimeException(
                'Central AI website identity was not found.'
            );
        }


        $authorizer =
            app(
                \App\Services\CentralApi\CentralWebsiteAuthorizationService::class
            );


        if ($website->isOffServer()) {

            /*
             * Off-server AI must never trust website_id alone.
             *
             * The Core request must carry the current installation
             * bearer token, which resolves back to this website.
             */
            $rawToken =
                trim(
                    (string) (
                        $request['access_token']
                        ?? $request['bearer_token']
                        ?? ''
                    )
                );


            if ($rawToken === '') {
                throw new RuntimeException(
                    'Off-server AI requires an authenticated Esubiz installation token.'
                );
            }


            $fakeRequest =
                \Illuminate\Http\Request::create(
                    '/',
                    'POST'
                );


            $fakeRequest->headers->set(
                'Authorization',
                'Bearer ' . $rawToken
            );


            $identity =
                $authorizer->authorizeService(
                    \App\Support\CentralApi\CentralServiceScopeRegistry::AI_USE,
                    \App\Services\CentralApi\CentralWebsiteAuthorizationService::ORIGIN_OFF_SERVER,
                    $fakeRequest
                );


            if (
                (int) (
                    $identity['website_id']
                    ?? 0
                )
                !== $websiteId
            ) {
                throw new RuntimeException(
                    'Off-server AI token does not belong to the requested website.'
                );
            }

        } elseif ($website->isSaas()) {

            /*
             * SaaS website identity is trusted through the Central
             * registry. User/session authorization remains handled
             * by the SaaS application entry point.
             */
            $identity =
                $authorizer->authorizeService(
                    \App\Support\CentralApi\CentralServiceScopeRegistry::AI_USE,
                    \App\Services\CentralApi\CentralWebsiteAuthorizationService::ORIGIN_SAAS,
                    $website,
                    isset($request['user_id'])
                        ? (int) $request['user_id']
                        : null
                );

        } else {

            throw new RuntimeException(
                'Central AI website deployment type is unsupported.'
            );
        }


        /*
         * Replace externally supplied identity with the trusted
         * Central identity returned by the authorizer.
         */
        $request['website_id'] =
            (int) $identity['website_id'];

        $request['website_uuid'] =
            $identity['website_uuid']
            ?? null;

        $request['deployment_type'] =
            $identity['deployment_type']
            ?? null;


        /*
        |--------------------------------------------------------------------------
        | WEBSITE IDENTITY
        |--------------------------------------------------------------------------
        |
        | SiteAiEngine should supply the authoritative central website
        | identity in the request/context. Never accept a provider API
        | key or model credential from the tenant.
        |
        */

        $websiteId =
            (int) (
                data_get(
                    $request,
                    'website_id'
                )
                ?? data_get(
                    $request,
                    'context.website_id'
                )
                ?? data_get(
                    $request,
                    'payload.context.website_id'
                )
                ?? 0
            );


        if ($websiteId <= 0) {
            throw new RuntimeException(
                'Site AI website context is missing.'
            );
        }


        $userId =
            (int) (
                data_get(
                    $request,
                    'user_id'
                )
                ?? data_get(
                    $request,
                    'context.user_id'
                )
                ?? 0
            );


        $userId =
            $userId > 0
                ? $userId
                : null;


        /*
        |--------------------------------------------------------------------------
        | PROMPT
        |--------------------------------------------------------------------------
        */

        $prompt =
            trim(
                (string) (
                    data_get(
                        $request,
                        'prompt'
                    )
                    ?? data_get(
                        $request,
                        'payload.prompt'
                    )
                    ?? data_get(
                        $request,
                        'prepared.prompt'
                    )
                    ?? ''
                )
            );


        if ($prompt === '') {
            throw new RuntimeException(
                'AI prompt is required.'
            );
        }


        /*
        |--------------------------------------------------------------------------
        | CAPABILITY CONTEXT
        |--------------------------------------------------------------------------
        |
        | "features", "about", "testimonials", etc. remain Site AI
        | editing targets.
        |
        | They are not OpenAI models or independent billing routes.
        | Central routing uses the canonical "site" route.
        |
        */

        $capability =
            (string) (
                data_get(
                    $request,
                    'capability'
                )
                ?? data_get(
                    $request,
                    'payload.capability'
                )
                ?? 'site'
            );


        $action =
            (string) (
                data_get(
                    $request,
                    'action'
                )
                ?? 'generate'
            );


        /*
         * Preserve useful Site AI context for auditing and future
         * capability-aware prompt/routing improvements.
         */
        $options = [
            'site_ai' => [
                'capability' =>
                    $capability,

                'action' =>
                    $action,

                'context' =>
                    (array) (
                        data_get(
                            $request,
                            'context'
                        )
                        ?? []
                    ),

                'payload' =>
                    (array) (
                        data_get(
                            $request,
                            'payload'
                        )
                        ?? []
                    ),
            ],
        ];


        /*
        |--------------------------------------------------------------------------
        | CENTRAL EXECUTION + BILLING
        |--------------------------------------------------------------------------
        |
        | CentralAiEngine performs:
        |
        | 1. AI credit preflight
        | 2. model routing
        | 3. provider request
        | 4. actual token metering
        | 5. provider-cost calculation
        | 6. Esubiz markup pricing
        | 7. exact AI-credit debit
        | 8. usage/transaction logging
        |
        */

        $result =
            $this->engine->execute(
                'site',
                $prompt,
                $userId,
                $websiteId,
                $options
            );


        if (
            !is_array(
                $result
            )
        ) {
            throw new RuntimeException(
                'Esubiz Central AI returned an invalid response.'
            );
        }


        return $result;
    }
}
