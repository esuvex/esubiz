<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreWebsiteDraftRequest;
use App\Models\Website;
use App\Models\WebsiteType;
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
        $types = WebsiteType::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        return view('websites.create', compact('types'));
    }

    /**
     * --------------------------------------------------------------------------
     * Create Website Draft
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
            'websites.information',
            $website
        );
    }

    /**
     * --------------------------------------------------------------------------
     * Continue Existing Draft
     * --------------------------------------------------------------------------
     */
    public function continue(
        Website $website
    ): RedirectResponse {

        $routes = [
            1 => 'websites.information',
            2 => 'websites.plan',
            3 => 'websites.review',
            4 => 'websites.review',
            5 => 'websites.review',
        ];

        $currentStep = (int) ($website->current_step ?? 1);

        return redirect()->route(
            $routes[$currentStep] ?? 'websites.information',
            $website
        );
    }

    /**
     * --------------------------------------------------------------------------
     * Step 2 - Theme
     * --------------------------------------------------------------------------
     */
    public function theme(
        Request $request,
        Website $website
    ): View {

        if ($request->filled('theme')) {
            $this->draftService->save(
                $website,
                $request->except('_token'),
                2
            );

            $website = $website->fresh();
        }

        $themes = \App\Models\CatalogProduct::query()
        ->where('product_type', 'theme')
        ->where('is_active', true)
        ->whereIn('audience', ['saas', 'both'])
        ->orderBy('name')
        ->get();

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
     * Check Subdomain Availability
     * --------------------------------------------------------------------------
     */
    public function checkSubdomain(
        Request $request
    ): \Illuminate\Http\JsonResponse {

        $subdomain = \Illuminate\Support\Str::slug(
            $request->query('subdomain', '')
        );

        if ($subdomain === '') {
            return response()->json([
                'available' => false,
                'message' => 'Enter a subdomain name.',
            ]);
        }

        $exists = Website::where(
            'subdomain',
            $subdomain
        )->exists();

        return response()->json([
            'available' => !$exists,
            'subdomain' => $subdomain,
            'message' => $exists
                ? 'This subdomain is already taken.'
                : 'This subdomain is available.',
        ]);
    }

    /**
     * --------------------------------------------------------------------------
     * Step 2 - Website Details
     * --------------------------------------------------------------------------
     */
    public function information(
        Request $request,
        Website $website
    ): View|RedirectResponse {

        if ($request->isMethod('post')) {

            $data = $request->except([
                '_token',
                'password_confirmation',
            ]);

            $name = trim($data['name'] ?? '');

            if ($name === '') {
                return back()
                    ->withInput()
                    ->withErrors([
                        'name' => 'Please enter a website name.',
                    ]);
            }

            $data['name'] = $name;

            /*
            |--------------------------------------------------------------------------
            | Keep the central website identity synchronized with the wizard.
            |--------------------------------------------------------------------------
            */

            $website->update([
                'name' => $name,
            ]);

            $subdomain = \Illuminate\Support\Str::slug(
                $data['subdomain'] ?? \Illuminate\Support\Str::slug($name)
            );

            if ($subdomain === '') {
                return back()
                    ->withInput()
                    ->withErrors([
                        'subdomain' => 'Please enter a subdomain name.',
                    ]);
            }

            $taken = Website::query()
                ->where('subdomain', $subdomain)
                ->where('id', '!=', $website->id)
                ->exists();

            if ($taken) {
                return back()
                    ->withInput()
                    ->withErrors([
                        'subdomain' => 'This subdomain is already taken. Please choose another name.',
                    ]);
            }

            $data['subdomain'] = $subdomain;

            $this->draftService->save(
                $website,
                $data,
                2
            );

            return redirect()->route(
                'websites.plan',
                $website
            );
        }

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
     * Step 3 - Plan
     * --------------------------------------------------------------------------
     */
    public function plan(
        Request $request,
        Website $website
    ): View {

        if ($request->all()) {
            $this->draftService->save(
                $website,
                $request->except('_token'),
                3
            );

            $website = $website->fresh();
        }

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
        Request $request,
        Website $website
    ): View {

        if ($request->all()) {
            $this->draftService->save(
                $website,
                $request->except('_token'),
                5
            );

            $website = $website->fresh();
        }

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
        Request $request,
        Website $website
    ): View {

        if ($request->all()) {
            $this->draftService->save(
                $website,
                $request->except('_token'),
                6
            );

            $website = $website->fresh();
        }

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
        Request $request,
        Website $website
    ): View {

        if ($request->all()) {
            $this->draftService->save(
                $website,
                $request->except('_token'),
                7
            );

            $website = $website->fresh();
        }

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
        Request $request,
        Website $website
    ): View {

        if ($request->all()) {
            $this->draftService->save(
                $website,
                $request->except('_token'),
                4
            );

            $website = $website->fresh();
        }

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
     * Step 5 - Deploy
     * --------------------------------------------------------------------------
     */
    public function deploy(
        Request $request,
        Website $website
    ) {
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
                5
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
        | Launch Website
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
