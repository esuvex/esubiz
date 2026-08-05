<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
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
     * Step 2 - Template Selection
     */
    public function theme(Request $request): View
    {
        $website = [
            'type' => $request->input('type'),
        ];

        $themes = [

            [
                'id' => 'classic',
                'name' => 'Classic',
                'description' => 'Clean and timeless layout.',
            ],

            [
                'id' => 'modern',
                'name' => 'Modern',
                'description' => 'Contemporary business layout.',
            ],

            [
                'id' => 'premium',
                'name' => 'Premium',
                'description' => 'Elegant premium layout.',
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
     * Step 4 - Plan Selection
     */
    public function plan(Request $request): View
    {
        $plans = [

            [
                'id' => 1,
                'name' => 'Starter',
                'price' => '₦2,500/month',
            ],

            [
                'id' => 2,
                'name' => 'Professional',
                'price' => '₦6,000/month',
            ],

            [
                'id' => 3,
                'name' => 'Business',
                'price' => '₦15,000/month',
            ],

        ];

        return view('websites.plan', [
            'website' => $request->all(),
            'plans' => $plans,
        ]);
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
     * Step 7 - Provision
     */
    public function deploy(Request $request)
    {
        return app(\App\Services\WebsiteService::class)
            ->create($request->all());
    }
}
