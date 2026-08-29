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

        /*
         * SITE_AI_FINAL_VISION_OPTION_NORMALIZATION
         *
         * Normalize Site AI reference photos immediately before
         * CentralAiEngine execution.
         *
         * This touches only AI request data. It does not affect
         * chat appearance, avatars, names or text-only requests.
         */
        $referenceImages =
            (array) (
                data_get(
                    $request,
                    'payload.reference_images'
                )
                ?? data_get(
                    $request,
                    'context.reference_images'
                )
                ?? []
            );


        if (!empty($referenceImages)) {

            $referenceImages =
                array_slice(
                    $referenceImages,
                    0,
                    5
                );

            $options['payload'] =
                (array) (
                    $options['payload']
                    ?? []
                );

            $options['context'] =
                (array) (
                    $options['context']
                    ?? []
                );

            $options['payload']['reference_images'] =
                $referenceImages;

            $options['payload']['reference_images_count'] =
                count(
                    $referenceImages
                );

            $options['context']['reference_images'] =
                $referenceImages;
        }


        /*
         * ESUBIZ_OFFSERVER_CENTRAL_CHAT_LIMITS_V1
         *
         * Central Admin limits are authoritative here too.
         *
         * This provider is the trusted central boundary used by
         * Site AI, including authenticated off-server installations.
         * Off-server JavaScript/PHP cannot raise these limits.
         */
        $centralChatLimits =
            \Illuminate\Support\Facades\DB::table(
                'central_ai_chat_settings'
            )
                ->orderBy('id')
                ->first();

        $maxMessageCharacters =
            max(
                100,
                (int) (
                    $centralChatLimits
                        ->max_message_characters
                    ?? 10000
                )
            );

        $maxConversationMessages =
            max(
                1,
                (int) (
                    $centralChatLimits
                        ->max_conversation_messages
                    ?? 100
                )
            );

        $maxPhotosPerMessage =
            max(
                0,
                (int) (
                    $centralChatLimits
                        ->max_photos_per_message
                    ?? 5
                )
            );

        $maxPhotoSizeMb =
            max(
                1,
                (int) (
                    $centralChatLimits
                        ->max_photo_size_mb
                    ?? 10
                )
            );


        /*
         * Character limit.
         */
        if (
            mb_strlen(
                (string) $prompt
            )
            > $maxMessageCharacters
        ) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'prompt' => [
                    'Maximum message length is '
                    . $maxMessageCharacters
                    . ' characters.',
                ],
            ]);
        }


        /*
         * Photo count + actual transported image-size limit.
         *
         * The working vision transport already normalizes images
         * into $options['payload']['reference_images'].
         * We only inspect it; we do not alter it.
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

        if (
            count($referenceImages)
            > $maxPhotosPerMessage
        ) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'reference_images' => [
                    'A maximum of '
                    . $maxPhotosPerMessage
                    . ' photos may be attached to one message.',
                ],
            ]);
        }

        foreach (
            $referenceImages
            as $referenceImage
        ) {
            $dataUrl =
                trim(
                    (string) data_get(
                        $referenceImage,
                        'data_url',
                        ''
                    )
                );

            if (
                $dataUrl === ''
                || !str_starts_with(
                    $dataUrl,
                    'data:image/'
                )
                || !str_contains(
                    $dataUrl,
                    ','
                )
            ) {
                continue;
            }

            [, $encodedImage] =
                explode(
                    ',',
                    $dataUrl,
                    2
                );

            $padding =
                substr_count(
                    substr(
                        $encodedImage,
                        -2
                    ),
                    '='
                );

            $imageBytes =
                max(
                    0,
                    (
                        (int) floor(
                            strlen($encodedImage)
                            * 3
                            / 4
                        )
                    )
                    - $padding
                );

            if (
                $imageBytes
                > (
                    $maxPhotoSizeMb
                    * 1024
                    * 1024
                )
            ) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'reference_images' => [
                        'Each photo must be '
                        . $maxPhotoSizeMb
                        . ' MB or smaller.',
                    ],
                ]);
            }
        }


        /*
         * Conversation-length contract.
         *
         * Existing clients may send conversation/history/messages
         * under different compatible context keys. We accept the
         * existing forms without changing the request contract.
         */
        $conversationMessages =
            data_get(
                $options,
                'context.conversation'
            )
            ?? data_get(
                $options,
                'context.messages'
            )
            ?? data_get(
                $options,
                'context.history'
            );

        if (
            is_array($conversationMessages)
            && count($conversationMessages)
                > $maxConversationMessages
        ) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'conversation' => [
                    'This conversation has reached the maximum of '
                    . $maxConversationMessages
                    . ' messages. Start a new chat to continue.',
                ],
            ]);
        }


        /*
         * Publish the authoritative limits into the request context.
         *
         * This gives SaaS/off-server consumers a common central
         * contract whenever response/context metadata is propagated.
         */
        $options['context']['chat_limits'] = [
            'max_message_characters' =>
                $maxMessageCharacters,

            'max_conversation_messages' =>
                $maxConversationMessages,

            'max_photos_per_message' =>
                $maxPhotosPerMessage,

            'max_photo_size_mb' =>
                $maxPhotoSizeMb,
        ];


        /*
         * ESUBIZ_AUTHORITATIVE_AI_INSTRUCTIONS_V1
         *
         * Site AI context previously reached CentralAiEngine only
         * as metadata. CentralAiEngine sends actual model-level
         * instructions through $options['instructions'], so publish
         * the authoritative Esubiz identity + current job there.
         *
         * Do not modify payload/reference_images here. The existing
         * multimodal vision transport remains untouched.
         */
        $siteAiContext =
            (array) (
                $options['site_ai']['context']
                ?? []
            );

        $identityInstruction =
            trim(
                (string) (
                    $siteAiContext[
                        'assistant_identity_instruction'
                    ]
                    ?? ''
                )
            );

        $jobInstruction =
            trim(
                (string) (
                    $siteAiContext[
                        'assistant_job_instruction'
                    ]
                    ?? ''
                )
            );

        $assistantName =
            trim(
                (string) (
                    $siteAiContext[
                        'assistant_name'
                    ]
                    ?? 'Esubiz AI'
                )
            );

        $websiteName =
            trim(
                (string) (
                    $siteAiContext[
                        'website_name'
                    ]
                    ?? 'this website'
                )
            );

        $capabilityInstruction =
            'Current ESUBIZ Site AI capability: "'
            . $capability
            . '". Current action: "'
            . $action
            . '".';


        /*
         * ========================================================
         * ESUBIZ_EXPLICIT_THEME_AI_SCOPE_V1
         * ========================================================
         *
         * CentralAiEngine receives Site AI metadata separately from
         * the actual model instructions. Publish the selected theme
         * functions and their exact editable targets directly into
         * the model instruction stream.
         */
        $selectedFunctions =
            array_values(
                array_filter(
                    array_map(
                        static fn ($value) =>
                            trim((string) $value),
                        (array) (
                            data_get(
                                $request,
                                'payload.selected_functions'
                            )
                            ?? data_get(
                                $options,
                                'site_ai.payload.selected_functions'
                            )
                            ?? $siteAiContext[
                                'selected_functions'
                            ]
                            ?? []
                        )
                    ),
                    static fn ($value) =>
                        $value !== ''
                )
            );


        $themeAiManifest =
            (array) (
                $siteAiContext[
                    'theme_ai_manifest'
                ]
                ?? data_get(
                    $request,
                    'context.theme_ai_manifest'
                )
                ?? []
            );


        $explicitTargets =
            array_values(
                array_unique(
                    array_filter(
                        array_map(
                            static fn ($value) =>
                                trim((string) $value),
                            (array) (
                                $themeAiManifest[
                                    'targets'
                                ]
                                ?? []
                            )
                        ),
                        static fn ($value) =>
                            $value !== ''
                    )
                )
            );


        /*
         * Fallback to targets declared inside the currently scoped
         * functions if the manifest has not flattened them.
         */
        if (empty($explicitTargets)) {
            foreach (
                (array) (
                    $themeAiManifest[
                        'functions'
                    ]
                    ?? []
                )
                as $function
            ) {
                if (!is_array($function)) {
                    continue;
                }

                foreach (
                    (array) (
                        $function[
                            'targets'
                        ]
                        ?? []
                    )
                    as $target
                ) {
                    $target =
                        trim(
                            (string) $target
                        );

                    if (
                        $target !== ''
                        && !in_array(
                            $target,
                            $explicitTargets,
                            true
                        )
                    ) {
                        $explicitTargets[] =
                            $target;
                    }
                }
            }
        }


        $themeScopeInstruction = '';

        if (
            $capability === 'theme.homepage'
            && !empty($explicitTargets)
        ) {
            $themeScopeInstruction =
                'This is a website-edit generation request. '
                . (
                    !empty($selectedFunctions)
                        ? (
                            'The user selected these homepage functions: '
                            . implode(
                                ', ',
                                $selectedFunctions
                            )
                            . '. '
                        )
                        : ''
                )
                . 'Generate useful proposed content for the requested job '
                . 'using the applicable editable targets below. '
                . 'Do NOT return an empty changes array when the request '
                . 'can be fulfilled. '
                . 'Editable targets: '
                . implode(
                    ', ',
                    $explicitTargets
                )
                . '. '
                . 'For every generated field, return one object in the '
                . 'top-level changes array using the exact target name. '
                . 'Do not invent any other target.';
        }

        $baseIdentityInstruction =
            'You are "'
            . $assistantName
            . '", an official ESUBIZ AI assistant. '
            . 'ESUBIZ is a product of Esuvex Limited. '
            . 'You work for ESUBIZ. '
            . 'Your public assistant identity is "'
            . $assistantName
            . '". '
            . 'Always identify yourself using that Esubiz persona name. '
            . 'Never identify yourself as ChatGPT, GPT, OpenAI, '
            . 'or by an underlying AI model/provider name. '
            . 'If asked who you work for, answer ESUBIZ. '
            . 'When relevant, state that ESUBIZ is a product of '
            . 'Esuvex Limited. '
            . 'You are currently assisting the website "'
            . $websiteName
            . '".';

        /*
         * ========================================================
         * ESUBIZ_STRUCTURED_PROPOSAL_INSTRUCTIONS_V1
         * ========================================================
         *
         * Proposal-mode output is machine-readable so Esubiz can:
         *
         * validate -> preview -> approve -> apply.
         *
         * This does NOT apply to ordinary AI conversation.
         */
        /*
         * ESUBIZ_THEME_HOMEPAGE_PROPOSAL_ENFORCEMENT_V1
         *
         * Theme homepage generation must always produce a pending
         * structured proposal for Preview / Approve / Apply.
         *
         * Do not depend solely on proposal_mode surviving every
         * transport/context layer.
         */
        $proposalMode =
            (
                $capability === 'theme.homepage'
                && $action === 'generate'
            )
            || filter_var(
                $siteAiContext[
                    'proposal_mode'
                ]
                ?? false,
                FILTER_VALIDATE_BOOLEAN
            );


        $proposalInstruction =
            $proposalMode
                ? (
                    'This request is in ESUBIZ proposal mode. '
                    . 'Return only a structured proposal result that '
                    . 'Esubiz can validate and preview. '
                    . 'Do not return conversational prose outside the '
                    . 'structured result. '
                    . 'Use a top-level "changes" array. '
                    . 'Each change must contain exactly the applicable '
                    . 'target, type, and value. '
                    . 'The target must exactly match an editable target '
                    . 'declared in the supplied capability manifest. '
                    . 'Never invent, rename, broaden, or modify a target '
                    . 'outside the supplied manifest. '
                    . 'Use only targets required for the requested job; '
                    . 'do not change unrelated fields. '
                    . 'Valid change shape: '
                    . '{"target":"declared_target","type":"text","value":"generated value"}. '
                    . 'For structured values, place the structured data '
                    . 'inside "value". '
                    . 'For media or file proposals, use the applicable '
                    . 'declared media/file type and include only references '
                    . 'that Esubiz can later persist through its destination '
                    . 'system. '
                    . 'The complete response must be a JSON object shaped '
                    . 'as {"changes":[...]}. '
                    . 'Do not wrap the JSON in Markdown code fences.'
                )
                : '';


        $instructions = array_values(
            array_filter(
                [
                    $baseIdentityInstruction,
                    $identityInstruction,
                    $jobInstruction,
                    $capabilityInstruction,
                    $themeScopeInstruction,
                    $proposalInstruction,

                    'Treat the supplied ESUBIZ capability manifest, '
                    . 'page context, website context, theme context, '
                    . 'section context and action as authoritative. '
                    . 'Perform the job of the page or feature where '
                    . 'you are currently being used. Do not invent '
                    . 'editable fields or capabilities that ESUBIZ '
                    . 'has not supplied.',

                    'When working on theme customization, respect the '
                    . 'active theme configuration and the exact '
                    . 'section or field supplied by ESUBIZ. Generated '
                    . 'changes are proposals until the ESUBIZ '
                    . 'application explicitly approves and applies them.',
                ],
                static fn ($value) =>
                    trim((string) $value) !== ''
            )
        );

        $options['instructions'] =
            implode(
                "\n\n",
                $instructions
            );


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


        /*
         * ========================================================
         * ESUBIZ_STRUCTURED_PROPOSAL_RESPONSE_NORMALIZATION_V2
         * ========================================================
         *
         * Provider-independent structured proposal extraction.
         *
         * CentralAiEngine/provider adapters may nest generated model
         * output differently. Recursively inspect the complete result
         * and extract ONLY a valid structured object containing
         * "changes" or "targets".
         *
         * Existing SiteAiProposalFactory manifest validation remains
         * authoritative after this normalization.
         */
        if (
            $proposalMode
            && empty($result['changes'])
            && empty($result['targets'])
        ) {
            $structuredProposal = null;

            $decodeStructuredProposal =
                static function ($value): ?array {
                    if (!is_string($value)) {
                        return null;
                    }

                    $value = trim($value);

                    if ($value === '') {
                        return null;
                    }

                    /*
                     * Remove optional Markdown fences.
                     */
                    $value = preg_replace(
                        '/^```(?:json)?\\s*/i',
                        '',
                        $value
                    );

                    $value = preg_replace(
                        '/\\s*```$/',
                        '',
                        (string) $value
                    );

                    $value = trim(
                        (string) $value
                    );

                    /*
                     * First attempt: entire string is JSON.
                     */
                    $decoded =
                        json_decode(
                            $value,
                            true
                        );

                    if (
                        is_array($decoded)
                        && (
                            isset($decoded['changes'])
                            || isset($decoded['targets'])
                        )
                    ) {
                        return $decoded;
                    }

                    /*
                     * Second attempt: provider wrapped JSON in other
                     * textual content. Extract the widest JSON object
                     * and validate it before accepting it.
                     */
                    $firstBrace =
                        strpos(
                            $value,
                            '{'
                        );

                    $lastBrace =
                        strrpos(
                            $value,
                            '}'
                        );

                    if (
                        $firstBrace !== false
                        && $lastBrace !== false
                        && $lastBrace > $firstBrace
                    ) {
                        $candidate =
                            substr(
                                $value,
                                $firstBrace,
                                $lastBrace
                                    - $firstBrace
                                    + 1
                            );

                        $decoded =
                            json_decode(
                                $candidate,
                                true
                            );

                        if (
                            is_array($decoded)
                            && (
                                isset($decoded['changes'])
                                || isset($decoded['targets'])
                            )
                        ) {
                            return $decoded;
                        }
                    }

                    return null;
                };


            $walkResult = null;

            $walkResult =
                static function ($value)
                use (
                    &$walkResult,
                    $decodeStructuredProposal
                ): ?array {
                    if (is_string($value)) {
                        return
                            $decodeStructuredProposal(
                                $value
                            );
                    }

                    if (!is_array($value)) {
                        return null;
                    }

                    /*
                     * The current node itself may already be the
                     * structured proposal.
                     */
                    if (
                        isset($value['changes'])
                        && is_array(
                            $value['changes']
                        )
                    ) {
                        return $value;
                    }

                    if (
                        isset($value['targets'])
                        && is_array(
                            $value['targets']
                        )
                    ) {
                        return $value;
                    }

                    foreach (
                        $value
                        as $nestedValue
                    ) {
                        $found =
                            $walkResult(
                                $nestedValue
                            );

                        if ($found !== null) {
                            return $found;
                        }
                    }

                    return null;
                };


            $structuredProposal =
                $walkResult(
                    $result
                );


            if ($structuredProposal !== null) {
                if (
                    isset($structuredProposal['changes'])
                    && is_array(
                        $structuredProposal['changes']
                    )
                ) {
                    $result['changes'] =
                        array_values(
                            $structuredProposal['changes']
                        );
                }

                if (
                    isset($structuredProposal['targets'])
                    && is_array(
                        $structuredProposal['targets']
                    )
                ) {
                    $result['targets'] =
                        $structuredProposal['targets'];
                }

                if (
                    isset($structuredProposal['summary'])
                    && is_string(
                        $structuredProposal['summary']
                    )
                ) {
                    $result['summary'] =
                        $structuredProposal['summary'];
                }
            }
        }


        /*
         * ESUBIZ_THEME_HOMEPAGE_IMAGE_GENERATION_V1
         *
         * A theme function may expose image targets such as:
         *
         * hero_image_path
         * about_image_path
         *
         * Text generation remains the first pass.
         * Image-capable targets then use the central Esubiz AI
         * engine and become normal proposal items.
         *
         * Nothing is persisted at generation time.
         * The generated image exists only as proposal asset data
         * until Approve & Apply.
         */
        if (
            $capability === 'theme.homepage'
            && $action === 'generate'
        ) {
            $result =
                $this->attachThemeHomepageImages(
                    $result,
                    $request,
                    $siteAiContext,
                    $prompt,
                    $userId,
                    $websiteId
                );
        }


        return $result;
    }

    /**
     * Generate proposal-only images for image-capable
     * Theme Homepage functions.
     */
    protected function attachThemeHomepageImages(
        array $result,
        array $request,
        array $siteAiContext,
        string $prompt,
        ?int $userId,
        ?int $websiteId
    ): array {

        $selectedFunctions =
            (array) (
                data_get(
                    $request,
                    'payload.selected_functions'
                )
                ?? data_get(
                    $siteAiContext,
                    'selected_functions'
                )
                ?? []
            );


        $manifest =
            (array) (
                data_get(
                    $siteAiContext,
                    'theme_ai_manifest'
                )
                ?? data_get(
                    $request,
                    'context.theme_ai_manifest'
                )
                ?? []
            );


        $functions =
            (array) (
                $manifest['functions']
                ?? []
            );


        if (
            empty($selectedFunctions)
            || empty($functions)
        ) {
            return $result;
        }


        $existingTargets = [];

        foreach (
            (array) (
                $result['changes']
                ?? []
            )
            as $change
        ) {
            if (!is_array($change)) {
                continue;
            }

            $existingTarget =
                trim(
                    (string) (
                        $change['target']
                        ?? $change['target_key']
                        ?? ''
                    )
                );

            if ($existingTarget !== '') {
                $existingTargets[] =
                    $existingTarget;
            }
        }


        foreach (
            $selectedFunctions
            as $functionKey
        ) {
            $functionKey =
                trim(
                    (string) $functionKey
                );

            if ($functionKey === '') {
                continue;
            }


            $function =
                $functions[
                    $functionKey
                ]
                ?? null;


            if (!is_array($function)) {
                continue;
            }


            foreach (
                (array) (
                    $function['targets']
                    ?? []
                )
                as $target
            ) {
                $target =
                    trim(
                        (string) $target
                    );


                /*
                 * ESUBIZ_THEME_AI_IMAGE_TARGETS_V2
                 *
                 * Standard section images continue to use *_image_path.
                 * Theme-owned branding assets use their established
                 * logo/favicon path keys.
                 */
                $isImageTarget =
                    $target !== ''
                    && (
                        str_ends_with(
                            $target,
                            '_image_path'
                        )
                        || in_array(
                            $target,
                            [
                                'logo_path',
                                'footer_logo_path',
                                'favicon_path',
                            ],
                            true
                        )
                    );

                if (!$isImageTarget) {
                    continue;
                }


                /*
                 * ESUBIZ_THEME_AI_REAL_IMAGE_OVERRIDE_V2
                 *
                 * The text model may have returned a stock URL for the
                 * same *_image_path target.
                 *
                 * Remove that proposal item before invoking the real image
                 * generator. Image targets are owned exclusively by the
                 * image-generation path.
                 */
                $result['changes'] =
                    array_values(
                        array_filter(
                            (array) (
                                $result['changes']
                                ?? []
                            ),
                            function ($change) use ($target): bool {
                                if (!is_array($change)) {
                                    return true;
                                }

                                $changeTarget =
                                    trim(
                                        (string) (
                                            $change['target']
                                            ?? $change['target_key']
                                            ?? ''
                                        )
                                    );

                                return $changeTarget !== $target;
                            }
                        )
                    );


                /*
                 * ESUBIZ_THEME_AI_TARGET_AWARE_IMAGE_PROMPT_V1
                 */
                if (
                    in_array(
                        $target,
                        [
                            'logo_path',
                            'footer_logo_path',
                        ],
                        true
                    )
                ) {
                    $imagePrompt =
                        'Create one professional brand logo for this website. '
                        . 'Use the business identity, industry and direction '
                        . 'described in this website request: '
                        . $prompt
                        . '. '
                        . 'The logo must be clean, distinctive, professional, '
                        . 'simple enough for a website header and footer, and '
                        . 'visually strong at small sizes. '
                        . 'Do not create a photograph, mockup, website UI, '
                        . 'watermark, background scene or unrelated decoration.';
                } elseif (
                    $target === 'favicon_path'
                ) {
                    $imagePrompt =
                        'Create one simple favicon-style brand mark for this '
                        . 'website based on this website request: '
                        . $prompt
                        . '. '
                        . 'Use a bold, recognizable symbol or monogram that '
                        . 'remains clear at very small sizes. '
                        . 'Use a simple centered composition. '
                        . 'Do not create a photograph, mockup, website UI, '
                        . 'background scene, watermark or detailed illustration.';
                } else {
                    $imagePrompt =
                        'Create one professional landscape website photograph '
                        . 'for the "'
                        . $functionKey
                        . '" homepage section. '
                        . 'It must visually support this website request: '
                        . $prompt
                        . '. '
                        . 'The result must be suitable for a modern commercial '
                        . 'website section, realistic, polished and naturally '
                        . 'composed. '
                        . 'Do not place words, typography, logos, UI elements, '
                        . 'watermarks or fake branding inside the image.';
                }


                $imageResult =
                    $this->engine->execute(
                        'site',
                        $imagePrompt,
                        $userId,
                        $websiteId,
                        [
                            /*
                             * The host request continues through the
                             * centrally routed Site AI model while the
                             * image-generation tool delegates actual
                             * rendering to the image model.
                             */
                            'tools' => [
                                [
                                    'type' =>
                                        'image_generation',

                                    'model' =>
                                        'gpt-image-2',

                                    'size' =>
                                        '1536x1024',

                                    'quality' =>
                                        'medium',

                                    'output_format' =>
                                        'jpeg',
                                ],
                            ],

                            'tool_choice' => [
                                'type' =>
                                    'image_generation',
                            ],

                            'timeout' =>
                                240,

                            'minimum_preflight_credits' =>
                                1,
                        ]
                    );


                $base64 = null;

                foreach (
                    (array) data_get(
                        $imageResult,
                        'raw.output',
                        []
                    )
                    as $output
                ) {
                    if (
                        !is_array($output)
                        || (
                            $output['type']
                            ?? null
                        ) !==
                        'image_generation_call'
                    ) {
                        continue;
                    }


                    $candidate =
                        trim(
                            (string) (
                                $output['result']
                                ?? ''
                            )
                        );


                    if ($candidate !== '') {
                        $base64 =
                            $candidate;

                        break;
                    }
                }


                if (!$base64) {
                    throw new \RuntimeException(
                        'Theme AI image generation returned no image.'
                    );
                }


                $dataUrl =
                    'data:image/jpeg;base64,'
                    . $base64;


                $result['changes'][] = [
                    'target' =>
                        $target,

                    'target_key' =>
                        $target,

                    'type' =>
                        'image',

                    'item_type' =>
                        'image',

                    /*
                     * proposed value doubles as preview data.
                     * Persistence uses asset_reference instead.
                     */
                    'value' =>
                        $dataUrl,

                    'asset_reference' => [
                        'kind' =>
                            'base64',

                        'mime_type' =>
                            'image/jpeg',

                        'extension' =>
                            'jpg',

                        'data' =>
                            $base64,

                        'data_url' =>
                            $dataUrl,

                        'source' =>
                            'esubiz-central-ai',

                        'target_key' =>
                            $target,
                    ],
                ];


                $existingTargets[] =
                    $target;
            }
        }


        return $result;
    }

}
