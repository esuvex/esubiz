<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Website;
use App\Services\Website\WebsiteTenantDatabaseService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class WebsiteManagementController extends Controller
{
    public function __construct(
        protected WebsiteTenantDatabaseService $tenantDatabaseService
    ) {
    }

    /**
     * Show website management settings.
     */
    public function edit(Website $website): View
    {
        abort_unless(
            $website->owner_id === auth()->id(),
            403
        );

        return view('user.websites.edit', [
            'website' => $website,
        ]);
    }

    /**
     * Update owner-controlled website settings.
     *
     * Each section on the Edit Website page is saved independently.
     */
    public function update(
        Request $request,
        Website $website
    ): RedirectResponse {
        abort_unless(
            $website->owner_id === auth()->id(),
            403
        );

        /*
        |--------------------------------------------------------------------------
        | Website Name
        |--------------------------------------------------------------------------
        */

        if ($request->has('name')) {

            $validated = $request->validate([
                'name' => [
                    'required',
                    'string',
                    'max:255',
                ],
            ]);

            $name = trim($validated['name']);

            /*
            |--------------------------------------------------------------------------
            | Update Central Website
            |--------------------------------------------------------------------------
            */

            $wizardData = $website->wizard_data ?? [];

            $wizardData['name'] = $name;

            $website->update([
                'name' => $name,
                'wizard_data' => $wizardData,
            ]);

            /*
            |--------------------------------------------------------------------------
            | Synchronize Tenant Website Name
            |--------------------------------------------------------------------------
            |
            | Use the existing Esubiz tenant database service.
            | This preserves the existing tenant database credentials and
            | architecture.
            |
            */

            try {

                $this->tenantDatabaseService->connect($website);

                $tenantDb = $this->tenantDatabaseService->connection();

                /*
                |--------------------------------------------------------------------------
                | Site Settings
                |--------------------------------------------------------------------------
                */

                if (
                    $tenantDb->getSchemaBuilder()
                        ->hasTable('site_settings')
                ) {
                    $tenantDb->table('site_settings')->updateOrInsert(
                        [
                            'key' => 'website_name',
                        ],
                        [
                            'value' => $name,
                            'updated_at' => now(),
                            'created_at' => now(),
                        ]
                    );
                }

                /*
                |--------------------------------------------------------------------------
                | Homepage Title
                |--------------------------------------------------------------------------
                */

                if (
                    $tenantDb->getSchemaBuilder()
                        ->hasTable('pages')
                ) {
                    $tenantDb->table('pages')
                        ->where('is_homepage', true)
                        ->update([
                            'title' => $name,
                            'updated_at' => now(),
                        ]);
                }

            } finally {

                $this->tenantDatabaseService->disconnect();

            }

            return redirect()
                ->route('user.websites.edit', $website)
                ->with(
                    'success',
                    'Website name updated successfully.'
                );
        }

        /*
        |--------------------------------------------------------------------------
        | Website Availability
        |--------------------------------------------------------------------------
        */

        if ($request->has('user_enabled')) {

            $validated = $request->validate([
                'user_enabled' => [
                    'required',
                    'boolean',
                ],
            ]);

            $website->update([
                'user_enabled' => (bool) $validated['user_enabled'],
            ]);

            return redirect()
                ->route('user.websites.edit', $website)
                ->with(
                    'success',
                    'Website availability updated successfully.'
                );
        }

        return redirect()
            ->route('user.websites.edit', $website);
    }
}
