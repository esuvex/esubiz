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
