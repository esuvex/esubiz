<?php

namespace App\Http\Controllers;

use App\Models\Website;
use App\Services\SiteAi\SiteAiEngine;
use App\Services\SiteAi\SiteAiRegistry;
use App\Services\SiteAi\Proposals\SiteAiProposalFactory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use RuntimeException;
use Throwable;
use Illuminate\Support\Facades\DB;

/**
 * Generic tenant-facing AI endpoint.
 *
 * Theme Config, Page Builder, Ecommerce, Hotel, Blog,
 * Forms and future website features all call this endpoint.
 */
class TenantSiteAiController extends Controller
{
    public function generate(
        Request $request,
        string $subdomain,
        SiteAiEngine $engine
    ): JsonResponse {

        $website =
            Website::query()
                ->where(
                    'subdomain',
                    $subdomain
                )
                ->firstOrFail();


        $data =
            $request->validate([
                'capability' => [
                    'required',
                    'string',
                    'max:150',
                ],

                'action' => [
                    'required',
                    'string',
                    'max:100',
                ],

                'prompt' => [
                    'nullable',
                    'string',
                    'max:10000',
                ],

                'payload' => [
                    'nullable',
                    'array',
                ],

                'context' => [
                    'nullable',
                    'array',
                ],
            ]);


        
        /*
         * ESUBIZ_CENTRAL_CHAT_LIMITS_SERVER_V1
         *
         * Central Admin remains authoritative.
         * Client-side limits are only for immediate UX.
         */
        $centralChatLimits =
            DB::table(
                'central_ai_chat_settings'
            )
                ->orderBy('id')
                ->first();

        $maxMessageCharacters =
            max(
                100,
                (int) (
                    $centralChatLimits->max_message_characters
                    ?? 10000
                )
            );

        $maxPhotosPerMessage =
            max(
                0,
                (int) (
                    $centralChatLimits->max_photos_per_message
                    ?? 5
                )
            );

        $maxPhotoSizeMb =
            max(
                1,
                (int) (
                    $centralChatLimits->max_photo_size_mb
                    ?? 10
                )
            );

        if (
            mb_strlen(
                (string) ($data['prompt'] ?? '')
            )
            > $maxMessageCharacters
        ) {
            return response()->json(
                [
                    'message' =>
                        'Maximum message length is '
                        . $maxMessageCharacters
                        . ' characters.',
                ],
                422
            );
        }

        $incomingReferenceImages =
            (array) data_get(
                $data,
                'payload.reference_images',
                []
            );

        if (
            count($incomingReferenceImages)
            > $maxPhotosPerMessage
        ) {
            return response()->json(
                [
                    'message' =>
                        'A maximum of '
                        . $maxPhotosPerMessage
                        . ' photos may be attached to one message.',
                ],
                422
            );
        }

        foreach (
            $incomingReferenceImages
            as $incomingReferenceImage
        ) {
            $dataUrl =
                (string) data_get(
                    $incomingReferenceImage,
                    'data_url',
                    ''
                );

            if (
                $dataUrl === ''
                || !str_contains($dataUrl, ',')
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
                (int) floor(
                    strlen($encodedImage)
                    * 3
                    / 4
                )
                - $padding;

            if (
                $imageBytes
                > ($maxPhotoSizeMb * 1024 * 1024)
            ) {
                return response()->json(
                    [
                        'message' =>
                            'Each photo must be '
                            . $maxPhotoSizeMb
                            . ' MB or smaller.',
                    ],
                    422
                );
            }
        }

$payload =
            (array) (
                $data[
                    'payload'
                ] ?? []
            );


        /*
         * Prompt is normalized into payload so all capabilities
         * receive the same canonical request structure.
         */
        if (
            array_key_exists(
                'prompt',
                $data
            )
        ) {
            $payload[
                'prompt'
            ] =
                $data[
                    'prompt'
                ];
        }


        /*
         * SITE_AI_REFERENCE_IMAGE_CONTEXT_BRIDGE
         *
         * Keep reference images in the canonical payload, while also
         * exposing them through context for the Central vision bridge.
         *
         * This does not affect normal text-only requests.
         */
        $siteAiContext =
            (array) (
                $data[
                    'context'
                ] ?? []
            );

        /*
         * ESUBIZ_PROPOSAL_MODE_CONTEXT_FIX_V1
         *
         * proposal_mode is part of the request contract but the
         * central Site AI provider reads it from Site AI context.
         *
         * Normalize it here so proposal generation always receives
         * the strict structured {"changes":[...]} instruction.
         */
        $siteAiContext[
            'proposal_mode'
        ] =
            filter_var(
                $data[
                    'proposal_mode'
                ]
                ?? $siteAiContext[
                    'proposal_mode'
                ]
                ?? false,
                FILTER_VALIDATE_BOOLEAN
            );

        /*
         * ESUBIZ_ACTIVE_THEME_AI_CONTEXT_V1
         *
         * The tenant's active theme is authoritative.
         *
         * Each theme declares its own Site AI features in
         * its ai-manifest.php file. Central Esubiz does not
         * assume which sections/features a theme contains.
         */
        $activeThemeKey =
            null;

        $themeAiManifest =
            [];

        $tenantDatabaseService =
            app(
                \App\Services\Website\WebsiteTenantDatabaseService::class
            );

        $tenantDatabaseService
            ->connect(
                $website
            );

        try {
            $activeThemeKey =
                trim(
                    (string) (
                        $tenantDatabaseService
                            ->connection()
                            ->table(
                                'site_settings'
                            )
                            ->where(
                                'key',
                                'theme.active'
                            )
                            ->value(
                                'value'
                            )
                        ?? ''
                    )
                );
        } finally {
            $tenantDatabaseService
                ->disconnect();
        }

        if ($activeThemeKey !== '') {
            $themeAiManifest =
                app(
                    \App\Services\SiteAi\Support\ThemeAiManifest::class
                )->load(
                    $activeThemeKey
                );
        }

        $siteAiContext[
            'active_theme'
        ] =
            $activeThemeKey !== ''
                ? $activeThemeKey
                : null;

        $siteAiContext[
            'theme_ai_manifest'
        ] =
            $themeAiManifest;


        /*
         * ========================================================
         * ESUBIZ_DYNAMIC_AI_FUNCTION_SCOPE_V1
         * ========================================================
         *
         * Location establishes scope.
         *
         * Themes declare their own functions/features through
         * ai-manifest.php. Central Esubiz does not hardcode Hero,
         * About, Features or any future theme feature.
         *
         * If the caller selects one or more theme functions,
         * only targets belonging to those declared functions are
         * exposed to AI generation and proposal validation.
         */

        $selectedFunctions =
            array_values(
                array_unique(
                    array_filter(
                        array_map(
                            static fn ($value) =>
                                trim(
                                    (string) $value
                                ),

                            (array) (
                                $payload[
                                    'selected_functions'
                                ]
                                ?? []
                            )
                        ),

                        static fn ($value) =>
                            $value !== ''
                    )
                )
            );


        $scopedThemeAiManifest =
            $themeAiManifest;


        if (
            $data['capability']
                === 'theme.homepage'
            && !empty(
                $selectedFunctions
            )
        ) {
            $declaredFunctions =
                (array) (
                    $themeAiManifest[
                        'functions'
                    ]
                    ?? []
                );

            $scopedFunctions =
                [];


            foreach (
                $selectedFunctions
                as $selectedFunction
            ) {
                if (
                    !array_key_exists(
                        $selectedFunction,
                        $declaredFunctions
                    )
                ) {
                    throw new RuntimeException(
                        'The selected AI theme function "'
                        . $selectedFunction
                        . '" is not declared by the active theme.'
                    );
                }

                $scopedFunctions[
                    $selectedFunction
                ] =
                    $declaredFunctions[
                        $selectedFunction
                    ];
            }


            $scopedThemeAiManifest[
                'functions'
            ] =
                $scopedFunctions;


            /*
             * Replace the context manifest too, so provider
             * instructions and downstream proposal creation see
             * the same authoritative scope.
             */
            $siteAiContext[
                'theme_ai_manifest'
            ] =
                $scopedThemeAiManifest;


            $siteAiContext[
                'selected_functions'
            ] =
                $selectedFunctions;
        }


        /*
         * Resolve the exact capability manifest BEFORE generation.
         *
         * This is also the authoritative manifest snapshot later
         * passed into the global proposal factory.
         */
        $siteAiRegistry =
            app(
                SiteAiRegistry::class
            );

        $siteAiCapability =
            $siteAiRegistry->get(
                $data[
                    'capability'
                ]
            );


        $manifestContext =
            $siteAiContext;

        if (
            $data['capability']
                === 'theme.homepage'
        ) {
            $manifestContext[
                'theme_ai_manifest'
            ] =
                $scopedThemeAiManifest;
        }


        $manifest =
            $siteAiCapability->manifest(
                $manifestContext
            );


        /*
         * Pass the already-resolved authoritative manifest into
         * the generation context. The engine may independently
         * resolve its capability as usual; this value ensures
         * proposal creation and provider context share the same
         * scope snapshot.
         */
        $siteAiContext[
            'capability_manifest'
        ] =
            $manifest;


        /*
         * ========================================================
         * ESUBIZ_PROPOSAL_GENERATION_CONTEXT_V1
         * ========================================================
         *
         * Proposal mode must be known BEFORE generation so the
         * provider can request structured, previewable changes.
         *
         * Ordinary Site AI conversation remains false/default.
         */
        $proposalMode =
            filter_var(
                $request->input(
                    'proposal_mode',
                    false
                ),
                FILTER_VALIDATE_BOOLEAN
            );

        $siteAiContext[
            'proposal_mode'
        ] =
            $proposalMode;


        $referenceImages =
            (array) (
                $payload[
                    'reference_images'
                ] ?? []
            );

        if (!empty($referenceImages)) {
            $siteAiContext[
                'reference_images'
            ] =
                array_slice(
                    $referenceImages,
                    0,
                    5
                );
        }


        try {

            /*
             * ESUBIZ_AI_IDENTITY_CONTEXT_V1
             *
             * Resolve the exact same official persona used
             * by the Esubiz Site AI interface.
             */
            $siteAiPersona =
                app(
                    \App\Services\SiteAi\Support\SiteAiPersona::class
                )->current();


            $result =
                $engine->generate(
                    $website,
                    $data[
                        'capability'
                    ],
                    $data[
                        'action'
                    ],
                    $payload,
                    array_merge(
                        $siteAiContext,
                        [
                            /*
                             * ESUBIZ_AI_IDENTITY_CONTEXT_V1
                             *
                             * This identity is authoritative.
                             * The model/provider name must never
                             * become the public assistant identity.
                             */

                            'platform_name' =>
                                'ESUBIZ',

                            'parent_company_name' =>
                                'Esuvex Limited',

                            'assistant_name' =>
                                trim(
                                    (string) (
                                        $siteAiPersona['name']
                                        ?? 'Esubiz AI'
                                    )
                                ),

                            'assistant_avatar_url' =>
                                $siteAiPersona[
                                    'avatar_url'
                                ]
                                ?? null,

                            'assistant_persona_id' =>
                                $siteAiPersona[
                                    'id'
                                ]
                                ?? null,

                            'website_name' =>
                                trim(
                                    (string) (
                                        $website->name
                                        ?: 'this website'
                                    )
                                ),

                            /*
                             * Current job context.
                             *
                             * Site AI capabilities define what the
                             * assistant is working on in this request.
                             */
                            'current_capability' =>
                                $data[
                                    'capability'
                                ],

                            'current_action' =>
                                $data[
                                    'action'
                                ],

                            'assistant_identity_instruction' =>
                                'You are "'
                                . trim(
                                    (string) (
                                        $siteAiPersona['name']
                                        ?? 'Esubiz AI'
                                    )
                                )
                                . '", an official ESUBIZ AI assistant. '
                                . 'ESUBIZ is a product of Esuvex Limited. '
                                . 'You work for ESUBIZ and assist users inside the ESUBIZ platform and ESUBIZ-powered websites. '
                                . 'Your public identity is always the configured Esubiz persona name "'
                                . trim(
                                    (string) (
                                        $siteAiPersona['name']
                                        ?? 'Esubiz AI'
                                    )
                                )
                                . '". Never identify yourself as ChatGPT, GPT, OpenAI, or by the underlying model or provider name. '
                                . 'If asked who you work for, say you work for ESUBIZ. '
                                . 'If relevant, explain that ESUBIZ is a product of Esuvex Limited. '
                                . 'You are currently assisting the website "'
                                . trim(
                                    (string) (
                                        $website->name
                                        ?: 'this website'
                                    )
                                )
                                . '". Keep this identity consistent throughout the conversation.',

                            'assistant_job_instruction' =>
                                'Your current ESUBIZ job is defined by capability "'
                                . (string) $data['capability']
                                . '" and action "'
                                . (string) $data['action']
                                . '". Understand the current page or feature from the capability manifest and supplied context. '
                                . 'Focus your response on that specific job. '
                                . 'Do not invent website fields, theme settings, widgets, sections, actions, or capabilities that are not supplied by ESUBIZ. '
                                . 'When working with a theme, page, section, widget, form, product, or other website feature, respect its supplied configuration and available fields. '
                                . 'Generated content must be suitable for the exact website, page, section, or feature represented by the current capability and context.',
                        ]
                    )
                );

            /*
             * ========================================================
             * ESUBIZ_GLOBAL_AI_PROPOSAL_BRIDGE_V1
             * ========================================================
             *
             * Ordinary AI conversation stays unchanged.
             *
             * Only explicitly requested proposal-mode operations
             * enter the universal Preview / Approval workflow.
             *
             * Nothing is written to the tenant here.
             */
            if ($proposalMode) {
                if (!is_array($result)) {
                    throw new RuntimeException(
                        'AI proposal generation must return structured output.'
                    );
                }

                /*
                 * Use the SAME capability manifest and SAME context
                 * that authorized this generation.
                 *
                 * This preserves location scope:
                 *
                 * About AI -> About targets only.
                 * Hero AI -> Hero targets only.
                 * Theme generation -> Theme generation only.
                 *
                 * A proposal cannot widen itself into another
                 * Esubiz capability after generation.
                 */
                $proposal =
                    app(
                        SiteAiProposalFactory::class
                    )->createFromResult(
                        website:
                            $website,

                        capability:
                            $data[
                                'capability'
                            ],

                        action:
                            $data[
                                'action'
                            ],

                        manifest:
                            $manifest,

                        context:
                            $siteAiContext,

                        result:
                            $result,

                        options: [
                            'user_id' =>
                                auth()->id(),

                            'proposal_type' =>
                                'change',
                        ]
                    );

                /*
                 * The frontend will use this payload for the
                 * universal large Preview / Approval interface.
                 */
                return response()->json([
                    'ok' =>
                        true,

                    /*
                     * ESUBIZ_PROPOSAL_RESPONSE_COMPAT_V1
                     *
                     * Existing Site AI frontend uses success=true
                     * as its standard successful-response contract.
                     */
                    'success' =>
                        true,


                    'mode' =>
                        'proposal',

                    'proposal' => [
                        'uuid' =>
                            $proposal->uuid,

                        'status' =>
                            $proposal->status,

                        'capability' =>
                            $proposal->capability,

                        'action' =>
                            $proposal->action,

                        'title' =>
                            $proposal->title,

                        'summary' =>
                            $proposal->summary,

                        'destination' =>
                            $proposal->destination,

                        'items' =>
                            $proposal->items
                                ->map(
                                    fn ($item) => [
                                        'uuid' =>
                                            $item->uuid,

                                        'item_type' =>
                                            $item->item_type,

                                        'target_key' =>
                                            $item->target_key,

                                        'proposed_value' =>
                                            $item->proposed_value,

                                        'preview_payload' =>
                                            $item->preview_payload,
                                    ]
                                )
                                ->values()
                                ->all(),
                    ],

                    'result' =>
                        $result,
                ]);
            }



            return response()->json([
                'success' =>
                    true,

                'capability' =>
                    $data[
                        'capability'
                    ],

                'action' =>
                    $data[
                        'action'
                    ],

                'result' =>
                    $result,
            ]);


        } catch (
            RuntimeException $exception
        ) {

            return response()->json(
                [
                    'success' =>
                        false,

                    'message' =>
                        $exception
                            ->getMessage(),
                ],
                422
            );


        } catch (
            Throwable $exception
        ) {

            report(
                $exception
            );


            return response()->json(
                [
                    'success' =>
                        false,

                    'message' =>
                        'Site AI could not complete this request.',
                ],
                500
            );
        }
    }


    /**
     * Tenant Esubiz AI settings.
     */
    public function settings(
        string $subdomain
    ) {
        $website =
            Website::query()
                ->where(
                    'subdomain',
                    $subdomain
                )
                ->firstOrFail();


        $personas =
            \App\Models\Ai\AiPersona::query()
                ->where(
                    'is_active',
                    true
                )
                ->orderByDesc(
                    'is_default'
                )
                ->orderBy(
                    'sort_order'
                )
                ->orderBy(
                    'id'
                )
                ->get();


        $selectedPersonaId =
            null;


        $tenantDatabaseService =
            app(
                \App\Services\Website\WebsiteTenantDatabaseService::class
            );


        $tenantDatabaseService
            ->connect(
                $website
            );


        try {

            $value =
                $tenantDatabaseService
                    ->connection()
                    ->table(
                        'site_settings'
                    )
                    ->where(
                        'key',
                        'ai.persona_id'
                    )
                    ->value(
                        'value'
                    );


            $selectedPersonaId =
                is_numeric(
                    $value
                )
                    ? (int) $value
                    : null;


            $siteAiEnabled =
                (
                    $tenantDatabaseService
                        ->connection()
                        ->table('site_settings')
                        ->where(
                            'key',
                            'ai.site_enabled'
                        )
                        ->value('value')
                    ?? '1'
                ) === '1';


            $liveChatAiEnabled =
                (
                    $tenantDatabaseService
                        ->connection()
                        ->table('site_settings')
                        ->where(
                            'key',
                            'ai.live_chat_enabled'
                        )
                        ->value('value')
                    ?? '1'
                ) === '1';


            $whatsappAiEnabled =
                (
                    $tenantDatabaseService
                        ->connection()
                        ->table('site_settings')
                        ->where(
                            'key',
                            'ai.whatsapp_enabled'
                        )
                        ->value('value')
                    ?? '1'
                ) === '1';


            $emailAiEnabled =
                (
                    $tenantDatabaseService
                        ->connection()
                        ->table('site_settings')
                        ->where(
                            'key',
                            'ai.email_enabled'
                        )
                        ->value('value')
                    ?? '1'
                ) === '1';


            $smsAiEnabled =
                (
                    $tenantDatabaseService
                        ->connection()
                        ->table('site_settings')
                        ->where(
                            'key',
                            'ai.sms_enabled'
                        )
                        ->value('value')
                    ?? '1'
                ) === '1';


            $socialMediaAiEnabled =
                (
                    $tenantDatabaseService
                        ->connection()
                        ->table('site_settings')
                        ->where(
                            'key',
                            'ai.social_media_enabled'
                        )
                        ->value('value')
                    ?? '1'
                ) === '1';


            $adsAiEnabled =
                (
                    $tenantDatabaseService
                        ->connection()
                        ->table('site_settings')
                        ->where(
                            'key',
                            'ai.ads_enabled'
                        )
                        ->value('value')
                    ?? '1'
                ) === '1';


        } finally {

            $tenantDatabaseService
                ->disconnect();
        }


        return view(
            'tenant.admin.ai.settings',
            compact(
                'website',
                'personas',
                'selectedPersonaId',
                'siteAiEnabled',
                'liveChatAiEnabled',
                'whatsappAiEnabled',
                'emailAiEnabled',
                'smsAiEnabled',
                'socialMediaAiEnabled',
                'adsAiEnabled'
            )
        );
    }


    /**
     * Save workspace choice of official Esubiz AI persona.
     */
    public function updateSettings(
        Request $request,
        string $subdomain
    ) {

        $website =
            Website::query()
                ->where(
                    'subdomain',
                    $subdomain
                )
                ->firstOrFail();


        $data =
            $request->validate([
                'persona_id' => [
                    'nullable',
                    'integer',
                    'exists:ai_personas,id',
                ],

                'site_ai_enabled' => [
                    'nullable',
                    'boolean',
                ],

                'live_chat_ai_enabled' => [
                    'nullable',
                    'boolean',
                ],

                'whatsapp_ai_enabled' => [
                    'nullable',
                    'boolean',
                ],

                'email_ai_enabled' => [
                    'nullable',
                    'boolean',
                ],

                'sms_ai_enabled' => [
                    'nullable',
                    'boolean',
                ],

                'social_media_ai_enabled' => [
                    'nullable',
                    'boolean',
                ],

                'ads_ai_enabled' => [
                    'nullable',
                    'boolean',
                ],

                'site_ai_color' => [
                    'nullable',
                    'regex:/^#[0-9A-Fa-f]{6}$/',
                ],

                'site_ai_text_color' => [
                    'nullable',
                    'regex:/^#[0-9A-Fa-f]{6}$/',
                ],

                'site_ai_user_color' => [
                    'nullable',
                    'regex:/^#[0-9A-Fa-f]{6}$/',
                ],

                'site_ai_user_text_color' => [
                    'nullable',
                    'regex:/^#[0-9A-Fa-f]{6}$/',
                ],
            ]);


        /*
         * Central Admin controls whether tenant/site admins
         * may override global AI chat colors.
         */
        $allowSiteAiChatColorCustomization =
            (bool) (
                DB::table(
                    'central_ai_chat_settings'
                )
                    ->orderBy('id')
                    ->value(
                        'allow_user_chat_color_customization'
                    )
                ?? true
            );


        if (!$allowSiteAiChatColorCustomization) {

            unset(
                $data['site_ai_color'],
                $data['site_ai_text_color'],
                $data['site_ai_user_color'],
                $data['site_ai_user_text_color']
            );
        }


        $website->forceFill([
            'site_ai_color' =>
                $data['site_ai_color']
                ?? $website->site_ai_color
                ?? '#0b1f3a',

            'site_ai_text_color' =>
                $data['site_ai_text_color']
                ?? $website->site_ai_text_color
                ?? '#ffffff',

            'site_ai_user_color' =>
                $data['site_ai_user_color']
                ?? $website->site_ai_user_color
                ?? '#f1f5f9',

            'site_ai_user_text_color' =>
                $data['site_ai_user_text_color']
                ?? $website->site_ai_user_text_color
                ?? '#0f172a',
        ])->save();


        $persona =
            !empty(
                $data[
                    'persona_id'
                ]
            )
                ? \App\Models\Ai\AiPersona::query()
                    ->where(
                        'is_active',
                        true
                    )
                    ->findOrFail(
                        $data[
                            'persona_id'
                        ]
                    )
                : null;


        $tenantDatabaseService =
            app(
                \App\Services\Website\WebsiteTenantDatabaseService::class
            );


        $tenantDatabaseService
            ->connect(
                $website
            );


        try {

            if ($persona) {

                $tenantDatabaseService
                    ->connection()
                    ->table(
                        'site_settings'
                    )
                    ->updateOrInsert(
                        [
                            'key' =>
                                'ai.persona_id',
                        ],
                        [
                            'value' =>
                                (string) $persona->id,
                        ]
                    );
            }


            $aiSettings = [
                'ai.assistant_name' =>
                    $website->site_ai_name
                    ?: 'Esubiz AI',

                'ai.assistant_avatar' =>
                    $website->site_ai_avatar
                    ?: '',

                'ai.chat.ai_color' =>
                    $website->site_ai_color
                    ?: '#0b1f3a',

                'ai.chat.ai_text_color' =>
                    $website->site_ai_text_color
                    ?: '#ffffff',

                'ai.chat.user_color' =>
                    $website->site_ai_user_color
                    ?: '#f1f5f9',

                'ai.chat.user_text_color' =>
                    $website->site_ai_user_text_color
                    ?: '#0f172a',

                'ai.site_enabled' =>
                    $request->boolean(
                        'site_ai_enabled'
                    )
                        ? '1'
                        : '0',

                'ai.live_chat_enabled' =>
                    $request->boolean(
                        'live_chat_ai_enabled'
                    )
                        ? '1'
                        : '0',

                'ai.whatsapp_enabled' =>
                    $request->boolean(
                        'whatsapp_ai_enabled'
                    )
                        ? '1'
                        : '0',

                'ai.email_enabled' =>
                    $request->boolean(
                        'email_ai_enabled'
                    )
                        ? '1'
                        : '0',

                'ai.sms_enabled' =>
                    $request->boolean(
                        'sms_ai_enabled'
                    )
                        ? '1'
                        : '0',

                'ai.social_media_enabled' =>
                    $request->boolean(
                        'social_media_ai_enabled'
                    )
                        ? '1'
                        : '0',

                'ai.ads_enabled' =>
                    $request->boolean(
                        'ads_ai_enabled'
                    )
                        ? '1'
                        : '0',
            ];


            foreach (
                $aiSettings
                as $settingKey => $settingValue
            ) {

                $tenantDatabaseService
                    ->connection()
                    ->table(
                        'site_settings'
                    )
                    ->updateOrInsert(
                        [
                            'key' =>
                                $settingKey,
                        ],
                        [
                            'value' =>
                                $settingValue,
                        ]
                    );
            }


        } finally {

            $tenantDatabaseService
                ->disconnect();
        }


        return redirect()
            ->back()
            ->with(
                'success',
                'Esubiz AI persona updated.'
            );
    }



    /**
     * Autosave one website AI service.
     *
     * One reusable endpoint serves every AI service.
     */
    public function updateServiceSetting(
        Request $request,
        string $subdomain
    ): JsonResponse {

        $website =
            Website::query()
                ->where(
                    'subdomain',
                    $subdomain
                )
                ->firstOrFail();


        $allowedServices = [
            'site',
            'live_chat',
            'whatsapp',
            'email',
            'sms',
            'social_media',
            'ads',
        ];


        $data =
            $request->validate([
                'service' => [
                    'required',
                    'string',
                    Rule::in(
                        $allowedServices
                    ),
                ],

                'enabled' => [
                    'required',
                    'boolean',
                ],
            ]);


        $tenantDatabaseService =
            app(
                \App\Services\Website\WebsiteTenantDatabaseService::class
            );


        $tenantDatabaseService
            ->connect(
                $website
            );


        try {

            $tenantDatabaseService
                ->connection()
                ->table(
                    'site_settings'
                )
                ->updateOrInsert(
                    [
                        'key' =>
                            'ai.'
                            . $data[
                                'service'
                            ]
                            . '_enabled',
                    ],
                    [
                        'value' =>
                            $data[
                                'enabled'
                            ]
                                ? '1'
                                : '0',
                    ]
                );


        } finally {

            $tenantDatabaseService
                ->disconnect();
        }


        return response()->json([
            'success' =>
                true,

            'service' =>
                $data[
                    'service'
                ],

            'enabled' =>
                (bool) $data[
                    'enabled'
                ],

            'message' =>
                $data[
                    'enabled'
                ]
                    ? 'AI service enabled.'
                    : 'AI service disabled.',
        ]);
    }



    /**
     * Autosave the official Esubiz AI avatar selected
     * by this tenant website.
     */
    public function updateAvatarSetting(
        Request $request,
        string $subdomain
    ): \Illuminate\Http\JsonResponse {

        $website =
            Website::query()
                ->where(
                    'subdomain',
                    $subdomain
                )
                ->firstOrFail();


        $data =
            $request->validate([
                'persona_id' => [
                    'required',
                    'integer',
                    'min:1',
                ],
            ]);


        /*
         * Official avatars always come from the Central DB.
         */
        $persona =
            \App\Models\Ai\AiPersona::query()
                ->where(
                    'is_active',
                    true
                )
                ->find(
                    (int) $data['persona_id']
                );


        if (!$persona) {

            return response()->json([
                'success' => false,
                'message' =>
                    'The selected AI avatar is unavailable.',
            ], 422);
        }


        /*
         * Use the exact tenant DB service already proven by
         * updateServiceSetting().
         */
        $tenantDatabaseService =
            app(
                \App\Services\Website\WebsiteTenantDatabaseService::class
            );


        $tenantDatabaseService
            ->connect(
                $website
            );


        try {

            $db =
                $tenantDatabaseService
                    ->connection();


            $db->table(
                'site_settings'
            )
            ->updateOrInsert(
                [
                    'key' =>
                        'ai.persona_id',
                ],
                [
                    'value' =>
                        (string) $persona->id,
                ]
            );


            /*
             * Read it back before declaring the AJAX save successful.
             */
            $saved =
                (string) (
                    $db->table(
                        'site_settings'
                    )
                    ->where(
                        'key',
                        'ai.persona_id'
                    )
                    ->value(
                        'value'
                    )
                    ?? ''
                );


            if (
                $saved
                !== (string) $persona->id
            ) {

                return response()->json([
                    'success' => false,
                    'message' =>
                        'The AI avatar setting could not be verified.',
                ], 500);
            }


        } finally {

            $tenantDatabaseService
                ->disconnect();
        }


        return response()->json([
            'success' => true,

            'message' =>
                'Avatar saved',

            'persona_id' =>
                $persona->id,

            'name' =>
                $persona->name,

            'avatar_url' =>
                $persona->avatarUrl(),
        ]);
    }



    /**
     * Generic tenant Website AI settings autosave.
     */
    public function autosaveSetting(
        Request $request,
        string $subdomain
    ): \Illuminate\Http\JsonResponse {

        $data =
            $request->validate([
                'field' => [
                    'required',
                    'string',
                    \Illuminate\Validation\Rule::in([
                        'site_ai_enabled',
                        'live_chat_ai_enabled',
                        'whatsapp_ai_enabled',
                        'email_ai_enabled',
                        'sms_ai_enabled',
                        'social_media_ai_enabled',
                        'ads_ai_enabled',
                        'persona_id',
                    ]),
                ],

                'value' => [
                    'nullable',
                ],
            ]);


        /*
        |--------------------------------------------------------------------------
        | AI AVATAR
        |--------------------------------------------------------------------------
        |
        | Do not duplicate tenant DB logic here.
        |
        | Delegate directly to the already-authoritative
        | updateAvatarSetting() method.
        |
        */

        if (
            $data['field']
            === 'persona_id'
        ) {

            $request->merge([
                'persona_id' =>
                    (int) $data['value'],
            ]);


            return $this
                ->updateAvatarSetting(
                    $request,
                    $subdomain
                );
        }


        /*
        |--------------------------------------------------------------------------
        | AI SERVICE SWITCHES
        |--------------------------------------------------------------------------
        |
        | Convert the UI field into the generic service key used by
        | updateServiceSetting(), then let that method perform the
        | tenant DB persistence.
        |
        */

        $serviceMap = [
            'site_ai_enabled' =>
                'site',

            'live_chat_ai_enabled' =>
                'live_chat',

            'whatsapp_ai_enabled' =>
                'whatsapp',

            'email_ai_enabled' =>
                'email',

            'sms_ai_enabled' =>
                'sms',

            'social_media_ai_enabled' =>
                'social_media',

            'ads_ai_enabled' =>
                'ads',
        ];


        $enabled =
            filter_var(
                $data['value'],
                FILTER_VALIDATE_BOOLEAN
            );


        $request->merge([
            'service' =>
                $serviceMap[
                    $data['field']
                ],

            'enabled' =>
                $enabled,
        ]);


        return $this
            ->updateServiceSetting(
                $request,
                $subdomain
            );
    }


    /*
    |--------------------------------------------------------------------------
    | ESUBIZ_TENANT_AI_USAGE_PRICING_V1
    |--------------------------------------------------------------------------
    |
    | Customer-facing AI pricing guide and consumption history.
    |
    | Provider/model/token/commercial internals deliberately never leave
    | this controller. Customer prices are expressed only in AI Credits.
    |
    */
    public function usagePricing(
        ?string $subdomain = null
    ): \Illuminate\Http\JsonResponse {

        if ($subdomain === null) {
            try {
                $token = request()->bearerToken();
                abort_unless($token, 401);

                $resolved = app(
                    \App\Services\Licensing\OffServerApiCredentialService::class
                )->resolveBearerToken($token);

                $website = $resolved['website'];
            } catch (\Symfony\Component\HttpKernel\Exception\HttpExceptionInterface $exception) {
                throw $exception;
            } catch (\Throwable $exception) {
                return response()->json([
                    'message' => 'This Core installation is not authorized.',
                ], 401);
            }
        } else {
            $website = \App\Models\Website::query()
                ->where('subdomain', $subdomain)
                ->firstOrFail();

            $websiteId = (int) $website->id;
            $siteSession = "tenant_cms_sites.{$websiteId}";
            $authorized =
                (
                    (bool) session('tenant_cms_authenticated')
                    && (int) session('tenant_cms_website_id') === $websiteId
                    && (int) session('tenant_cms_user_id') > 0
                )
                || (
                    (bool) session($siteSession . '.authenticated')
                    && (int) session($siteSession . '.user_id') > 0
                );

            abort_unless($authorized, 403);

            if ($website->deployment_type === 'off_server') {
                try {
                    $central = app(
                        \App\Services\Core\CoreCentralConnectionService::class
                    );

                    $response = $central->request(15)->get(
                        $central->centralUrl() . '/api/v1/core/ai/usage-data',
                        request()->only('table', 'pricing_page', 'history_page')
                    );

                    return response()->json(
                        $response->json() ?: [
                            'message' => 'AI records are temporarily unavailable.',
                        ],
                        $response->successful() ? 200 : 422
                    );
                } catch (\Throwable $exception) {
                    report($exception);
                    return response()->json([
                        'message' => 'AI records are temporarily unavailable.',
                    ], 422);
                }
            }

            abort_unless($website->deployment_type === 'saas', 422);
        }

        $pricing =
            app(
                \App\Services\Ai\AiPricingService::class
            );


        /*
         * Internal provider-cost envelopes.
         *
         * These are NOT customer prices.
         * They represent approximate underlying AI work envelopes and
         * are converted to credits by the same central pricing authority
         * used by Esubiz billing.
         *
         * Final credit ranges therefore respond automatically to:
         * - Esubiz markup
         * - USD/NGN billing rate
         * - Naira per AI Credit
         */
        $taskDefinitions = collect([
            [
                'task' => 'Short Text & Titles',
                'description' =>
                    'Titles, subtitles, labels and short website copy.',
                'min_usd' => 0.001,
                'max_usd' => 0.004,
            ],
            [
                'task' => 'Paragraph & Website Copy',
                'description' =>
                    'Paragraphs, descriptions and general website content.',
                'min_usd' => 0.003,
                'max_usd' => 0.012,
            ],
            [
                'task' => 'Call to Action',
                'description' =>
                    'CTA headings, supporting text and button copy.',
                'min_usd' => 0.003,
                'max_usd' => 0.012,
            ],
            [
                'task' => 'Hero Content',
                'description' =>
                    'Hero headline, supporting copy and call to action.',
                'min_usd' => 0.005,
                'max_usd' => 0.020,
            ],
            [
                'task' => 'Product or Service Description',
                'description' =>
                    'AI-written product and service descriptions.',
                'min_usd' => 0.003,
                'max_usd' => 0.015,
            ],
            [
                'task' => 'Features / Cards — Text',
                'description' =>
                    'Structured feature or service cards without generated photos.',
                'min_usd' => 0.008,
                'max_usd' => 0.030,
            ],
            [
                'task' => 'Testimonials — Text',
                'description' =>
                    'Structured testimonial content without generated photos.',
                'min_usd' => 0.008,
                'max_usd' => 0.030,
            ],
            [
                'task' => 'Full Website Section',
                'description' =>
                    'A complete website section with structured content.',
                'min_usd' => 0.010,
                'max_usd' => 0.040,
            ],
            [
                'task' => 'Multi-section Website Content',
                'description' =>
                    'Larger requests covering several website sections.',
                'min_usd' => 0.025,
                'max_usd' => 0.100,
            ],
            [
                'task' => 'Single AI Image',
                'description' =>
                    'One generated website image.',
                'min_usd' => 0.035,
                'max_usd' => 0.060,
            ],
            [
                'task' => 'Hero + AI Image',
                'description' =>
                    'Hero content with one generated website image.',
                'min_usd' => 0.040,
                'max_usd' => 0.080,
            ],
            [
                'task' => 'Features / Cards + Images',
                'description' =>
                    'Structured feature cards with generated supporting images.',
                'min_usd' => 0.080,
                'max_usd' => 0.250,
            ],
            [
                'task' => 'Testimonials + Headshots',
                'description' =>
                    'Structured testimonials with generated profile photos.',
                'min_usd' => 0.080,
                'max_usd' => 0.250,
            ],
            [
                'task' => 'Branding',
                'description' =>
                    'Website logo, footer logo and website icon generation.',
                'min_usd' => 0.120,
                'max_usd' => 0.220,
            ],
            [
                'task' => 'Theme Generation',
                'description' =>
                    'Complete AI-assisted website theme generation. Usage varies with theme complexity, sections, content and generated images.',
                'min_usd' => 0.180,
                'max_usd' => 0.890,
            ],
            [
                'task' => 'Advanced Website Generation',
                'description' =>
                    'Complex website generation requiring multiple AI operations.',
                'min_usd' => 0.080,
                'max_usd' => 0.300,
            ],
        ])->map(
            function (array $task) use ($pricing) {

                $minimum =
                    $pricing->quote(
                        (float) $task['min_usd']
                    );

                $maximum =
                    $pricing->quote(
                        (float) $task['max_usd']
                    );

                return [
                    'task' =>
                        $task['task'],

                    'description' =>
                        $task['description'],

                    'minimum_credits' =>
                        (int) (
                            $minimum['credits']
                            ?? 0
                        ),

                    'maximum_credits' =>
                        max(
                            (int) (
                                $minimum['credits']
                                ?? 0
                            ),
                            (int) (
                                $maximum['credits']
                                ?? 0
                            )
                        ),
                ];
            }
        );


        /*
         * Separate paginator name prevents pricing navigation from
         * interfering with usage-history navigation.
         */
        $pricingPage =
            max(
                1,
                (int) request(
                    'pricing_page',
                    1
                )
            );

        $pricingPerPage = 10;

        $pricingGuide =
            new \Illuminate\Pagination\LengthAwarePaginator(
                $taskDefinitions
                    ->forPage(
                        $pricingPage,
                        $pricingPerPage
                    )
                    ->values(),
                $taskDefinitions->count(),
                $pricingPerPage,
                $pricingPage,
                [
                    'path' =>
                        request()->url(),

                    'pageName' =>
                        'pricing_page',

                    'query' =>
                        request()->except(
                            'pricing_page'
                        ),
                ]
            );


        /*
         * Customer history comes from the authoritative central
         * AI-credit ledger, not from provider-facing usage details.
         */
        $usageHistory =
            \App\Models\Ai\AiCreditTransaction::query()
                ->where(
                    'website_id',
                    $website->id
                )
                ->where(
                    'direction',
                    'debit'
                )
                ->where(
                    'type',
                    'ai_usage'
                )
                ->latest('id')
                ->paginate(
                    10,
                    [
                        'id',
                        'route_key',
                        'credits',
                        'balance_after',
                        'status',
                        'description',
                        'metadata',
                        'created_at',
                    ],
                    'history_page'
                )
                ->withQueryString();


        $table = (string) request()->query('table', 'usage');

        if ($table === 'pricing') {
            return response()->json([
                'page' => $pricingGuide->currentPage(),
                'has_previous' => $pricingGuide->currentPage() > 1,
                'has_next' => $pricingGuide->hasMorePages(),
                'rows' => $pricingGuide->items(),
            ]);
        }

        if ($table !== 'usage') {
            return response()->json([
                'message' => 'Unknown AI table.',
            ], 422);
        }

        $labels = [
            'site' => 'Website AI',
            'live_chat' => 'Live Chat AI',
            'whatsapp' => 'WhatsApp AI',
            'email' => 'Email AI',
            'sms' => 'SMS AI',
            'social_media' => 'Social Media AI',
            'ads' => 'Ads AI',
            'theme.homepage' => 'Website Generation',
            'app_builder' => 'App Builder AI',
            'module_builder' => 'Module Builder AI',
            'addon_builder' => 'Addon Builder AI',
        ];

        return response()->json([
            'page' => $usageHistory->currentPage(),
            'has_previous' => $usageHistory->currentPage() > 1,
            'has_next' => $usageHistory->hasMorePages(),
            'rows' => collect($usageHistory->items())->map(
                fn ($usage) => [
                    'task' => $labels[$usage->route_key] ?? 'Esubiz AI',
                    'credits' => (float) $usage->credits,
                    'balance_after' => $usage->balance_after !== null
                        ? (float) $usage->balance_after
                        : null,
                    'status' => (string) ($usage->status ?: 'completed'),
                    'date' => optional($usage->created_at)->format('d M Y, g:i A'),
                ]
            )->values()->all(),
        ]);
    }


}
