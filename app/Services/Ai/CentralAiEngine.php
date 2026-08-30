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


        /*
         * ESUBIZ_CENTRAL_AI_IMAGE_TOOL_SUPPORT_V1
         *
         * Image generation remains inside the same Central AI
         * authority as text generation:
         *
         * routing -> provider -> website credits -> usage log
         * -> pricing -> debit.
         *
         * Normal text requests are completely unaffected because
         * tools are only forwarded when explicitly requested.
         */
        if (
            !empty($options['tools'])
            && is_array($options['tools'])
        ) {
            $payload['tools'] =
                array_values(
                    $options['tools']
                );
        }


        if (
            isset($options['tool_choice'])
            && $options['tool_choice'] !== ''
            && $options['tool_choice'] !== null
        ) {
            $payload['tool_choice'] =
                $options['tool_choice'];
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


        /*
         * ESUBIZ_GPT_IMAGE_2_ACCOUNTING_CONTEXT_V1
         *
         * A Responses API request may be hosted by the centrally routed
         * text model while its image_generation tool performs the actual
         * image work with GPT-Image-2.
         *
         * Never silently price that image work as Luna/Terra/Sol.
         * Preserve the existing text calculation for ordinary requests
         * and capture the provider's complete usage payload for exact
         * GPT-Image-2 accounting.
         */
        $imageToolModelKey = null;

        foreach (
            (array) ($options['tools'] ?? [])
            as $tool
        ) {
            if (
                is_array($tool)
                && ($tool['type'] ?? null) === 'image_generation'
                && !empty($tool['model'])
            ) {
                $imageToolModelKey =
                    (string) $tool['model'];

                break;
            }
        }


        $providerCost =
            $this->calculateProviderCost(
                $model,
                $inputTokens,
                $cachedInputTokens,
                $outputTokens
            );


        $providerUsageSnapshot =
            is_array(
                data_get(
                    $data,
                    'usage'
                )
            )
                ? data_get(
                    $data,
                    'usage'
                )
                : [];


        /*
         * ESUBIZ_GPT_IMAGE_2_FINAL_BILLING_V1
         *
         * GPT-Image-2 economics are independent of the host text model.
         *
         * Verified Esubiz/OpenAI billing export rates:
         *
         * text input   = $5 / 1M
         * image input  = $8 / 1M
         * image output = $30 / 1M
         *
         * The export also establishes the historical average provider
         * cost of $4.192988 / 96 = $0.0436770833 per generated image.
         *
         * When explicit image-generation token usage is available in the
         * Responses output, calculate the exact provider cost.
         *
         * Otherwise use the verified historical average PER GENERATED
         * IMAGE rather than charging zero or pretending Luna/Terra
         * performed the image generation.
         */
        if ($imageToolModelKey === 'gpt-image-2') {

            $imageTextInputTokens = 0;
            $imageInputTokens = 0;
            $imageOutputTokens = 0;
            $generatedImageCount = 0;


            foreach (
                (array) data_get(
                    $data,
                    'output',
                    []
                )
                as $outputItem
            ) {
                if (!is_array($outputItem)) {
                    continue;
                }


                $outputType =
                    (string) (
                        $outputItem['type']
                        ?? ''
                    );


                if (
                    $outputType
                    === 'image_generation_call'
                ) {
                    $generatedImageCount++;


                    $imageTextInputTokens +=
                        (int) (
                            data_get(
                                $outputItem,
                                'usage.text_input_tokens',
                                data_get(
                                    $outputItem,
                                    'usage.input_tokens_details.text_tokens',
                                    0
                                )
                            )
                            ?? 0
                        );


                    $imageInputTokens +=
                        (int) (
                            data_get(
                                $outputItem,
                                'usage.image_input_tokens',
                                data_get(
                                    $outputItem,
                                    'usage.input_tokens_details.image_tokens',
                                    0
                                )
                            )
                            ?? 0
                        );


                    $imageOutputTokens +=
                        (int) (
                            data_get(
                                $outputItem,
                                'usage.image_output_tokens',
                                data_get(
                                    $outputItem,
                                    'usage.output_tokens',
                                    0
                                )
                            )
                            ?? 0
                        );
                }
            }


            if (
                $imageTextInputTokens > 0
                || $imageInputTokens > 0
                || $imageOutputTokens > 0
            ) {
                $providerCost =
                    (
                        $imageTextInputTokens
                        / 1000000
                        * 5.00
                    )
                    +
                    (
                        $imageInputTokens
                        / 1000000
                        * 8.00
                    )
                    +
                    (
                        $imageOutputTokens
                        / 1000000
                        * 30.00
                    );


                $imagePricingSource =
                    'exact_image_token_usage';
            } else {

                /*
                 * Historical provider-billing fallback derived from the
                 * Esubiz OpenAI cost export:
                 *
                 * $4.192988 total GPT-Image-2 cost
                 * / 96 requests
                 * = $0.0436770833 average provider cost per image.
                 */
                $generatedImageCount =
                    max(
                        1,
                        $generatedImageCount
                    );


                /*
                 * ESUBIZ_GPT_IMAGE_2_HISTORICAL_BUFFER_V1
                 *
                 * Historical average provider cost:
                 * $0.0436770833 per generated image.
                 *
                 * Apply a 20% estimation safety buffer while exact
                 * live image-token usage is unavailable.
                 *
                 * This buffer is part of the temporary provider-cost
                 * estimate. The normal Esubiz commercial markup is
                 * applied separately afterwards by AiPricingService.
                 */
                $historicalImageCostUsd =
                    0.0436770833;

                $historicalImageSafetyMultiplier =
                    1.20;

                $providerCost =
                    $generatedImageCount
                    * $historicalImageCostUsd
                    * $historicalImageSafetyMultiplier;


                $imagePricingSource =
                    'verified_historical_average_plus_20_percent';
            }
        } else {
            $imageTextInputTokens = 0;
            $imageInputTokens = 0;
            $imageOutputTokens = 0;
            $generatedImageCount = 0;
            $imagePricingSource = null;
        }


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

                                'image_tool_model' =>
                                    $imageToolModelKey,

                                'provider_usage' =>
                                    $providerUsageSnapshot,

                                'image_pricing_source' =>
                                    $imagePricingSource,

                                'generated_image_count' =>
                                    $generatedImageCount,

                                'image_text_input_tokens' =>
                                    $imageTextInputTokens,

                                'image_input_tokens' =>
                                    $imageInputTokens,

                                'image_output_tokens' =>
                                    $imageOutputTokens,
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
