<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Support\Facades\DB;

use Illuminate\Support\Facades\Schema;

use App\Http\Controllers\Controller;
use App\Models\CentralTax;
use App\Models\MarketplaceProductTax;
use App\Models\MarketplaceCategory;
use App\Models\MarketplaceFinancialRule;
use App\Models\MarketplaceExpenseRule;
use App\Models\MarketplaceReferralRule;
use App\Models\MarketplaceSetting;
use App\Models\Role;
use App\Services\Marketplace\Developer\DeveloperProductTypePolicyService;
use App\Services\Marketplace\Settings\MarketplaceProductRegistry;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class MarketplaceSettingsController extends Controller
{

    public function index(
        Request $request,
        DeveloperProductTypePolicyService $developerPolicies,
        MarketplaceProductRegistry $productRegistry
    ): View {
        $marketplaceProductCollection = collect(
            $productRegistry->all()
        );

        $productPage = max(
            1,
            (int) $request->query('product_page', 1)
        );

        $marketplaceProductsPaginator = new LengthAwarePaginator(
            $marketplaceProductCollection
                ->forPage($productPage, 10)
                ->values(),
            $marketplaceProductCollection->count(),
            10,
            $productPage,
            [
                'path' => $request->url(),
                'pageName' => 'product_page',
                'query' => $request->query(),
            ]
        );

        return view('admin.marketplace.settings.index', [
            'categories' => MarketplaceCategory::query()
                ->withCount('catalogProducts')
                ->ordered()
                ->paginate(10, ['*'], 'category_page')
                ->withQueryString(),

            'marketplaceSettings' => MarketplaceSetting::query()
                ->orderBy('group')
                ->orderBy('key')
                ->get(),

            'financialRules' => MarketplaceFinancialRule::query()
                ->orderBy('product_type')
                ->get(),

            'expenseRules' => MarketplaceExpenseRule::query()
                ->ordered()
                ->get()
                ->groupBy('product_type'),

            /*
             * Referral Settings are product-centric.
             * One paginated row represents one product and contains all
             * role-specific Level 1 Marketplace referral rules.
             */
            'referralRules' => MarketplaceReferralRule::query()
                ->select('product_type')
                ->selectRaw('MIN(id) as id')
                ->groupBy('product_type')
                ->orderBy('product_type')
                ->paginate(10, ['*'], 'referral_page')
                ->withQueryString()
                ->through(function ($productRow) {
                    $productRow->rules = MarketplaceReferralRule::query()
                        ->where('product_type', $productRow->product_type)
                        ->orderBy('referrer_role')
                        ->get();

                    return $productRow;
                }),

            /*
             * Dynamic Central roles for Marketplace referral configuration.
             * Missing/empty role storage is valid and must never break Settings.
             */
            'referralRoles' => Schema::hasTable('roles')
                ? Role::query()
                    ->where('is_active', true)
                    ->orderBy('name')
                    ->get(['id', 'name', 'slug'])
                : collect(),

            'centralTaxes' => CentralTax::query()
                ->active()
                ->ordered()
                ->get(),

            'productTaxes' => MarketplaceProductTax::query()
                ->with('centralTax')
                ->get()
                ->keyBy('product_type'),

            'productTypes' => $productRegistry->types(),
            'marketplaceProducts' => $marketplaceProductsPaginator,

            /*
             * Only actual Developer-created product families receive
             * Build / Publish controls.
             */
            'developerProductTypes' =>
                $productRegistry->developerBuildTypes(),

            /*
             * Products Developers may sell to customers.
             * This includes Developer-published products plus
             * Esubiz-owned products available for resale.
             */
            'developerSellerTypes' =>
                $productRegistry->developerSellerTypes(),

            'developerResellTypes' =>
                $productRegistry->developerResellTypes(),

            /*
             * Policies are required for every product family that can be
             * built/published by Developers OR resold by Developers.
             * A product may belong to both groups.
             */
            'developerPolicies' => collect(
                array_merge(
                    $productRegistry->developerBuildTypes(),
                    $productRegistry->developerResellTypes()
                )
            )
                ->unique()
                ->values()
                ->mapWithKeys(
                    fn (string $type) => [
                        $type => $developerPolicies->policy($type),
                    ]
                ),
        ]);
    }

    public function updateGeneral(
        Request $request
    ): RedirectResponse {
        $data = $request->validate([
            'marketplace_enabled' => ['nullable', 'boolean'],
            'developer_sales_enabled' => ['nullable', 'boolean'],
            'developer_reseller_enabled' => ['nullable', 'boolean'],
        ]);

        $this->saveSetting(
            'marketplace_enabled',
            (bool) ($data['marketplace_enabled'] ?? false),
            'boolean',
            'general',
            'Controls Marketplace availability.'
        );

        $this->saveSetting(
            'developer_sales_enabled',
            (bool) ($data['developer_sales_enabled'] ?? false),
            'boolean',
            'general',
            'Controls Developer product sales through Marketplace.'
        );

        $this->saveSetting(
            'developer_reseller_enabled',
            (bool) ($data['developer_reseller_enabled'] ?? false),
            'boolean',
            'general',
            'Controls Developer reseller access to eligible Esubiz products.'
        );

        return back()->with(
            'success',
            'Marketplace settings updated.'
        );
    }

    public function updateProductAvailability(
        Request $request
    ): RedirectResponse {
        if ($request->has('products')) {
            $registry = app(MarketplaceProductRegistry::class);

            $data = $request->validate([
                'products' => ['required', 'array'],

                'products.*.product_type' => [
                    'required',
                    Rule::in($registry->types()),
                ],

                'products.*.is_visible' => [
                    'nullable',
                    'boolean',
                ],

                'products.*.is_available' => [
                    'nullable',
                    'boolean',
                ],
            ]);

            DB::transaction(function () use ($data) {
                foreach ($data['products'] as $product) {
                    $productType = $product['product_type'];

                    $this->saveSetting(
                        'product_availability.' . $productType,
                        [
                            'is_visible' =>
                                (bool) ($product['is_visible'] ?? false),

                            'is_available' =>
                                (bool) ($product['is_available'] ?? false),
                        ],
                        'json',
                        'product_availability',
                        'Marketplace visibility and availability for '
                            . $productType . '.'
                    );
                }
            });

            return back()->with(
                'success',
                'Marketplace product availability updated.'
            );
        }

        $data = $request->validate([
            'product_type' => [
                'required',
                Rule::in(app(MarketplaceProductRegistry::class)->types()),
            ],
            'is_visible' => [
                'nullable',
                'boolean',
            ],
            'is_available' => [
                'nullable',
                'boolean',
            ],
        ]);

        $productType = $data['product_type'];

        $this->saveSetting(
            'product_availability.' . $productType,
            [
                'is_visible' =>
                    (bool) ($data['is_visible'] ?? false),

                'is_available' =>
                    (bool) ($data['is_available'] ?? false),
            ],
            'json',
            'product_availability',
            'Marketplace visibility and availability for '
                . $productType . '.'
        );

        return back()->with(
            'success',
            'Marketplace product availability updated.'
        );
    }

    public function updateFinancialRule(
        Request $request
    ): RedirectResponse {
        if ($request->has('financial_rules')) {
            $data = $request->validate([
                'financial_rules' => ['required', 'array'],

                'financial_rules.*.product_type' => [
                    'required',
                    Rule::in(app(MarketplaceProductRegistry::class)->types()),
                ],

                'financial_rules.*.developer_share_percent' => [
                    'required',
                    'numeric',
                    'min:0',
                    'max:100',
                ],

                'financial_rules.*.is_active' => [
                    'nullable',
                    'boolean',
                ],
            ]);

            DB::transaction(function () use ($data) {
                foreach ($data['financial_rules'] as $rule) {
                    MarketplaceFinancialRule::query()->updateOrCreate(
                        [
                            'product_type' => $rule['product_type'],
                        ],
                        [
                            'developer_share_percent' =>
                                $rule['developer_share_percent'],

                            'is_active' =>
                                (bool) ($rule['is_active'] ?? false),
                        ]
                    );
                }
            });

            return back()->with(
                'success',
                'Developer commission settings updated.'
            );
        }

        $data = $request->validate([
            'product_type' => [
                'required',
                Rule::in(app(MarketplaceProductRegistry::class)->types()),
            ],
            'developer_share_percent' => [
                'required',
                'numeric',
                'min:0',
                'max:100',
            ],
            'is_active' => [
                'nullable',
                'boolean',
            ],
        ]);

        MarketplaceFinancialRule::query()->updateOrCreate(
            [
                'product_type' => $data['product_type'],
            ],
            [
                'developer_share_percent' =>
                    $data['developer_share_percent'],

                'is_active' =>
                    (bool) ($data['is_active'] ?? false),
            ]
        );

        return back()->with(
            'success',
            'Marketplace financial rule updated.'
        );
    }

    public function updateProductTax(
        Request $request,
        MarketplaceProductRegistry $productRegistry
    ) {
        $data = $request->validate([
            'product_type' => [
                'required',
                'string',
                Rule::in($productRegistry->types()),
            ],
            'central_tax_id' => [
                'required',
                'integer',
                Rule::exists('central_taxes', 'id')
                    ->where(fn ($query) => $query
                        ->where('is_active', true)
                        ->whereNull('deleted_at')),
            ],
            'is_exclusive' => ['nullable', 'boolean'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        MarketplaceProductTax::query()->updateOrCreate(
            [
                'product_type' => $data['product_type'],
            ],
            [
                'central_tax_id' => $data['central_tax_id'],
                'is_exclusive' => (bool) ($data['is_exclusive'] ?? false),
                'is_active' => (bool) ($data['is_active'] ?? false),
            ]
        );

        return back()
            ->with('success', 'Product tax settings saved.')
            ->with('settings_tab', 'taxes');
    }

    public function destroyProductTax(
        MarketplaceProductTax $productTax
    ) {
        $productTax->delete();

        return back()
            ->with('success', 'Product tax assignment removed.')
            ->with('settings_tab', 'taxes');
    }

    public function storeExpenseRule(
        Request $request
    ): RedirectResponse {
        $data = $this->validateExpenseRule($request);

        MarketplaceExpenseRule::query()->create($data);

        return back()->with(
            'success',
            'Marketplace product expense added.'
        );
    }

    public function updateExpenseRule(
        Request $request,
        MarketplaceExpenseRule $marketplaceExpenseRule
    ): RedirectResponse {
        $data = $this->validateExpenseRule($request);

        $marketplaceExpenseRule->update($data);

        return back()->with(
            'success',
            'Marketplace product expense updated.'
        );
    }

    public function destroyExpenseRule(
        MarketplaceExpenseRule $marketplaceExpenseRule
    ): RedirectResponse {
        $marketplaceExpenseRule->delete();

        return back()->with(
            'success',
            'Marketplace product expense deleted.'
        );
    }

    private function validateExpenseRule(
        Request $request
    ): array {
        return $request->validate([
            'product_type' => [
                'required',
                Rule::in(app(MarketplaceProductRegistry::class)->types()),
            ],
            'name' => [
                'required',
                'string',
                'max:120',
            ],
            'calculation_type' => [
                'required',
                Rule::in([
                    'percentage',
                    'fixed',
                ]),
            ],
            'value' => [
                'required',
                'numeric',
                'min:0',
            ],
            'is_active' => [
                'nullable',
                'boolean',
            ],
            'sort_order' => [
                'nullable',
                'integer',
                'min:0',
            ],
        ]) + [
            'is_active' =>
                $request->boolean('is_active'),

            'sort_order' =>
                (int) $request->input('sort_order', 0),
        ];
    }

    public function storeReferralRule(
        Request $request
    ): RedirectResponse {
        $data = $this->validateReferralRules($request);

        foreach ($data['roles'] as $roleRule) {
            MarketplaceReferralRule::query()->updateOrCreate(
                [
                    'product_type' => $data['product_type'],
                    'referrer_role' => $roleRule['referrer_role'],
                ],
                $this->referralRuleValues($roleRule)
            );
        }

        return back()
            ->with(
                'success',
                'Marketplace referral commissions saved.'
            )
            ->with('settings_tab', 'referrals');
    }

    public function updateReferralRule(
        Request $request,
        MarketplaceReferralRule $marketplaceReferralRule
    ): RedirectResponse {
        $data = $this->validateReferralRules($request);

        $originalProductType = $marketplaceReferralRule->product_type;

        DB::transaction(function () use (
            $data,
            $originalProductType
        ) {
            $selectedRoles = collect($data['roles'])
                ->pluck('referrer_role')
                ->values()
                ->all();

            MarketplaceReferralRule::query()
                ->where('product_type', $originalProductType)
                ->whereNotIn('referrer_role', $selectedRoles)
                ->delete();

            if ($originalProductType !== $data['product_type']) {
                MarketplaceReferralRule::query()
                    ->where('product_type', $originalProductType)
                    ->delete();
            }

            foreach ($data['roles'] as $roleRule) {
                MarketplaceReferralRule::query()->updateOrCreate(
                    [
                        'product_type' => $data['product_type'],
                        'referrer_role' => $roleRule['referrer_role'],
                    ],
                    $this->referralRuleValues($roleRule)
                );
            }
        });

        return back()
            ->with(
                'success',
                'Marketplace referral commissions updated.'
            )
            ->with('settings_tab', 'referrals');
    }

    public function destroyReferralRule(
        MarketplaceReferralRule $marketplaceReferralRule
    ): RedirectResponse {
        MarketplaceReferralRule::query()
            ->where(
                'product_type',
                $marketplaceReferralRule->product_type
            )
            ->delete();

        return back()
            ->with(
                'success',
                'Marketplace product referral commissions removed.'
            )
            ->with('settings_tab', 'referrals');
    }

    private function validateReferralRule(
        Request $request
    ): array {
        $activeRoleSlugs = $this->activeReferralRoleSlugs();

        $data = $request->validate([
            'product_type' => [
                'required',
                Rule::in(app(MarketplaceProductRegistry::class)->types()),
            ],
            'referrer_role' => [
                'required',
                'string',
                'max:80',
                Rule::in($activeRoleSlugs),
            ],
            'calculation_type' => [
                'required',
                Rule::in([
                    'percentage',
                    'fixed',
                ]),
            ],
            'commission_value' => [
                'required',
                'numeric',
                'min:0',
            ],
            'is_active' => [
                'nullable',
                'boolean',
            ],
        ]);

        $this->validateReferralPercentage(
            $data['calculation_type'],
            $data['commission_value'],
            'commission_value'
        );

        $data['is_active'] = $request->boolean('is_active');

        return $data;
    }

    private function validateReferralRules(
        Request $request
    ): array {
        $activeRoleSlugs = $this->activeReferralRoleSlugs();

        $data = $request->validate([
            'product_type' => [
                'required',
                Rule::in(app(MarketplaceProductRegistry::class)->types()),
            ],
            'roles' => [
                'required',
                'array',
                'min:1',
            ],
            'roles.*.referrer_role' => [
                'required',
                'string',
                'max:80',
                'distinct',
                Rule::in($activeRoleSlugs),
            ],
            'roles.*.calculation_type' => [
                'required',
                Rule::in([
                    'percentage',
                    'fixed',
                ]),
            ],
            'roles.*.commission_value' => [
                'required',
                'numeric',
                'min:0',
            ],
            'roles.*.is_active' => [
                'nullable',
                'boolean',
            ],
        ]);

        foreach ($data['roles'] as $index => &$roleRule) {
            $this->validateReferralPercentage(
                $roleRule['calculation_type'],
                $roleRule['commission_value'],
                "roles.{$index}.commission_value"
            );

            $roleRule['is_active'] = filter_var(
                $roleRule['is_active'] ?? false,
                FILTER_VALIDATE_BOOLEAN
            );
        }
        unset($roleRule);

        return $data;
    }

    private function activeReferralRoleSlugs(): array
    {
        if (!\Illuminate\Support\Facades\Schema::hasTable('roles')) {
            return [];
        }

        return Role::query()
            ->where('is_active', true)
            ->pluck('slug')
            ->all();
    }

    private function validateReferralPercentage(
        string $calculationType,
        mixed $commissionValue,
        string $field
    ): void {
        if (
            $calculationType === 'percentage'
            && (float) $commissionValue > 100
        ) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                $field => 'Percentage commission cannot exceed 100%.',
            ]);
        }
    }

    private function referralRuleValues(
        array $data
    ): array {
        return [
            'calculation_type' => $data['calculation_type'],
            'commission_value' => $data['commission_value'],

            /*
             * Compatibility with the existing referral engine.
             * Fixed commissions cannot be represented by the legacy
             * percentage-only field, so it remains zero for fixed rules.
             */
            'level_one_commission_percent' =>
                $data['calculation_type'] === 'percentage'
                    ? $data['commission_value']
                    : 0,

            'is_active' => (bool) $data['is_active'],
        ];
    }

    private function saveSetting(
        string $key,
        mixed $value,
        string $valueType,
        string $group,
        ?string $description = null
    ): void {
        MarketplaceSetting::query()->updateOrCreate(
            ['key' => $key],
            [
                'value' => ['value' => $value],
                'value_type' => $valueType,
                'group' => $group,
                'description' => $description,
                'is_active' => true,
            ]
        );
    }
}
