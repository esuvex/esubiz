<?php

namespace App\Services\Ai;

use App\Models\Ai\AiCreditRule;
use App\Models\Ai\AiModel;
use App\Models\Ai\AiRoutingRule;
use App\Models\Ai\AiUsageLog;
use App\Models\Website;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use RuntimeException;
use Throwable;

class CentralAiEngine
{
    public function execute(
        string $routeKey,
        string $input,
        ?int $userId = null,
        ?int $websiteId = null,
        array $options = []
    ): array {

        $route =
            AiRoutingRule::query()
                ->with([
                    'model.provider',
                    'fallbackModel.provider',
                ])
                ->where(
                    'route_key',
                    $routeKey
                )
                ->where(
                    'is_active',
                    true
                )
                ->first();


        if (!$route) {
            throw new RuntimeException(
                "No active AI routing rule exists for [{$routeKey}]."
            );
        }


        if (
            !$route->model
            || !$route->model->is_active
        ) {
            throw new RuntimeException(
                "The primary AI model for [{$routeKey}] is unavailable."
            );
        }


        /*
         * BILLING AUTHORITY
         * -----------------
         *
         * OpenAI bills Esubiz.
         * Esubiz bills the website.
         *
         * A billable website request MUST have a valid
         * website and enough Esubiz AI Credits before
         * provider execution begins.
         */
        $website = null;

        if ($websiteId !== null) {

            $website =
                Website::query()
                    ->find($websiteId);

            if (!$website) {
                throw new RuntimeException(
                    'The website for this AI request does not exist.'
                );
            }


            /*
             * Minimum pre-flight requirement.
             *
             * Exact charge is not known until OpenAI returns
             * actual usage, so we require at least one credit
             * before allowing provider consumption.
             */
            $minimumCredits =
                max(
                    1,
                    (float) (
                        $options['minimum_preflight_credits']
                        ?? 1
                    )
                );


            $availableCredits =
                app(
                    AiCreditService::class
                )->balance(
                    $website
                );


            if (
                $availableCredits
                < $minimumCredits
            ) {
                throw new RuntimeException(
                    'Insufficient AI credits. Please purchase AI Credits to continue.'
                );
            }
        }


        $requestUuid =
            (string) Str::uuid();


        $log =
            AiUsageLog::query()
                ->create([
                    'request_uuid' =>
                        $requestUuid,

                    'route_key' =>
                        $routeKey,

                    'user_id' =>
                        $userId,

                    'website_id' =>
                        $websiteId,

                    'status' =>
                        'pending',

                    'metadata' => [
                        'source' =>
                            'central-ai-engine',
                    ],
                ]);


        try {

            return $this->attempt(
                $route->model,
                $routeKey,
                $input,
                $log,
                false,
                $options
            );


        } catch (Throwable $primaryException) {

            $fallback =
                $route->fallbackModel;


            if (
                !$fallback
                || !$fallback->is_active
                || $fallback->id
                    === $route->model->id
            ) {

                $log->update([
                    'status' =>
                        'failed',

                    'error_message' =>
                        $primaryException
                            ->getMessage(),

                    'completed_at' =>
                        now(),
                ]);


                throw $primaryException;
            }


            try {

                return $this->attempt(
                    $fallback,
                    $routeKey,
                    $input,
                    $log,
                    true,
                    $options
                );


            } catch (Throwable $fallbackException) {

                $log->update([
                    'status' =>
                        'failed',

                    'used_fallback' =>
                        true,

                    'error_message' =>
                        $fallbackException
                            ->getMessage(),

                    'metadata' => [
                        'source' =>
                            'central-ai-engine',

                        'primary_error' =>
                            $primaryException
                                ->getMessage(),

                        'fallback_error' =>
                            $fallbackException
                                ->getMessage(),
                    ],

                    'completed_at' =>
                        now(),
                ]);


                throw $fallbackException;
            }
        }
    }


