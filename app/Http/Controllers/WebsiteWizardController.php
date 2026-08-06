<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class WebsiteWizardController extends Controller
{
    /**
     * Step 1 - Website Type
     */
    public function create(): View
    {
        return view('websites.create');
    }

    /**
     * Step 2 - Theme
     */
    public function theme(Request $request): View
    {
        $website = $request->all();

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

        return view('websites.theme', compact(
            'website',
            'themes'
        ));
    }

    /**
     * Step 3 - Website Information
     */
    public function information(Request $request): View
    {
        return view('websites.information', [
            'website' => $request->all(),
        ]);
    }

    /**
     * Step 4 - Plan
     */
    public function plan(Request $request): View
    {
        $website = $request->all();

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

        return view('websites.plan', compact(
            'website',
            'plans'
        ));
    }

    /**
     * Step 5 - Website Address
     */
    public function address(Request $request): View
    {
        return view('websites.address', [
            'website' => $request->all(),
        ]);
    }

    /**
     * Step 6 - Review
     */
    public function review(Request $request): View
    {
        return view('websites.review', [
            'website' => $request->all(),
        ]);
    }

    /**
     * Step 7 - Deploy
     */
    public function deploy(Request $request)
    {
        return app(\App\Services\WebsiteService::class)
            ->create($request->all());
    }
}
