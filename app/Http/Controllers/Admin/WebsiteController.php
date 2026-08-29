<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Website;
use App\Services\CentralApi\CentralWebsiteDetailService;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class WebsiteController extends Controller
{
    /**
     * Central Admin website registry.
     * ESUBIZ_ADMIN_WEBSITE_COMPACT_ACTION_MENU_V1
     *
     * This page lists both SaaS and off-server websites from the
     * canonical websites registry.
     */
    public function index(Request $request): View
    {
        $query = Website::query()
            ->with([
                'owner',
                'developer',
                'plan',
                'workspace',
            ]);

        /*
         * --------------------------------------------------------
         * Search
         * --------------------------------------------------------
         */
        $search = trim(
            (string) $request->query(
                'search',
                ''
            )
        );

        if ($search !== '') {
            $query->where(
                function ($query) use ($search) {
                    $query
                        ->where(
                            'name',
                            'like',
                            '%' . $search . '%'
                        )
                        ->orWhere(
                            'website_uuid',
                            'like',
                            '%' . $search . '%'
                        )
                        ->orWhere(
                            'registered_domain',
                            'like',
                            '%' . $search . '%'
                        )
                        ->orWhere(
                            'domain',
                            'like',
                            '%' . $search . '%'
                        )
                        ->orWhereHas(
                            'owner',
                            function ($ownerQuery) use ($search) {
                                $ownerQuery
                                    ->where(
                                        'name',
                                        'like',
                                        '%' . $search . '%'
                                    )
                                    ->orWhere(
                                        'email',
                                        'like',
                                        '%' . $search . '%'
                                    );
                            }
                        );
                }
            );
        }

        /*
         * --------------------------------------------------------
         * Deployment filter
         * --------------------------------------------------------
         */
        $deployment =
            trim(
                (string) $request->query(
                    'deployment',
                    ''
                )
            );

        if (
            in_array(
                $deployment,
                [
                    Website::DEPLOYMENT_SAAS,
                    Website::DEPLOYMENT_OFF_SERVER,
                ],
                true
            )
        ) {
            $query->where(
                'deployment_type',
                $deployment
            );
        }

        /*
         * --------------------------------------------------------
         * Registry status filter
         * --------------------------------------------------------
         */
        $registryStatus =
            trim(
                (string) $request->query(
                    'registry_status',
                    ''
                )
            );

        if ($registryStatus !== '') {
            $query->where(
                'registry_status',
                $registryStatus
            );
        }

        /*
         * --------------------------------------------------------
         * Website runtime status filter
         * --------------------------------------------------------
         */
        $status =
            trim(
                (string) $request->query(
                    'status',
                    ''
                )
            );

        if ($status !== '') {
            $query->where(
                'status',
                $status
            );
        }

        $websites =
            $query
                ->orderByDesc('id')
                ->paginate(10)
                ->withQueryString();

        return view(
            'admin.websites.index',
            [
                'websites' =>
                    $websites,

                'filters' => [
                    'search' =>
                        $search,

                    'deployment' =>
                        $deployment,

                    'registry_status' =>
                        $registryStatus,

                    'status' =>
                        $status,
                ],
            ]
        );
    }


    /**
     * Canonical Central management snapshot for one website.
     */
    public function show(
        Website $website,
        CentralWebsiteDetailService $detailService
    ): View {
        $website->load([
            'owner',
            'developer',
            'plan',
            'workspace',
        ]);

        $detail =
            $detailService->get(
                $website
            );

        return view(
            'admin.websites.show',
            [
                'website' =>
                    $website,

                'detail' =>
                    $detail,
            ]
        );
    }


    /**
     * ESUBIZ_SHARED_WEBSITE_MANAGEMENT_V1
     *
     * Central Admin website-management screen.
     */
    public function edit(Website $website): View
    {
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
            'managementMode' => 'admin',
            'entitlements' => $entitlements,
            'plans' => $plans,
        ]);
    }


    /**
     * Update Central Admin controlled website settings.
     */
    public function update(
        Request $request,
        Website $website
    ): RedirectResponse {
        /*
         * Website identity.
         */
        if ($request->input('section') === 'identity') {
            $validated = $request->validate([
                'name' => ['required', 'string', 'max:255'],
            ]);

            $name = trim($validated['name']);

            $wizardData = $website->wizard_data ?? [];
            $wizardData['name'] = $name;

            $website->update([
                'name' => $name,
                'wizard_data' => $wizardData,
            ]);

            /*
             * Synchronize the canonical tenant website name.
             */
            $tenantService = app(
                \App\Services\Website\WebsiteTenantDatabaseService::class
            );

            try {
                $tenantService->connect($website);
                $tenantDb = $tenantService->connection();

                if (
                    $tenantDb->getSchemaBuilder()
                        ->hasTable('site_settings')
                ) {
                    $tenantDb->table('site_settings')->updateOrInsert(
                        ['key' => 'website_name'],
                        [
                            'value' => $name,
                            'updated_at' => now(),
                            'created_at' => now(),
                        ]
                    );
                }

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
                $tenantService->disconnect();
            }

            return redirect()
                ->route('admin.websites.edit', $website)
                ->with(
                    'success',
                    'Website name updated successfully.'
                );
        }

        /*
         * Website administrator credentials.
         *
         * Central values remain the provisioning authority.
         * Tenant credential synchronization will be handled through
         * the same website-management page without exposing passwords.
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

            $updates = [
                'admin_name' =>
                    trim((string) ($validated['admin_name'] ?? '')),
                'admin_email' =>
                    strtolower(trim($validated['admin_email'])),
            ];

            if (!empty($validated['admin_password'])) {
                $updates['admin_password'] =
                    \Illuminate\Support\Facades\Hash::make(
                        $validated['admin_password']
                    );
            }

            $website->update($updates);

            return redirect()
                ->route('admin.websites.edit', $website)
                ->with(
                    'success',
                    'Website administrator credentials updated.'
                );
        }

        /*
         * Plan assignment.
         */
        if ($request->input('section') === 'plan') {
            $validated = $request->validate([
                'plan_id' => [
                    'nullable',
                    'integer',
                    'exists:plans,id',
                ],
            ]);

            $website->update([
                'plan_id' => $validated['plan_id'] ?? null,
            ]);

            return redirect()
                ->route('admin.websites.edit', $website)
                ->with(
                    'success',
                    'Website plan updated successfully.'
                );
        }

        /*
         * Central credit balances.
         */
        if ($request->input('section') === 'credits') {
            $validated = $request->validate([
                'ai_credits' => ['required', 'integer', 'min:0'],
                'sms_credits' => ['required', 'integer', 'min:0'],
                'email_credits' => ['required', 'integer', 'min:0'],
                'whatsapp_credits' => ['required', 'integer', 'min:0'],
            ]);

            $website->update($validated);

            return redirect()
                ->route('admin.websites.edit', $website)
                ->with(
                    'success',
                    'Website credit balances updated successfully.'
                );
        }

        return redirect()
            ->route('admin.websites.edit', $website);
    }


    /**
     * ESUBIZ_CENTRAL_ADMIN_TENANT_SUPPORT_LOGIN_V1
     *
     * Create a short-lived signed support handoff from Central Admin
     * into a SaaS tenant CMS. This does not expose or replace the
     * website owner's credentials.
     */
    public function loginToWebsite(
        Website $website
    ) {
        $admin = auth()->user();

        abort_unless(
            $admin,
            401
        );

        abort_unless(
            $website->status === 'active',
            403,
            'This website is not active.'
        );

        abort_unless(
            !empty($website->subdomain),
            422,
            'This website does not have a tenant subdomain.'
        );

        $expires = now()->addMinutes(2)->timestamp;
        $access = 'admin_support';

        $payload = implode('|', [
            $website->id,
            $admin->id,
            $expires,
            $access,
        ]);

        $signature = hash_hmac(
            'sha256',
            $payload,
            config('app.key')
        );

        $scheme =
            app()->environment('local')
                ? 'http'
                : 'https';

        $tenantUrl =
            $scheme
            . '://'
            . $website->subdomain
            . '.esubiz.com/admin'
            . '?sso=1'
            . '&website=' . urlencode((string) $website->id)
            . '&user=' . urlencode((string) $admin->id)
            . '&expires=' . urlencode((string) $expires)
            . '&access=' . urlencode($access)
            . '&signature=' . urlencode($signature);

        return redirect()->away($tenantUrl);
    }


    public function toggle(
        Website $website
    ) {
        $website->forceFill([
            'user_enabled' =>
                !(bool) $website->user_enabled,
        ])->save();

        return back()->with(
            'success',
            $website->user_enabled
                ? 'Website enabled successfully.'
                : 'Website disabled successfully.'
        );
    }


    public function destroy(
        Website $website,
        \App\Services\Website\WebsiteDeletionService $deletion
    ) {
        $name =
            $website->name;

        $deletion->delete(
            $website
        );

        return redirect()
            ->route(
                'admin.websites.index'
            )
            ->with(
                'success',
                "{$name} was permanently deleted."
            );
    }

}