    protected function attempt(
        AiModel $model,
        string $routeKey,
        string $input,
        AiUsageLog $log,
        bool $usedFallback,
        array $options
    ): array {

        $provider =
            $model->provider;


        if (
            !$provider
            || !$provider->is_active
        ) {
            throw new RuntimeException(
                "AI provider is unavailable for model [{$model->model_key}]."
            );
        }


        $secrets =
            $provider->secret_payload
            ?? [];


        $apiKey =
            trim(
                (string) (
                    $secrets['api_key']
                    ?? ''
                )
            );


        if ($apiKey === '') {
            throw new RuntimeException(
                "AI provider [{$provider->name}] has no API credential."
            );
        }


        $baseUrl =
            rtrim(
                (string) (
                    $provider->base_url
                    ?: 'https://api.openai.com/v1'
                ),
                '/'
            );


        $payload = [
            'model' =>
                $model->model_key,

            'input' =>
                        $this->buildSiteAiProviderInput(
                            $routeKey,
                            $input,
                            $options
                        ),
        ];


        if (
            !empty(
                $options['instructions']
            )
        ) {
            $payload['instructions'] =
                (string)
                $options['instructions'];
        }


        if (
            isset(
                $options['max_output_tokens']
            )
        ) {
            $payload['max_output_tokens'] =
                max(
                    1,
                    (int)
                    $options[
                        'max_output_tokens'
                    ]
                );
        }


        $response =
            Http::withToken(
                $apiKey
            )
            ->acceptJson()
            ->asJson()
            ->timeout(
                (int) (
                    $options['timeout']
                    ?? 120
                )
            )
            ->retry(
                2,
                500,
                throw: false
            )
            ->post(
                $baseUrl
                . '/responses',
                $payload
            );


        if (!$response->successful()) {

            $message =
                data_get(
                    $response->json(),
                    'error.message'
                )
                ?: (
                    'OpenAI request failed with HTTP '
                    . $response->status()
                );


            throw new RuntimeException(
                $message
            );
        }


        $data =
            $response->json();


        $inputTokens =
            (int) data_get(
                $data,
                'usage.input_tokens',
                0
            );


        $cachedInputTokens =
            (int) (
                data_get(
                    $data,
                    'usage.input_tokens_details.cached_tokens',
                    0
                )
                ?? 0
            );


        $outputTokens =
            (int) data_get(
                $data,
                'usage.output_tokens',
                0
            );


        $totalTokens =
            (int) data_get(
                $data,
                'usage.total_tokens',
                (
                    $inputTokens
                    + $outputTokens
                )
            );


        $providerCost =
            $this->calculateProviderCost(
                $model,
                $inputTokens,
                $cachedInputTokens,
                $outputTokens
            );


        /*
         * Convert the actual provider cost into the
         * Esubiz customer selling price.
         *
         * OpenAI bills Esubiz.
         * Esubiz bills the website in AI Credits.
         */
        $pricing =
            app(
                AiPricingService::class
            )->quote(
                $providerCost
            );


        $credits =
            (float) (
                $pricing['credits']
                ?? 0
            );


        /*
         * EXACT ESUBIZ AI CREDIT CHARGE
         * -----------------------------
         *
         * Provider execution has succeeded and actual usage
         * is now known.
         *
         * AiCreditService uses:
         *
         * - database transaction
         * - SELECT ... FOR UPDATE
         * - unique request_key
         *
         * therefore the same AI request cannot be charged
         * twice.
         */
        $creditTransaction = null;


        if (
            $log->website_id !== null
            && $credits > 0
        ) {

            try {

                $creditTransaction =
                    app(
                        AiCreditService::class
                    )->debit(
                        (int) $log->website_id,
                        $credits,
                        'ai-usage:' . $log->request_uuid,
                        [
                            'user_id' =>
                                $log->user_id,

                            'workspace_id' =>
                                Website::query()
                                    ->whereKey(
                                        $log->website_id
                                    )
                                    ->value(
                                        'workspace_id'
                                    ),

                            'ai_usage_log_id' =>
                                $log->id,

                            'ai_model_id' =>
                                $model->id,

                            'route_key' =>
                                $routeKey,

                            'type' =>
                                'ai_usage',

                            'source_type' =>
                                'central_ai_engine',

                            'source_id' =>
                                $log->request_uuid,

                            'description' =>
                                'Esubiz AI usage - '
                                . $routeKey,

                            'metadata' => [
                                'provider' =>
                                    $provider->slug,

                                'model' =>
                                    $model->model_key,

                                'provider_cost_usd' =>
                                    $providerCost,

                                'pricing' =>
                                    $pricing,

                                'used_fallback' =>
                                    $usedFallback,
                            ],
                        ]
                    );

            } catch (Throwable $billingException) {

                /*
                 * OpenAI has already completed the request.
                 *
                 * Never pretend this request was free.
                 * Record the billing failure explicitly.
                 */
                $log->update([
                    'ai_provider_id' =>
                        $provider->id,

                    'ai_model_id' =>
                        $model->id,

                    'provider_request_id' =>
                        data_get(
                            $data,
                            'id'
                        ),

                    'status' =>
                        'billing_failed',

                    'used_fallback' =>
                        $usedFallback,

                    'input_tokens' =>
                        $inputTokens,

                    'cached_input_tokens' =>
                        $cachedInputTokens,

                    'output_tokens' =>
                        $outputTokens,

                    'total_tokens' =>
                        $totalTokens,

                    'provider_cost_usd' =>
                        $providerCost,

                    'credits_charged' =>
                        0,

                    'error_message' =>
                        $billingException
                            ->getMessage(),

                    'metadata' => [
                        'source' =>
                            'central-ai-engine',

                        'billing_failure' =>
                            true,

                        'calculated_credits' =>
                            $credits,

                        'pricing' =>
                            $pricing,

                        'model_key' =>
                            $model->model_key,
                    ],

                    'completed_at' =>
                        now(),
                ]);


                throw new RuntimeException(
                    'AI request completed but Esubiz could not charge the required AI Credits: '
                    . $billingException->getMessage(),
                    0,
                    $billingException
                );
            }
        }


        $text =
            $this->extractText(
                $data
            );


        $log->update([
            'ai_provider_id' =>
                $provider->id,

            'ai_model_id' =>
                $model->id,

            'provider_request_id' =>
                data_get(
                    $data,
                    'id'
                ),

            'status' =>
                'completed',

            'used_fallback' =>
                $usedFallback,

            'input_tokens' =>
                $inputTokens,

            'cached_input_tokens' =>
                $cachedInputTokens,

            'output_tokens' =>
                $outputTokens,

            'total_tokens' =>
                $totalTokens,

            'provider_cost_usd' =>
                $providerCost,

            /*
             * Actual Esubiz AI Credits deducted from
             * the website central balance.
             */
            'credits_charged' =>
                $creditTransaction
                    ? (float) data_get($creditTransaction, 'credits')
                    : 0,

            'metadata' => [
                'source' =>
                    'central-ai-engine',

                'calculated_credits' =>
                    $credits,

                'pricing' =>
                    $pricing,

                'credit_transaction_id' =>
                    data_get($creditTransaction, 'id'),

                'credit_transaction_reference' =>
                    data_get($creditTransaction, 'reference'),

                'balance_before' =>
                    $creditTransaction
                        ? (float) data_get($creditTransaction, 'balance_before')
                        : null,

                'balance_after' =>
                    $creditTransaction
                        ? (float) data_get($creditTransaction, 'balance_after')
                        : null,

                'model_key' =>
                    $model->model_key,
            ],

            'completed_at' =>
                now(),
        ]);


        return [
            'success' =>
                true,

            'request_uuid' =>
                $log->request_uuid,

            'route_key' =>
                $routeKey,

            'model' =>
                $model->model_key,

            'used_fallback' =>
                $usedFallback,

            'text' =>
                $text,

            'usage' => [
                'input_tokens' =>
                    $inputTokens,

                'cached_input_tokens' =>
                    $cachedInputTokens,

                'output_tokens' =>
                    $outputTokens,

                'total_tokens' =>
                    $totalTokens,
            ],

            'provider_cost_usd' =>
                $providerCost,

            'calculated_credits' =>
                $credits,

            'pricing' =>
                $pricing,

            'billing' => [
                'charged' =>
                    $creditTransaction !== null,

                'credits' =>
                    $creditTransaction
                        ? (float) data_get($creditTransaction, 'credits')
                        : 0,

                'transaction_id' =>
                    data_get($creditTransaction, 'id'),

                'reference' =>
                    data_get($creditTransaction, 'reference'),

                'balance_before' =>
                    $creditTransaction
                        ? (float) data_get($creditTransaction, 'balance_before')
                        : null,

                'balance_after' =>
                    $creditTransaction
                        ? (float) data_get($creditTransaction, 'balance_after')
                        : null,
            ],

            'raw' =>
                $data,
        ];
    }


