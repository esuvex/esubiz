<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreWebsiteDraftRequest;
use App\Models\Website;
use App\Services\WebsiteDraftService;
use App\Services\WebsiteService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class WebsiteWizardController extends Controller
{
    public function __construct(
        protected WebsiteDraftService $draftService,
        protected WebsiteService $websiteService
    ) {
    }

    /**
     * --------------------------------------------------------------------------
     * Step 1 - Website Type
     * --------------------------------------------------------------------------
     */
    public function create(): View
    {
        return view('websites.create');
    }

    /**
     * --------------------------------------------------------------------------
     * Create a new website draft
     * --------------------------------------------------------------------------
     */
    public function store(
        StoreWebsiteDraftRequest $request
    ): RedirectResponse {

        $website = $this->draftService->create();

        $this->draftService->save(
            $website,
            [
                'type' => $request->validated()['type'],
            ],
            1
        );

        return redirect()->route(
            'websites.theme',
            $website
        );
    }

    /**
     * --------------------------------------------------------------------------
     * Step 2 - Theme
     * --------------------------------------------------------------------------
     */
    public function theme(
        Website $website
    ): View {

        $themes = [

            [
                'id' => 'classic',
                'name' => 'Classic',
                'description' => 'Clean and timeless business design.',
            ],

            [
                'id' => 'modern',
                'name' => 'Modern',
                'description' => 'Modern corporate experience.',
            ],

            [
                'id' => 'premium',
                'name' => 'Premium',
                'description' => 'Premium high-converting layout.',
            ],

        ];

        return view(
            'websites.theme',
            [
                'website' => $website,
                'wizard' => $website->wizard_data ?? [],
                'themes' => $themes,
            ]
        );
    }
    /**
     * --------------------------------------------------------------------------
     * Step 3 - Website Information
     * --------------------------------------------------------------------------
     */
    public function information(
        Website $website
    ): View {

        return view(
            'websites.information',
            [
                'website' => $website,
                'wizard' => $website->wizard_data ?? [],
            ]
        );
    }

    /**
     * --------------------------------------------------------------------------
     * Step 4 - Plan
     * --------------------------------------------------------------------------
     */
    public function plan(
        Website $website
    ): View {

        $plans = [

            [
                'id' => 1,
                'name' => 'Starter',
                'price' => '₦2,500 / month',
                'description' => 'Perfect for personal brands and small businesses.',
                'features' => [
                    '1 Website',
                    'Free SSL',
                    'Free Subdomain',
                    'CRM Included',
                    'AI Assistant',
                    '5 GB Storage',
                    '1 Team Member',
                ],
            ],

            [
                'id' => 2,
                'name' => 'Professional',
                'price' => '₦6,000 / month',
                'description' => 'Ideal for growing businesses.',
                'popular' => true,
                'features' => [
                    'Everything in Starter',
                    'Custom Domain',
                    '20 GB Storage',
                    '5 Team Members',
                    'Marketing Suite',
                    'Payment Gateway',
                    'Advanced CRM',
                ],
            ],

            [
                'id' => 3,
                'name' => 'Business',
                'price' => '₦15,000 / month',
                'description' => 'Built for companies with advanced needs.',
                'features' => [
                    'Unlimited Pages',
                    'Unlimited Products',
                    'Unlimited Team',
                    'AI Credits',
                    'POS',
                    'Marketplace',
                    'Priority Support',
                ],
            ],

        ];

        return view(
            'websites.plan',
            [
                'website' => $website,
                'wizard' => $website->wizard_data ?? [],
                'plans' => $plans,
            ]
        );
    }

   /**
 * --------------------------------------------------------------------------
 * Step 5 - Website Address
 * --------------------------------------------------------------------------
 */
public function domain(
    Website $website
): View {

    return view(
        'websites.domain',
        [
            'website' => $website,
            'wizard' => $website->wizard_data ?? [],
        ]
    );

}

/**
 * --------------------------------------------------------------------------
 * Step 6 - Business Address
 * --------------------------------------------------------------------------
 */
public function address(
    Website $website
): View {

    return view(
        'websites.address',
        [
            'website' => $website,
            'wizard' => $website->wizard_data ?? [],
        ]
    );

}

/**
 * --------------------------------------------------------------------------
 * Step 7 - Website Administrator
 * --------------------------------------------------------------------------
 */
public function administrator(
    Website $website
): View {

    return view(
        'websites.administrator',
        [
            'website' => $website,
            'wizard' => $website->wizard_data ?? [],
        ]
    );

}

/**
 * --------------------------------------------------------------------------
 * Step 8 - Review
 * --------------------------------------------------------------------------
 */
public function review(
    Website $website
): View {

    return view(
        'websites.review',
        [
            'website' => $website,
            'wizard' => $website->wizard_data ?? [],
        ]
    );

}

/**
 * --------------------------------------------------------------------------
 * Deploy Website
 * --------------------------------------------------------------------------
 */
public function deploy(
    Request $request,
    Website $website
)
{
    /*
    |--------------------------------------------------------------------------
    | Final wizard data merge
    |--------------------------------------------------------------------------
    */

    if (!empty($request->all())) {

        $this->draftService->save(
            $website,
            $request->except([
                '_token',
            ]),
            8
        );

        $website = $website->fresh();
    }

    /*
    |--------------------------------------------------------------------------
    | Mark provisioning
    |--------------------------------------------------------------------------
    */

    $website->markProvisioning();

    /*
    |--------------------------------------------------------------------------
    | Deploy website
    |--------------------------------------------------------------------------
    */

    return $this->websiteService->create(
        array_merge(
            $website->wizard_data ?? [],
            [
                'website_id' => $website->id,
            ]
        )
    );
}

}
