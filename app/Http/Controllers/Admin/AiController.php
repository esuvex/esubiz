<?php

namespace App\Http\Controllers\Admin;

use App\Services\Media\CentralMediaService;
use App\Http\Controllers\Controller;
use App\Models\Ai\AiPersona;
use App\Models\Ai\AiCreditRule;
use App\Models\Ai\AiModel;
use App\Models\Ai\AiProvider;
use App\Models\Ai\AiRoutingRule;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Services\SiteAi\SiteAiRegistry;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AiController extends Controller
{
    public function index(
        SiteAiRegistry $registry
    ): View {

        $personas =
            AiPersona::query()
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


        $capabilities =
            collect(
                $registry->all()
            )
            ->map(
                function (
                    $capability,
                    $key
                ) {

                    $definition =
                        method_exists(
                            $capability,
                            'definition'
                        )
                            ? $capability
                                ->definition()
                            : $capability
                                ->manifest();

                    return [
                        'key' =>
                            $key,

                        'label' =>
                            $definition[
                                'label'
                            ] ?? $key,

                        'version' =>
                            $definition[
                                'version'
                            ] ?? null,

                        'functions' =>
                            collect(
                                $definition[
                                    'functions'
                                ] ?? []
                            )
                            ->pluck(
                                'label'
                            )
                            ->filter()
                            ->values()
                            ->all(),
                    ];
                }
            )
            ->values();


        $services = [
            [
                'key' => 'site',
                'label' => 'Site AI',
                'description' =>
                    'Theme, Page Builder, products, content and other internal website AI tools.',
            ],

            [
                'key' => 'live_chat',
                'label' => 'Live Chat AI',
                'description' =>
                    'Customer-facing AI conversations through website live chat.',
            ],

            [
                'key' => 'whatsapp',
                'label' => 'WhatsApp AI',
                'description' =>
                    'AI-powered WhatsApp support, enquiries and automated replies.',
            ],

            [
                'key' => 'email',
                'label' => 'Email AI',
                'description' =>
                    'Drafts, replies, campaigns, follow-ups and subject lines.',
            ],

            [
                'key' => 'sms',
                'label' => 'SMS AI',
                'description' =>
                    'SMS campaigns, reminders, alerts and concise generated messages.',
            ],

            [
                'key' => 'social_media',
                'label' => 'Social Media AI',
                'description' =>
                    'Posts, captions, images, scheduling and publishing assistance.',
            ],

            [
                'key' => 'ads',
                'label' => 'Ads AI',
                'description' =>
                    'Ad concepts, copy, creatives, CTAs and campaign variations.',
            ],

            [
                'key' => 'app_builder',
                'label' => 'App Builder AI',
                'description' =>
                    'Build and assist with Esubiz applications using registered app architecture, components and development capabilities.',
            ],

            [
                'key' => 'module_builder',
                'label' => 'Module Builder AI',
                'description' =>
                    'Build and assist with Esubiz modules using the central module architecture and registered module capabilities.',
            ],

            [
                'key' => 'addon_builder',
                'label' => 'Addon Builder AI',
                'description' =>
                    'Build and assist with Esubiz addons using the shared addon architecture and registered addon capabilities.',
            ],
        ];


        $providers =
            AiProvider::query()
                ->with('models')
                ->orderBy('priority')
                ->orderBy('name')
                ->get();


        $models =
            AiModel::query()
                ->with('provider')
                ->orderBy('name')
                ->get();


        $routingRules =
            AiRoutingRule::query()
                ->with([
                    'model.provider',
                    'fallbackModel.provider',
                ])
                ->orderBy('priority')
                ->orderBy('route_key')
                ->get();


        $creditRules =
            AiCreditRule::query()
                ->orderBy('key')
                ->get();


        return view(
            'admin.ai.index',
            compact(
                'personas',
                'capabilities',
                'services',
                'providers',
                'models',
                'routingRules',
                'creditRules'
            )
        );
    }


    /*
    |--------------------------------------------------------------------------
    | OFFICIAL ESUBIZ AI AVATARS
    |--------------------------------------------------------------------------
    */

    public function storeAvatar(
        Request $request
    ): RedirectResponse {

        $data =
            $request->validate([
                'name' => [
                    'required',
                    'string',
                    'max:100',
                ],

                'avatar' => [
                    'required',
                    'file',
                    'image',
                    'mimes:jpg,jpeg,png,webp',
                    'max:10240',
                ],

                'description' => [
                    'nullable',
                    'string',
                    'max:1000',
                ],

                'persona_prompt' => [
                    'nullable',
                    'string',
                    'max:10000',
                ],

                'gender' => [
                    'nullable',
                    'string',
                    'max:30',
                ],

                'sort_order' => [
                    'nullable',
                    'integer',
                    'min:0',
                ],
            ]);


        $avatarFile =
            $request->file(
                'avatar'
            );


        if (
            !$avatarFile
            || !$avatarFile->isValid()
        ) {
            return back()
                ->withInput()
                ->withErrors([
                    'avatar' =>
                        'The avatar photo could not be uploaded. Please choose the image again.',
                ]);
        }


        $avatarPath =
            app(
                CentralMediaService::class
            )->store(
                $avatarFile,
                'ai/avatars'
            );


        if (
            !$avatarPath
            || !app(
                CentralMediaService::class
            )->exists(
                $avatarPath
            )
        ) {
            return back()
                ->withInput()
                ->withErrors([
                    'avatar' =>
                        'The avatar photo could not be saved to Esubiz Central storage.',
                ]);
        }


        $persona =
            AiPersona::create([
                'name' =>
                    trim(
                        $data['name']
                    ),

                'avatar_path' =>
                    $avatarPath,

                'description' =>
                    $data['description']
                    ?? null,

                'persona_prompt' =>
                    $data['persona_prompt']
                    ?? null,

                'gender' =>
                    $data['gender']
                    ?? null,

                'sort_order' =>
                    $data['sort_order']
                    ?? 0,

                'is_active' =>
                    $request->boolean(
                        'is_active',
                        true
                    ),

                'is_default' =>
                    false,
            ]);


        if (
            $request->boolean(
                'is_default'
            )
        ) {

            AiPersona::query()
                ->where(
                    'id',
                    '!=',
                    $persona->id
                )
                ->update([
                    'is_default' => false,
                ]);


            $persona->update([
                'is_default' => true,
            ]);
        }


        return back()->with(
            'success',
            'AI avatar created.'
        );
    }


    public function updateAvatar(
        Request $request,
        AiPersona $persona
    ): RedirectResponse {

        $data =
            $request->validate([
                'name' => [
                    'required',
                    'string',
                    'max:100',
                ],

                'avatar' => [
                    'nullable',
                    'image',
                    'mimes:jpg,jpeg,png,webp',
                    'max:5120',
                ],

                'description' => [
                    'nullable',
                    'string',
                    'max:1000',
                ],

                'persona_prompt' => [
                    'nullable',
                    'string',
                    'max:10000',
                ],

                'gender' => [
                    'nullable',
                    'string',
                    'max:30',
                ],

                'sort_order' => [
                    'nullable',
                    'integer',
                    'min:0',
                ],
            ]);


        $values = [
            'name' =>
                trim(
                    $data['name']
                ),

            'description' =>
                $data['description']
                ?? null,

            'persona_prompt' =>
                $data['persona_prompt']
                ?? null,

            'gender' =>
                $data['gender']
                ?? null,

            'sort_order' =>
                $data['sort_order']
                ?? 0,

            'is_active' =>
                $request->boolean(
                    'is_active'
                ),
        ];


        if (
            $request->hasFile(
                'avatar'
            )
        ) {

            $values[
                'avatar_path'
            ] =
                app(
                    CentralMediaService::class
                )->replace(
                    $persona->avatar_path,
                    $request->file(
                        'avatar'
                    ),
                    'ai/avatars'
                );
        }


        $persona->update(
            $values
        );


        if (
            $request->boolean(
                'is_default'
            )
        ) {

            AiPersona::query()
                ->where(
                    'id',
                    '!=',
                    $persona->id
                )
                ->update([
                    'is_default' =>
                        false,
                ]);


            $persona->update([
                'is_default' =>
                    true,
            ]);
        }


        return back()->with(
            'success',
            'AI avatar updated.'
        );
    }


    public function destroyAvatar(
        AiPersona $persona
    ): RedirectResponse {

        abort_if(
            $persona->is_default,
            422,
            'The default AI avatar cannot be deleted.'
        );


        app(
            CentralMediaService::class
        )->delete(
            $persona->avatar_path
        );


$persona->delete();


        return back()->with(
            'success',
            'AI avatar deleted.'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | CENTRAL AI PROVIDERS / API CREDENTIALS
    |--------------------------------------------------------------------------
    */

    public function storeProvider(
        Request $request
    ): RedirectResponse {

        $data =
            $request->validate([
                'name' => [
                    'required',
                    'string',
                    'max:100',
                ],

                'driver' => [
                    'required',
                    'string',
                    'max:100',
                ],

                'base_url' => [
                    'nullable',
                    'url',
                    'max:1000',
                ],

                'api_key' => [
                    'nullable',
                    'string',
                    'max:10000',
                ],

                'organization' => [
                    'nullable',
                    'string',
                    'max:500',
                ],

                'priority' => [
                    'nullable',
                    'integer',
                    'min:0',
                ],
            ]);


        $provider =
            AiProvider::create([
                'name' =>
                    trim(
                        $data['name']
                    ),

                'slug' =>
                    Str::slug(
                        $data['name']
                    ),

                'driver' =>
                    trim(
                        $data['driver']
                    ),

                'base_url' =>
                    $data['base_url']
                    ?? null,

                'secret_payload' => [
                    'api_key' =>
                        $data['api_key']
                        ?? null,

                    'organization' =>
                        $data['organization']
                        ?? null,
                ],

                'priority' =>
                    $data['priority']
                    ?? 100,

                'is_active' =>
                    $request->boolean(
                        'is_active',
                        true
                    ),

                'is_default' =>
                    false,
            ]);


        if (
            $request->boolean(
                'is_default'
            )
        ) {

            AiProvider::query()
                ->where(
                    'id',
                    '!=',
                    $provider->id
                )
                ->update([
                    'is_default' =>
                        false,
                ]);


            $provider->update([
                'is_default' =>
                    true,
            ]);
        }


        return back()->with(
            'success',
            'AI provider saved.'
        );
    }


    public function updateProvider(
        Request $request,
        AiProvider $provider
    ): RedirectResponse {

        $data =
            $request->validate([
                'name' => [
                    'required',
                    'string',
                    'max:100',
                ],

                'driver' => [
                    'required',
                    'string',
                    'max:100',
                ],

                'base_url' => [
                    'nullable',
                    'url',
                    'max:1000',
                ],

                'api_key' => [
                    'nullable',
                    'string',
                    'max:10000',
                ],

                'organization' => [
                    'nullable',
                    'string',
                    'max:500',
                ],

                'priority' => [
                    'nullable',
                    'integer',
                    'min:0',
                ],
            ]);


        $secrets =
            $provider->secret_payload
            ?? [];


        /*
         * Blank key means keep the existing encrypted key.
         */
        if (
            !empty(
                $data['api_key']
            )
        ) {
            $secrets[
                'api_key'
            ] =
                $data[
                    'api_key'
                ];
        }


        if (
            array_key_exists(
                'organization',
                $data
            )
        ) {
            $secrets[
                'organization'
            ] =
                $data[
                    'organization'
                ];
        }


        $provider->update([
            'name' =>
                trim(
                    $data['name']
                ),

            'driver' =>
                trim(
                    $data['driver']
                ),

            'base_url' =>
                $data['base_url']
                ?? null,

            'secret_payload' =>
                $secrets,

            'priority' =>
                $data['priority']
                ?? 100,

            'is_active' =>
                $request->boolean(
                    'is_active'
                ),
        ]);


        if (
            $request->boolean(
                'is_default'
            )
        ) {

            AiProvider::query()
                ->where(
                    'id',
                    '!=',
                    $provider->id
                )
                ->update([
                    'is_default' =>
                        false,
                ]);


            $provider->update([
                'is_default' =>
                    true,
            ]);
        }


        return back()->with(
            'success',
            'AI provider updated.'
        );
    }


    public function destroyProvider(
        AiProvider $provider
    ): RedirectResponse {

        abort_if(
            $provider->is_default,
            422,
            'The default AI provider cannot be deleted.'
        );


        $provider->delete();


        return back()->with(
            'success',
            'AI provider deleted.'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | PROVIDER MODELS
    |--------------------------------------------------------------------------
    */

    public function storeModel(
        Request $request
    ): RedirectResponse {

        $data =
            $request->validate([
                'ai_provider_id' => [
                    'required',
                    'exists:ai_providers,id',
                ],

                'name' => [
                    'required',
                    'string',
                    'max:150',
                ],

                'model_key' => [
                    'required',
                    'string',
                    'max:255',
                ],

                'capabilities' => [
                    'nullable',
                    'array',
                ],

                'input_cost_per_million' => [
                    'nullable',
                    'numeric',
                    'min:0',
                ],

                'output_cost_per_million' => [
                    'nullable',
                    'numeric',
                    'min:0',
                ],
            ]);


        AiModel::create([
            'ai_provider_id' =>
                $data[
                    'ai_provider_id'
                ],

            'name' =>
                trim(
                    $data['name']
                ),

            'model_key' =>
                trim(
                    $data['model_key']
                ),

            'capabilities' =>
                array_values(
                    $data[
                        'capabilities'
                    ]
                    ?? []
                ),

            'input_cost_per_million' =>
                $data[
                    'input_cost_per_million'
                ]
                ?? null,

            'output_cost_per_million' =>
                $data[
                    'output_cost_per_million'
                ]
                ?? null,

            'is_active' =>
                $request->boolean(
                    'is_active',
                    true
                ),
        ]);


        return back()->with(
            'success',
            'AI model registered.'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | ROUTING
    |--------------------------------------------------------------------------
    */

    public function storeRoutingRule(
        Request $request
    ): RedirectResponse {

        $data =
            $request->validate([
                'route_key' => [
                    'required',
                    'string',
                    'max:255',
                    'unique:ai_routing_rules,route_key',
                ],

                'label' => [
                    'required',
                    'string',
                    'max:255',
                ],

                'ai_model_id' => [
                    'nullable',
                    'exists:ai_models,id',
                ],

                'fallback_model_id' => [
                    'nullable',
                    'exists:ai_models,id',
                ],

                'priority' => [
                    'nullable',
                    'integer',
                    'min:0',
                ],
            ]);


        AiRoutingRule::create([
            'route_key' =>
                trim(
                    $data['route_key']
                ),

            'label' =>
                trim(
                    $data['label']
                ),

            'ai_model_id' =>
                $data['ai_model_id']
                ?? null,

            'fallback_model_id' =>
                $data[
                    'fallback_model_id'
                ]
                ?? null,

            'priority' =>
                $data['priority']
                ?? 100,

            'is_active' =>
                $request->boolean(
                    'is_active',
                    true
                ),
        ]);


        return back()->with(
            'success',
            'AI routing rule created.'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | AI CREDIT RULES
    |--------------------------------------------------------------------------
    */

    public function storeCreditRule(
        Request $request
    ): RedirectResponse {

        $data =
            $request->validate([
                'key' => [
                    'required',
                    'string',
                    'max:255',
                    'unique:ai_credit_rules,key',
                ],

                'label' => [
                    'required',
                    'string',
                    'max:255',
                ],

                'unit' => [
                    'required',
                    'string',
                    'max:100',
                ],

                'credits_per_unit' => [
                    'required',
                    'numeric',
                    'min:0',
                ],

                'minimum_credits' => [
                    'required',
                    'numeric',
                    'min:0',
                ],
            ]);


        AiCreditRule::create([
            'key' =>
                trim(
                    $data['key']
                ),

            'label' =>
                trim(
                    $data['label']
                ),

            'unit' =>
                trim(
                    $data['unit']
                ),

            'credits_per_unit' =>
                $data[
                    'credits_per_unit'
                ],

            'minimum_credits' =>
                $data[
                    'minimum_credits'
                ],

            'is_active' =>
                $request->boolean(
                    'is_active',
                    true
                ),
        ]);


        return back()->with(
            'success',
            'AI credit rule created.'
        );
    }



    /**
     * Public delivery endpoint for official Esubiz AI avatars.
     *
     * Files remain in Central/Landlord storage.
     * This endpoint avoids relying on the public/storage
     * symlink, which may be blocked by the web server.
     */
    public function avatarImage(
        AiPersona $persona
    ) {
        $storedPath =
            trim(
                (string) (
                    $persona->avatar_path
                    ?? ''
                )
            );


        abort_if(
            $storedPath === '',
            404
        );


        $relative =
            ltrim(
                $storedPath,
                '/'
            );


        /*
         * Backward compatibility:
         * /storage/ai/avatars/...
         */
        if (
            str_starts_with(
                $relative,
                'storage/'
            )
        ) {
            $relative =
                substr(
                    $relative,
                    strlen('storage/')
                );
        }


        $disk =
            Storage::disk(
                'public'
            );


        abort_unless(
            $disk->exists(
                $relative
            ),
            404
        );


        $absolutePath =
            $disk->path(
                $relative
            );


        $mime =
            mime_content_type(
                $absolutePath
            )
            ?: 'application/octet-stream';


        return response()->file(
            $absolutePath,
            [
                'Content-Type' =>
                    $mime,

                'Cache-Control' =>
                    'public, max-age=86400',
            ]
        );
    }



    /**
     * Generic AJAX persistence for existing Central AI records.
     *
     * Creation forms remain explicit submits.
     * Existing configuration fields autosave through this endpoint.
     */
    public function autosaveSetting(
        Request $request
    ): \Illuminate\Http\JsonResponse {

        $data =
            $request->validate([
                'type' => [
                    'required',
                    'string',
                    \Illuminate\Validation\Rule::in([
                        'avatar',
                        'provider',
                        'model',
                        'routing',
                        'credit',
                    ]),
                ],

                'id' => [
                    'required',
                    'integer',
                    'min:1',
                ],

                'field' => [
                    'required',
                    'string',
                    'max:100',
                ],

                'value' => [
                    'nullable',
                ],
            ]);


        $maps = [
            'avatar' => [
                'model' =>
                    \App\Models\Ai\AiPersona::class,

                'fields' => [
                    'is_active',
                    'is_default',
                    'sort_order',
                ],
            ],

            'provider' => [
                'model' =>
                    \App\Models\Ai\AiProvider::class,

                'fields' => [
                    'is_active',
                    'is_default',
                    'priority',
                ],
            ],

            'model' => [
                'model' =>
                    \App\Models\Ai\AiModel::class,

                'fields' => [
                    'is_active',
                ],
            ],

            'routing' => [
                'model' =>
                    \App\Models\Ai\AiRoutingRule::class,

                'fields' => [
                    'is_active',
                    'priority',
                    'ai_model_id',
                    'fallback_model_id',
                ],
            ],

            'credit' => [
                'model' =>
                    \App\Models\Ai\AiCreditRule::class,

                'fields' => [
                    'is_active',
                    'credits_per_unit',
                    'minimum_credits',
                ],
            ],
        ];


        $definition =
            $maps[
                $data['type']
            ];


        abort_unless(
            in_array(
                $data['field'],
                $definition['fields'],
                true
            ),
            422,
            'This setting cannot be changed through autosave.'
        );


        $record =
            $definition['model']::query()
                ->findOrFail(
                    $data['id']
                );


        $booleanFields = [
            'is_active',
            'is_default',
        ];


        $integerFields = [
            'sort_order',
            'priority',
            'ai_model_id',
            'fallback_model_id',
        ];


        $decimalFields = [
            'credits_per_unit',
            'minimum_credits',
        ];


        if (
            in_array(
                $data['field'],
                $booleanFields,
                true
            )
        ) {

            $value =
                filter_var(
                    $data['value'],
                    FILTER_VALIDATE_BOOLEAN
                );


        } elseif (
            in_array(
                $data['field'],
                $integerFields,
                true
            )
        ) {

            $value =
                (
                    $data['value'] === null
                    || $data['value'] === ''
                )
                    ? null
                    : (int) $data['value'];


        } elseif (
            in_array(
                $data['field'],
                $decimalFields,
                true
            )
        ) {

            abort_unless(
                is_numeric(
                    $data['value']
                ),
                422,
                'A numeric value is required.'
            );

            $value =
                (float) $data['value'];


        } else {

            $value =
                $data['value'];
        }


        /*
         * Only one Central default avatar/provider at a time.
         */
        if (
            $data['field'] === 'is_default'
            && $value === true
            && in_array(
                $data['type'],
                [
                    'avatar',
                    'provider',
                ],
                true
            )
        ) {

            $definition['model']::query()
                ->where(
                    'id',
                    '!=',
                    $record->id
                )
                ->update([
                    'is_default' =>
                        false,
                ]);
        }


        $record->update([
            $data['field'] =>
                $value,
        ]);


        return response()->json([
            'success' =>
                true,

            'message' =>
                'Saved',

            'type' =>
                $data['type'],

            'id' =>
                $record->id,

            'field' =>
                $data['field'],

            'value' =>
                $record->fresh()
                    ->getAttribute(
                        $data['field']
                    ),
        ]);
    }



    public function updateCommercialSetting(
        \Illuminate\Http\Request $request,
        \App\Models\Ai\AiCommercialSetting $setting
    ): \Illuminate\Http\JsonResponse {

        $data = $request->validate([
            'field' => [
                'required',
                'string',
                \Illuminate\Validation\Rule::in([
                    'value',
                ]),
            ],

            'value' => [
                'required',
                'numeric',
            ],
        ]);


        $value = (float) $data['value'];


        if (
            $setting->key === 'provider_markup_percent'
            && (
                $value < 0
                || $value > 5000
            )
        ) {
            return response()->json([
                'success' => false,
                'message' => 'Markup must be between 0% and 5000%.',
            ], 422);
        }


        if (
            in_array(
                $setting->key,
                [
                    'naira_per_ai_credit',
                    'usd_ngn_rate',
                ],
                true
            )
            && $value <= 0
        ) {
            return response()->json([
                'success' => false,
                'message' => 'This value must be greater than zero.',
            ], 422);
        }


        $setting->update([
            'value' => $value,
        ]);


        return response()->json([
            'success' => true,
            'message' => 'Saved',
            'key' => $setting->key,
            'value' => (float) $setting->fresh()->value,
            'selling_multiplier' =>
                $setting->key === 'provider_markup_percent'
                    ? 1 + ($value / 100)
                    : null,
        ]);
    }


    public function updateModelPricing(
        \Illuminate\Http\Request $request,
        \App\Models\Ai\AiModel $model
    ): \Illuminate\Http\JsonResponse {

        $data = $request->validate([
            'field' => [
                'required',
                'string',
                \Illuminate\Validation\Rule::in([
                    'input_cost_per_million',
                    'cached_input_cost_per_million',
                    'output_cost_per_million',
                ]),
            ],

            'value' => [
                'required',
                'numeric',
                'min:0',
                'max:1000000',
            ],
        ]);


        $field = $data['field'];

        $model->update([
            $field => (float) $data['value'],
        ]);


        return response()->json([
            'success' => true,
            'message' => 'Saved',
            'model_id' => $model->id,
            'field' => $field,
            'value' => (float) $model->fresh()->getAttribute($field),
        ]);
    }

}