    protected function calculateProviderCost(
        AiModel $model,
        int $inputTokens,
        int $cachedInputTokens,
        int $outputTokens
    ): float {

        $normalInputTokens =
            max(
                0,
                $inputTokens
                - $cachedInputTokens
            );


        $normalInputCost =
            (
                $normalInputTokens
                / 1000000
            )
            * (float)
                $model
                    ->input_cost_per_million;


        $cachedInputCost =
            (
                $cachedInputTokens
                / 1000000
            )
            * (float)
                $model
                    ->cached_input_cost_per_million;


        $outputCost =
            (
                $outputTokens
                / 1000000
            )
            * (float)
                $model
                    ->output_cost_per_million;


        return round(
            $normalInputCost
            + $cachedInputCost
            + $outputCost,
            8
        );
    }


    protected function extractText(
        array $data
    ): string {

        $direct =
            data_get(
                $data,
                'output_text'
            );


        if (
            is_string(
                $direct
            )
            && $direct !== ''
        ) {
            return $direct;
        }


        $parts = [];


        foreach (
            (
                $data['output']
                ?? []
            )
            as $output
        ) {

            foreach (
                (
                    $output['content']
                    ?? []
                )
                as $content
            ) {

                $text =
                    $content['text']
                    ?? null;


                if (
                    is_string(
                        $text
                    )
                    && $text !== ''
                ) {
                    $parts[] =
                        $text;
                }
            }
        }


        return trim(
            implode(
                "\n",
                $parts
            )
        );
    }


