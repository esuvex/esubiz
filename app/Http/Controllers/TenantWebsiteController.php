<?php

namespace App\Http\Controllers;

use App\Models\Website;
use App\Models\WebsiteTenant;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Spatie\Multitenancy\Models\Tenant;

class TenantWebsiteController extends Controller
{
    /**
     * Display the temporary default public website page.
     *
     * This is only a temporary interface until the actual website
     * type/template renderer is configured.
     */
    public function home(Request $request): View
    {
        /** @var WebsiteTenant|null $tenant */
        $tenant = Tenant::current();

        abort_unless(
            $tenant,
            404,
            'Website tenant not found.'
        );

        $website = Website::query()
            ->findOrFail($tenant->website_id);

        /*
        |--------------------------------------------------------------------------
        | Current Public Website URL
        |--------------------------------------------------------------------------
        |
        | Use the actual host the visitor is accessing.
        | This makes the temporary page work for both Esubiz subdomains
        | and future custom domains.
        |
        */

        $websiteUrl = $request->getScheme() . '://' . $request->getHost();

        return view('tenant.website', [
            'website' => $website,
            'websiteUrl' => $websiteUrl,
        ]);
    }
}
