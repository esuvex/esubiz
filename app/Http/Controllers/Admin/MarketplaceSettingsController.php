<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MarketplaceCategory;
use App\Models\MarketplaceFinancialRule;
use App\Models\MarketplaceReferralRule;
use App\Models\MarketplaceSetting;
use App\Services\Marketplace\DeveloperProductTypePolicyService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class MarketplaceSettingsController extends Controller
{
    private const PRODUCT_TYPES = [
        'theme',
        'module',
        'addon',
        'bundle',
        'website_type',
    ];

    public function index(
        DeveloperProductTypePolicyService $developerPolicies
    ): View {
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

            'referralRules' => MarketplaceReferralRule::query()
                ->orderBy('product_type')
                ->orderBy('referrer_role')
                ->paginate(10, ['*'], 'referral_page')
                ->withQueryString(),

            'productTypes' => self::PRODUCT_TYPES,

            'developerPolicies' => collect(self::PRODUCT_TYPES)
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

        return back()->with(
            'success',
            'Marketplace settings updated.'
        );
    }

    public function updateFinancialRule(
        Request $request
    ): RedirectResponse {
        $data = $request->validate([
            'product_type' => [
                'required',
                Rule::in(self::PRODUCT_TYPES),
            ],
            'developer_share_percent' => [
                'required',
                'numeric',
                'min:0',
                'max:100',
            ],
            'expense_percent' => [
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

                'expense_percent' =>
                    $data['expense_percent'],

                'is_active' =>
                    (bool) ($data['is_active'] ?? false),
            ]
        );

        return back()->with(
            'success',
            'Marketplace financial rule updated.'
        );
    }

    public function storeReferralRule(
        Request $request
    ): RedirectResponse {
        $data = $request->validate([
            'product_type' => [
                'required',
                Rule::in(self::PRODUCT_TYPES),
            ],
            'referrer_role' => [
                'required',
                'string',
                'max:80',
            ],
            'level_one_commission_percent' => [
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

        MarketplaceReferralRule::query()->updateOrCreate(
            [
                'product_type' =>
                    $data['product_type'],

                'referrer_role' =>
                    $data['referrer_role'],
            ],
            [
                'level_one_commission_percent' =>
                    $data['level_one_commission_percent'],

                'is_active' =>
                    (bool) ($data['is_active'] ?? false),
            ]
        );

        return back()->with(
            'success',
            'Marketplace referral override saved.'
        );
    }

    public function destroyReferralRule(
        MarketplaceReferralRule $marketplaceReferralRule
    ): RedirectResponse {
        $marketplaceReferralRule->delete();

        return back()->with(
            'success',
            'Marketplace referral override removed.'
        );
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