    /*
     * SAFE_SITE_AI_VISION_INPUT
     *
     * Convert Site AI reference images into the OpenAI Responses
     * multimodal input contract.
     *
     * Text-only AI requests remain unchanged.
     */
    protected function buildSiteAiProviderInput(
        string $routeKey,
        string $input,
        array $options
    ): string|array {

        if ($routeKey !== 'site') {
            return $input;
        }


        /*
         * SITE_AI_CONTEXT_VISION_FALLBACK
         *
         * Canonical location remains payload.reference_images.
         * context.reference_images is accepted as the transport
         * fallback used by SiteAiEngine.
         */
        $referenceImages =
            (array) (
                data_get(
                    $options,
                    'payload.reference_images'
                )
                ?? data_get(
                    $options,
                    'context.reference_images'
                )
                ?? []
            );


        if (empty($referenceImages)) {
            return $input;
        }


        $content = [
            [
                'type' =>
                    'input_text',

                'text' =>
                    $input,
            ],
        ];


        foreach (
            array_slice(
                $referenceImages,
                0,
                5
            )
            as $referenceImage
        ) {

            $imageUrl =
                trim(
                    (string) data_get(
                        $referenceImage,
                        'data_url',
                        ''
                    )
                );


            if (
                $imageUrl === ''
                || !str_starts_with(
                    $imageUrl,
                    'data:image/'
                )
            ) {
                continue;
            }


            $content[] = [
                'type' =>
                    'input_image',

                'image_url' =>
                    $imageUrl,
            ];
        }


        /*
         * No valid image survived validation.
         */
        if (count($content) === 1) {
            return $input;
        }


        return [
            [
                'role' =>
                    'user',

                'content' =>
                    $content,
            ],
        ];
    }

}
