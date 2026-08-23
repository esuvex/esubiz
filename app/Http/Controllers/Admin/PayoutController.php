<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PayoutController extends Controller
{
    public function index()
    {
        $automaticCount = DB::table('payout_methods')
            ->whereNull('deleted_at')
            ->whereNotNull('payment_provider_id')
            ->count();

        $manualCount = DB::table('payout_methods')
            ->whereNull('deleted_at')
            ->whereNull('payment_provider_id')
            ->count();

        return view(
            'admin.site-settings.payout',
            compact(
                'automaticCount',
                'manualCount'
            )
        );
    }

    public function automatic()
    {
        $automaticMethods = DB::table('payout_methods as methods')
            ->leftJoin(
                'payment_providers as providers',
                'providers.id',
                '=',
                'methods.payment_provider_id'
            )
            ->whereNull('methods.deleted_at')
            ->whereNotNull('methods.payment_provider_id')
            ->select([
                'methods.*',
                'providers.name as provider_name',
                'providers.slug as provider_slug',
                'providers.is_active as provider_is_active',
            ])
            ->orderBy('methods.priority')
            ->get();

        return view(
            'admin.site-settings.payout-automatic',
            compact('automaticMethods')
        );
    }

    public function manual()
    {
        $manualMethods = DB::table('payout_methods')
            ->whereNull('deleted_at')
            ->whereNull('payment_provider_id')
            ->orderBy('priority')
            ->orderBy('name')
            ->paginate(
                10,
                ['*'],
                'manual_page'
            );

        $requests = DB::table('payout_requests as requests')
            ->leftJoin(
                'users',
                'users.id',
                '=',
                'requests.user_id'
            )
            ->leftJoin(
                'payout_methods as methods',
                'methods.id',
                '=',
                'requests.payout_method_id'
            )
            ->whereNull('requests.deleted_at')
            ->whereNull('methods.payment_provider_id')
            ->select([
                'requests.*',
                'users.name as user_name',
                'users.email as user_email',
                'methods.name as payout_method_name',
                'methods.type as payout_method_type',
            ])
            ->orderByDesc('requests.created_at')
            ->paginate(
                10,
                ['*'],
                'request_page'
            );

        return view(
            'admin.site-settings.payout-manual',
            compact(
                'manualMethods',
                'requests'
            )
        );
    }


    public function updateAutomatic(
        Request $request,
        int $method
    ) {
        $payoutMethod = DB::table('payout_methods')
            ->where('id', $method)
            ->whereNotNull('payment_provider_id')
            ->whereNull('deleted_at')
            ->first();

        abort_unless($payoutMethod, 404);

        $data = $request->validate([
            'fixed_fee' => ['nullable', 'numeric', 'min:0'],
            'percentage_fee' => ['nullable', 'numeric', 'min:0'],
            'minimum_amount' => ['nullable', 'numeric', 'min:0'],
            'maximum_amount' => ['nullable', 'numeric', 'min:0'],
            'processing_time' => ['nullable', 'integer', 'min:0'],
            'priority' => ['nullable', 'integer', 'min:1'],
            'supported_currencies' => ['nullable', 'string'],
            'supported_countries' => ['nullable', 'string'],

            'markdown_enabled' => [
                'nullable',
                'boolean',
            ],

            'markdown_type' => [
                'nullable',
                'in:percentage,fixed',
            ],

            'markdown_value' => [
                'nullable',
                'numeric',
                'min:0',
            ],

            'usage_contexts' => [
                'nullable',
                'array',
            ],

            'usage_contexts.*' => [
                'string',
                'in:user_payout,developer_payout,marketplace_payout',
            ],
        ]);

        $isDefault = $request->boolean('is_default');

        DB::transaction(function () use (
            $method,
            $data,
            $request,
            $isDefault
        ) {
            if ($isDefault) {
                DB::table('payout_methods')
                    ->whereNull('deleted_at')
                    ->update([
                        'is_default' => false,
                        'updated_at' => now(),
                    ]);
            }

            DB::table('payout_methods')
                ->where('id', $method)
                ->update([
                    'fixed_fee' =>
                        (float) ($data['fixed_fee'] ?? 0),

                    'percentage_fee' =>
                        (float) ($data['percentage_fee'] ?? 0),

                    'minimum_amount' =>
                        (float) ($data['minimum_amount'] ?? 0),

                    'maximum_amount' =>
                        isset($data['maximum_amount'])
                            && $data['maximum_amount'] !== ''
                                ? (float) $data['maximum_amount']
                                : null,

                    'processing_time' =>
                        (int) ($data['processing_time'] ?? 0),

                    'priority' =>
                        (int) ($data['priority'] ?? 1),

                    'supported_currencies' =>
                        $this->normaliseList(
                            $data['supported_currencies'] ?? ''
                        ),

                    'supported_countries' =>
                        $this->normaliseList(
                            $data['supported_countries'] ?? ''
                        ),

                    'is_active' =>
                        $request->boolean('is_active'),

                    'is_default' =>
                        $isDefault,

                    'markdown_enabled' =>
                        $request->boolean('markdown_enabled'),

                    'markdown_type' =>
                        $data['markdown_type']
                            ?? 'percentage',

                    'markdown_value' =>
                        max(
                            0,
                            (float) (
                                $data['markdown_value'] ?? 0
                            )
                        ),

                    'usage_contexts' => json_encode(
                        array_values(
                            array_intersect(
                                $data['usage_contexts'] ?? [],
                                [
                                    'user_payout',
                                    'developer_payout',
                                    'marketplace_payout',
                                ]
                            )
                        )
                    ),

                    'updated_at' =>
                        now(),
                ]);
        });

        return back()->with(
            'success',
            'Automatic payout method updated successfully.'
        );
    }

    /**
     * Create a manual payout method.
     */
    public function storeManual(Request $request)
    {
        $data = $this->validateManualPayoutMethod($request);

        $normalisedFormFields =
            $this->normaliseManualPayoutFormFields(
                $data['form_fields'] ?? []
            );

        $conversion =
            $this->manualPayoutConversionData(
                $request,
                $data
            );

        $isDefault = $request->boolean('is_default');

        $slug = Str::slug($data['name'])
            . '-'
            . Str::lower(Str::random(6));

        DB::transaction(function () use (
            $data,
            $request,
            $normalisedFormFields,
            $conversion,
            $isDefault,
            $slug
        ) {
            if ($isDefault) {
                DB::table('payout_methods')
                    ->whereNull('deleted_at')
                    ->update([
                        'is_default' => false,
                        'updated_at' => now(),
                    ]);
            }

            DB::table('payout_methods')->insert([
                'payment_provider_id' => null,
                'uuid' => (string) Str::uuid(),
                'name' => $data['name'],
                'slug' => $slug,
                'type' => $data['type'],

                'supported_currencies' =>
                    $this->normaliseList(
                        $data['supported_currencies'] ?? ''
                    ),

                'supported_countries' =>
                    $this->normaliseList(
                        $data['supported_countries'] ?? ''
                    ),

                'fixed_fee' =>
                    (float) ($data['fixed_fee'] ?? 0),

                'percentage_fee' =>
                    (float) ($data['percentage_fee'] ?? 0),

                'minimum_amount' =>
                    (float) ($data['minimum_amount'] ?? 0),

                'maximum_amount' =>
                    isset($data['maximum_amount'])
                    && $data['maximum_amount'] !== ''
                        ? (float) $data['maximum_amount']
                        : null,

                'processing_time' =>
                    (int) ($data['processing_time'] ?? 0),

                'priority' =>
                    (int) ($data['priority'] ?? 10),

                'is_active' =>
                    $request->boolean('is_active'),

                'is_default' => $isDefault,

                'conversion_enabled' =>
                    $conversion['conversion_enabled'],

                'conversion_type' =>
                    $conversion['conversion_type'],

                'conversion_provider' =>
                    $conversion['conversion_provider'],

                'conversion_target' =>
                    $conversion['conversion_target'],

                'manual_conversion_rate' =>
                    $conversion['manual_conversion_rate'],

                'conversion_settings' =>
                    $conversion['conversion_settings'],

                'markdown_enabled' =>
                    $conversion['markdown_enabled'],

                'markdown_type' =>
                    $conversion['markdown_type'],

                'markdown_value' =>
                    $conversion['markdown_value'],

                'usage_contexts' =>
                    $conversion['usage_contexts'],

                'form_fields' => json_encode(
                    $normalisedFormFields
                ),

                'created_at' => now(),
                'updated_at' => now(),
                'deleted_at' => null,
            ]);
        });

        return back()->with(
            'success',
            'Manual payout method created successfully.'
        );
    }


    /**
     * Update a manual payout method.
     */
    public function updateManual(
        Request $request,
        int $method
    ) {
        $payoutMethod = DB::table('payout_methods')
            ->where('id', $method)
            ->whereNull('payment_provider_id')
            ->whereNull('deleted_at')
            ->first();

        abort_unless($payoutMethod, 404);

        $data = $this->validateManualPayoutMethod($request);

        $normalisedFormFields =
            $this->normaliseManualPayoutFormFields(
                $data['form_fields'] ?? []
            );

        $conversion =
            $this->manualPayoutConversionData(
                $request,
                $data
            );

        $isDefault = $request->boolean('is_default');

        DB::transaction(function () use (
            $method,
            $data,
            $request,
            $normalisedFormFields,
            $conversion,
            $isDefault
        ) {
            if ($isDefault) {
                DB::table('payout_methods')
                    ->where('id', '!=', $method)
                    ->whereNull('deleted_at')
                    ->update([
                        'is_default' => false,
                        'updated_at' => now(),
                    ]);
            }

            DB::table('payout_methods')
                ->where('id', $method)
                ->update([
                    'name' => $data['name'],
                    'type' => $data['type'],

                    'supported_currencies' =>
                        $this->normaliseList(
                            $data['supported_currencies'] ?? ''
                        ),

                    'supported_countries' =>
                        $this->normaliseList(
                            $data['supported_countries'] ?? ''
                        ),

                    'fixed_fee' =>
                        (float) ($data['fixed_fee'] ?? 0),

                    'percentage_fee' =>
                        (float) ($data['percentage_fee'] ?? 0),

                    'minimum_amount' =>
                        (float) ($data['minimum_amount'] ?? 0),

                    'maximum_amount' =>
                        isset($data['maximum_amount'])
                        && $data['maximum_amount'] !== ''
                            ? (float) $data['maximum_amount']
                            : null,

                    'processing_time' =>
                        (int) ($data['processing_time'] ?? 0),

                    'priority' =>
                        (int) ($data['priority'] ?? 10),

                    'is_active' =>
                        $request->boolean('is_active'),

                    'is_default' => $isDefault,

                    /*
                     * Conversion belongs to THIS payout method.
                     */
                    'conversion_enabled' =>
                        $conversion['conversion_enabled'],

                    'conversion_type' =>
                        $conversion['conversion_type'],

                    'conversion_provider' =>
                        $conversion['conversion_provider'],

                    'conversion_target' =>
                        $conversion['conversion_target'],

                    'manual_conversion_rate' =>
                        $conversion['manual_conversion_rate'],

                    'conversion_settings' =>
                        $conversion['conversion_settings'],

                    /*
                     * Admin-defined User/Developer form.
                     */
                    'markdown_enabled' =>
                        $conversion['markdown_enabled'],

                    'markdown_type' =>
                        $conversion['markdown_type'],

                    'markdown_value' =>
                        $conversion['markdown_value'],

                    'usage_contexts' =>
                        $conversion['usage_contexts'],

                    'form_fields' => json_encode(
                        $normalisedFormFields
                    ),

                    'updated_at' => now(),
                ]);
        });

        return back()->with(
            'success',
            'Manual payout method updated successfully.'
        );
    }


    /**
     * Shared validation for manual payout methods.
     */
    private function validateManualPayoutMethod(
        Request $request
    ): array {
        return $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
            ],

            'type' => [
                'required',
                'in:bank_transfer,wallet,paypal,stripe_connect,crypto,mobile_money,other',
            ],

            'fixed_fee' => [
                'nullable',
                'numeric',
                'min:0',
            ],

            'percentage_fee' => [
                'nullable',
                'numeric',
                'min:0',
            ],

            'minimum_amount' => [
                'nullable',
                'numeric',
                'min:0',
            ],

            'maximum_amount' => [
                'nullable',
                'numeric',
                'min:0',
            ],

            'processing_time' => [
                'nullable',
                'integer',
                'min:0',
            ],

            'priority' => [
                'nullable',
                'integer',
                'min:1',
            ],

            'supported_currencies' => [
                'nullable',
                'string',
            ],

            'supported_countries' => [
                'nullable',
                'string',
            ],

            'conversion_enabled' => [
                'nullable',
                'boolean',
            ],

            'conversion_type' => [
                'nullable',
                'in:none,fiat,crypto',
            ],

            'conversion_provider' => [
                'nullable',
                'in:frankfurter,coingecko,manual',
            ],

            'conversion_target' => [
                'nullable',
                'string',
                'max:30',
            ],

            'manual_conversion_rate' => [
                'nullable',
                'numeric',
                'gt:0',
            ],

            'markdown_enabled' => [
                'nullable',
                'boolean',
            ],

            'markdown_type' => [
                'nullable',
                'in:percentage,fixed',
            ],

            'markdown_value' => [
                'nullable',
                'numeric',
                'min:0',
            ],

            'usage_contexts' => [
                'nullable',
                'array',
            ],

            'usage_contexts.*' => [
                'string',
                'in:user_payout,developer_payout,marketplace_payout',
            ],

            'form_fields' => [
                'nullable',
                'array',
            ],

            'form_fields.*.label' => [
                'required_with:form_fields',
                'string',
                'max:255',
            ],

            'form_fields.*.type' => [
                'required_with:form_fields',
                'in:text,number,email,tel,textarea,select,radio,checkbox,date,url,password,hidden,instructions',
            ],

            'form_fields.*.required' => [
                'nullable',
                'boolean',
            ],

            'form_fields.*.placeholder' => [
                'nullable',
                'string',
                'max:255',
            ],

            'form_fields.*.help_text' => [
                'nullable',
                'string',
                'max:500',
            ],

            'form_fields.*.options' => [
                'nullable',
                'string',
                'max:5000',
            ],
        ]);
    }


    /**
     * Normalize Admin-defined payout form fields.
     */
    private function normaliseManualPayoutFormFields(
        array $fields
    ): array {
        return collect($fields)
            ->map(function ($field, $index) {
                $label = trim(
                    (string) ($field['label'] ?? '')
                );

                if ($label === '') {
                    return null;
                }

                $type = $field['type'] ?? 'text';

                $key = Str::slug(
                    $label,
                    '_'
                );

                if ($key === '') {
                    $key = 'field';
                }

                $key .= '_' . ($index + 1);

                $options = [];

                if (
                    in_array(
                        $type,
                        ['select', 'radio', 'checkbox'],
                        true
                    )
                ) {
                    $options = collect(
                        preg_split(
                            '/[\r\n,]+/',
                            (string) (
                                $field['options'] ?? ''
                            )
                        )
                    )
                        ->map(
                            fn ($item) => trim($item)
                        )
                        ->filter()
                        ->unique()
                        ->values()
                        ->all();
                }

                return [
                    'key' => $key,
                    'label' => $label,
                    'type' => $type,

                    'required' => filter_var(
                        $field['required'] ?? false,
                        FILTER_VALIDATE_BOOLEAN
                    ),

                    'placeholder' => trim(
                        (string) (
                            $field['placeholder'] ?? ''
                        )
                    ) ?: null,

                    'help_text' => trim(
                        (string) (
                            $field['help_text'] ?? ''
                        )
                    ) ?: null,

                    'options' => $options,
                    'priority' => $index + 1,
                ];
            })
            ->filter()
            ->values()
            ->all();
    }


    /**
     * Normalize conversion configuration.
     *
     * API credentials are deliberately not stored here.
     */
    private function manualPayoutConversionData(
        Request $request,
        array $data
    ): array {
        $enabled =
            $request->boolean('conversion_enabled');

        $type = $enabled
            ? ($data['conversion_type'] ?? 'none')
            : 'none';

        $provider = $enabled
            ? ($data['conversion_provider'] ?? null)
            : null;

        $target = $enabled
            ? strtoupper(
                trim(
                    (string) (
                        $data['conversion_target'] ?? ''
                    )
                )
            )
            : null;

        if ($target === '') {
            $target = null;
        }

        $manualRate =
            $enabled
            && $provider === 'manual'
                ? (
                    isset($data['manual_conversion_rate'])
                    && $data['manual_conversion_rate'] !== ''
                        ? (float) $data['manual_conversion_rate']
                        : null
                )
                : null;

        return [
            'conversion_enabled' => $enabled,
            'conversion_type' => $type,
            'conversion_provider' => $provider,
            'conversion_target' => $target,
            'manual_conversion_rate' => $manualRate,

            'markdown_enabled' =>
                $request->boolean('markdown_enabled'),

            'markdown_type' =>
                $data['markdown_type']
                    ?? 'percentage',

            'markdown_value' =>
                max(
                    0,
                    (float) (
                        $data['markdown_value'] ?? 0
                    )
                ),

            'usage_contexts' => json_encode(
                array_values(
                    array_intersect(
                        $data['usage_contexts'] ?? [],
                        [
                            'user_payout',
                            'developer_payout',
                            'marketplace_payout',
                        ]
                    )
                )
            ),

            'conversion_settings' => json_encode([
                'rate_visibility' => 'admin_only',
                'show_estimated_receive_amount' => true,
            ]),
        ];
    }


    public function destroyManual(int $method)
    {
        $payoutMethod = DB::table('payout_methods')
            ->where('id', $method)
            ->whereNull('payment_provider_id')
            ->whereNull('deleted_at')
            ->first();

        abort_unless($payoutMethod, 404);

        DB::table('payout_methods')
            ->where('id', $method)
            ->update([
                'is_active' => false,
                'is_default' => false,
                'deleted_at' => now(),
                'updated_at' => now(),
            ]);

        return back()->with(
            'success',
            'Manual payout method removed successfully.'
        );
    }

    protected function normaliseList(string $value): string
    {
        $items = collect(
            preg_split('/[\s,]+/', strtoupper(trim($value)))
        )
            ->filter()
            ->unique()
            ->values()
            ->all();

        return json_encode($items);
    }

    public function completeRequest(int $requestId)
    {
        DB::transaction(function () use ($requestId) {
            $request = DB::table('payout_requests')
                ->where('id', $requestId)
                ->whereNull('deleted_at')
                ->lockForUpdate()
                ->first();

            abort_unless($request, 404);

            /*
             * Manual payout uses one-step Admin settlement:
             *
             * Pending -> Mark Paid -> Completed
             *
             * Previously-approved requests are also accepted here so
             * existing records created by the old two-step flow can be
             * completed safely.
             */
            abort_unless(
                in_array(
                    $request->status,
                    ['pending', 'approved'],
                    true
                ),
                422,
                'Only pending payout requests can be marked paid.'
            );

            $method = DB::table('payout_methods')
                ->where('id', $request->payout_method_id)
                ->whereNull('payment_provider_id')
                ->whereNull('deleted_at')
                ->first();

            abort_unless(
                $method,
                422,
                'This is not a manual payout request.'
            );

            $metadata = json_decode(
                $request->metadata ?? '{}',
                true
            ) ?: [];

            $walletId = (int) (
                $metadata['wallet_id'] ?? 0
            );

            $reservedAmount = (float) (
                $metadata['reserved_wallet_amount']
                    ?? $request->requested_amount
            );

            $wallet = \App\Models\Wallet::query()
                ->where('id', $walletId)
                ->where('user_id', $request->user_id)
                ->lockForUpdate()
                ->firstOrFail();

            abort_if(
                (float) $wallet->reserved_balance < $reservedAmount,
                422,
                'The wallet no longer has enough reserved balance for this payout.'
            );

            /*
             * Funds were removed from available_balance when the
             * withdrawal request was submitted.
             *
             * Mark Paid consumes the reservation only.
             * Never debit available_balance again here.
             */
            $wallet->update([
                'reserved_balance' =>
                    (float) $wallet->reserved_balance
                    - $reservedAmount,
            ]);

            DB::table('payout_requests')
                ->where('id', $request->id)
                ->update([
                    'status' => 'completed',
                    'approved_amount' =>
                        $request->requested_amount,
                    'reviewed_by' => auth()->id(),
                    'reviewed_at' => now(),
                    'updated_at' => now(),
                ]);

            DB::table('wallet_transactions')
                ->where('source_type', 'payout_request')
                ->where('source_id', $request->id)
                ->where('type', 'payout')
                ->update([
                    'status' => 'completed',
                    'balance_after' =>
                        $wallet->fresh()->available_balance,
                    'updated_at' => now(),
                ]);
        });

        return back()->with(
            'success',
            'Payout marked as paid successfully.'
        );
    }


    public function rejectRequest(
        \Illuminate\Http\Request $httpRequest,
        int $requestId
    ) {
        $data = $httpRequest->validate([
            'rejection_reason' => [
                'required',
                'string',
                'max:2000',
            ],
        ]);

        DB::transaction(function () use (
            $requestId,
            $data
        ) {
            $request = DB::table('payout_requests')
                ->where('id', $requestId)
                ->whereNull('deleted_at')
                ->lockForUpdate()
                ->first();

            abort_unless($request, 404);

            abort_unless(
                in_array(
                    $request->status,
                    ['pending', 'approved'],
                    true
                ),
                422,
                'This payout request cannot be rejected.'
            );

            $method = DB::table('payout_methods')
                ->where('id', $request->payout_method_id)
                ->whereNull('payment_provider_id')
                ->whereNull('deleted_at')
                ->first();

            abort_unless(
                $method,
                422,
                'This is not a manual payout request.'
            );

            $metadata = json_decode(
                $request->metadata ?? '{}',
                true
            ) ?: [];

            $walletId = (int) (
                $metadata['wallet_id'] ?? 0
            );

            $reservedAmount = (float) (
                $metadata['reserved_wallet_amount']
                    ?? $request->requested_amount
            );

            $wallet = \App\Models\Wallet::query()
                ->where('id', $walletId)
                ->where('user_id', $request->user_id)
                ->firstOrFail();

            app(
                \App\Services\Core\WalletService::class
            )->releasePayoutReservation(
                $wallet,
                $reservedAmount
            );

            $wallet = $wallet->fresh();

            DB::table('payout_requests')
                ->where('id', $request->id)
                ->update([
                    'status' => 'rejected',
                    'approved_amount' => null,
                    'reviewed_by' => auth()->id(),
                    'reviewed_at' => now(),
                    'rejection_reason' =>
                        $data['rejection_reason'],
                    'updated_at' => now(),
                ]);

            /*
             * Keep the rejected request in the user's Wallet log,
             * but show that no debit ultimately occurred.
             */
            DB::table('wallet_transactions')
                ->where('source_type', 'payout_request')
                ->where('source_id', $request->id)
                ->where('type', 'payout')
                ->update([
                    /*
                     * Rejected payout returned the reserved funds,
                     * therefore this ledger movement was reversed.
                     */
                    'status' => 'reversed',
                    'balance_after' =>
                        $wallet->available_balance,
                    'updated_at' => now(),
                ]);
        });

        return back()->with(
            'success',
            'Payout request rejected and reserved funds returned to the wallet.'
        );
    }

}
