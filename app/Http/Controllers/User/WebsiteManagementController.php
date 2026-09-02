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

        $website->load([
            'owner',
            'developer',
            'plan',
            'workspace',
        ]);

        $entitlements = \Illuminate\Support\Facades\DB::table(
            'product_entitlements'
        )
            ->where('website_id', $website->id)
            ->whereNull('deleted_at')
            ->orderByDesc('created_at')
            ->get();

        $plans = \App\Models\Plan::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('user.websites.edit', [
            'website' => $website,
            'managementMode' => 'owner',
            'entitlements' => $entitlements,
            'plans' => $plans,
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

        if ($request->input('section') === 'identity' || ($request->has('name') && !$request->has('section'))) {

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
        | ESUBIZ_WEBSITE_MANAGEMENT_COMPLETE_UI_V1
        | Website Administrator Credentials
        |--------------------------------------------------------------------------
        */

        if ($request->input('section') === 'credentials') {

            $validated = $request->validate([
                'admin_name' => [
                    'nullable',
                    'string',
                    'max:255',
                ],
                'admin_email' => [
                    'required',
                    'email',
                    'max:255',
                ],
                'admin_password' => [
                    'nullable',
                    'string',
                    'min:8',
                    'max:255',
                ],
            ]);

            /*
             * ESUBIZ_CENTRAL_CORE_ADMIN_SYNC_V1
             *
             * Preserve the previous Central administrator email
             * so an email change updates the same Core user row.
             */
            $previousAdminEmail =
                strtolower(
                    trim(
                        (string) (
                            $website->admin_email
                            ?? ''
                        )
                    )
                );

            $updates = [
                'admin_name' =>
                    trim(
                        (string) (
                            $validated['admin_name']
                            ?? ''
                        )
                    ),

                'admin_email' =>
                    strtolower(
                        trim(
                            $validated['admin_email']
                        )
                    ),
            ];

            if (
                !empty(
                    $validated['admin_password']
                )
            ) {
                $updates['admin_password'] =
                    \Illuminate\Support\Facades\Hash::make(
                        $validated['admin_password']
                    );
            }

            $website->update(
                $updates
            );

            /*
             * Keep the SaaS Core administrator synchronized
             * with the Central Website administrator record.
             */
            try {
                app(
                    \App\Services\Website\CoreWebsiteAdministratorSyncService::class
                )->sync(
                    $website,
                    $previousAdminEmail
                );
            } catch (\Throwable $coreAdminSyncError) {
                report($coreAdminSyncError);

                return redirect()
                    ->route(
                        'user.websites.edit',
                        $website
                    )
                    ->withErrors([
                        'credentials' =>
                            'Central administrator credentials were updated, '
                            . 'but the SaaS Core administrator could not be '
                            . 'synchronized. Please retry or inspect the '
                            . 'application log.',
                    ]);
            }

            return redirect()
                ->route(
                    'user.websites.edit',
                    $website
                )
                ->with(
                    'success',
                    'Website login credentials updated and '
                    . 'synchronized with Core.'
                );
        }


        /*
        |--------------------------------------------------------------------------
        | Website Availability
        |--------------------------------------------------------------------------
        */

        if ($request->input('section') === 'availability' || ($request->has('user_enabled') && !$request->has('section'))) {

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
