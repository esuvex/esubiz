<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreWebsiteDraftRequest;
use App\Models\Website;
use App\Models\WebsiteType;
use App\Services\WebsiteDraftService;
use App\Services\WebsiteService;
use App\Services\Website\Deployment\WebsiteTypeDeploymentProfileService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
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
            ->where('show_in_user_wizard', true)
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
    ): RedirectResponse|\Illuminate\Http\JsonResponse {

        $website = $this->draftService->create();

        $this->draftService->save(
            $website,
            [
                'type' => $request->validated()['type'],
            ],
            1
        );

        /*
         * ESUBIZ_FAST_WEBSITE_TYPE_AJAX_V24
         *
         * AJAX selection must not follow/render Step 2 inside fetch().
         * Return only the authoritative next URL so the browser performs
         * a single navigation to Website Information.
         */
        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'next_url' => route(
                    'websites.information',
                    $website
                ),
            ]);
        }

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

        /*
         * ESUBIZ_CONDITIONAL_THEME_RESUME_V48
         *
         * Step 2 must always hand resume control to the Theme route.
         * The Theme route is the single authority for whether Theme
         * selection is visible:
         *
         * - visible => render conditional Theme Step 3
         * - hidden  => silently persist Admin default and continue Plan
         *
         * This prevents resumed Website Wizards from bypassing the
         * Website Type deployment-profile Theme configuration.
         */
        $routes = [
            1 => 'websites.information',
            2 => 'websites.theme',
            3 => 'websites.plan',
            4 => 'websites.review',
            5 => 'websites.review',
            6 => 'websites.review',
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
        Website $website,
        WebsiteTypeDeploymentProfileService $deploymentProfiles
    ): View|RedirectResponse|\Illuminate\Http\JsonResponse {

        /*
         * ESUBIZ_DETERMINISTIC_THEME_STEP_V34
         *
         * Website Type SaaS deployment configuration is the authority.
         *
         * GET:
         * - visible Theme selection => always render Theme Step 3
         * - hidden Theme selection  => silently save default => Plan
         *
         * POST:
         * - validate selected Theme against assigned Website Type Themes
         * - save Theme
         * - continue to Plan
         *
         * Generic for every current and future Website Type / Theme.
         */

        $wizard = $website->wizard_data ?? [];

        $websiteTypeSlug = trim(
            (string) data_get(
                $wizard,
                'type',
                ''
            )
        );

        $websiteType = WebsiteType::query()
            ->where(
                'slug',
                $websiteTypeSlug
            )
            ->where(
                'is_active',
                true
            )
            ->where(
                'show_in_user_wizard',
                true
            )
            ->firstOrFail();

        $profile = $deploymentProfiles->resolve(
            $websiteType,
            'saas'
        );

        $assignedThemes = collect(
            $profile['themes'] ?? []
        );

        $rawThemeVisibility = data_get(
            $profile,
            'configuration.show_theme_selection'
        );

        /*
         * Backward compatibility:
         * missing/null means visible.
         * Only an explicit false-like Admin setting hides the step.
         */
        $showThemeSelection = filter_var(
            (
                $rawThemeVisibility
                ?? true
            ),
            FILTER_VALIDATE_BOOLEAN
        );

        $defaultTheme = trim(
            (string) data_get(
                $profile,
                'default_theme',
                ''
            )
        );

        /*
         * --------------------------------------------------------------
         * POST - user selected a visible Theme
         * --------------------------------------------------------------
         */
        if ($request->isMethod('post')) {

            abort_unless(
                $showThemeSelection,
                422,
                'Theme selection is disabled for this Website Type.'
            );

            $selectedTheme = trim(
                (string) $request->input(
                    'theme',
                    ''
                )
            );

            abort_if(
                $selectedTheme === '',
                422,
                'Please select a Theme.'
            );

            $allowed = $assignedThemes->contains(
                fn (array $theme) =>
                    (string) data_get(
                        $theme,
                        'slug'
                    )
                    === $selectedTheme
            );

            abort_unless(
                $allowed,
                422,
                'The selected Theme is not available for this Website Type.'
            );

            /*
             * Visible Theme is logical Step 3.
             */
            $this->draftService->save(
                $website,
                [
                    'theme' => $selectedTheme,
                ],
                3
            );

            $website = $website->fresh();

            if (
                $request->expectsJson()
                || $request->ajax()
            ) {
                return response()->json([
                    'success' => true,
                    'next_url' => route(
                        'websites.plan',
                        $website
                    ),
                ]);
            }

            return redirect()->route(
                'websites.plan',
                $website
            );
        }

        /*
         * --------------------------------------------------------------
         * GET - hidden Theme selection
         * --------------------------------------------------------------
         */
        if (!$showThemeSelection) {

            abort_if(
                $defaultTheme === '',
                422,
                'This Website Type requires a default Theme before Theme selection can be hidden.'
            );

            $defaultAllowed = $assignedThemes->contains(
                fn (array $theme) =>
                    (string) data_get(
                        $theme,
                        'slug'
                    )
                    === $defaultTheme
            );

            abort_unless(
                $defaultAllowed,
                422,
                'The configured default Theme is not assigned to this Website Type.'
            );

            /*
             * No visible logical Theme step exists when hidden,
             * so keep the draft at logical Step 2.
             */
            $this->draftService->save(
                $website,
                [
                    'theme' => $defaultTheme,
                ],
                2
            );

            return redirect()->route(
                'websites.plan',
                $website
            );
        }

        /*
         * --------------------------------------------------------------
         * GET - visible Theme selection
         *
         * IMPORTANT:
         * A visible profile ALWAYS renders Step 3 here.
         * Existing saved Theme data must never cause this page to skip.
         * --------------------------------------------------------------
         */
        $themeIds = $assignedThemes
            ->pluck('catalog_product_id')
            ->filter()
            ->map(
                fn ($id) => (int) $id
            )
            ->values();

        $themes = \App\Models\CatalogProduct::query()
            ->whereIn(
                'id',
                $themeIds
            )
            ->where(
                'product_type',
                'theme'
            )
            ->where(
                'is_active',
                true
            )
            ->whereIn(
                'audience',
                [
                    'saas',
                    'both',
                ]
            )
            ->orderBy('name')
            ->get();

        abort_if(
            $themes->isEmpty(),
            422,
            'No active SaaS Theme is assigned to this Website Type.'
        );

        /*
         * ESUBIZ_WIZARD_THEME_PREVIEW_DATA_V39
         *
         * CatalogProduct remains the wizard Theme identity.
         * Native ThemePackage supplies the existing safe Marketplace
         * preview endpoint.
         *
         * No package filesystem path is exposed to the browser.
         */
        $nativeThemePackages = \Illuminate\Support\Facades\DB::table(
                'theme_packages'
            )
            ->whereIn(
                'catalog_product_id',
                $themes->pluck('id')
            )
            ->get()
            ->keyBy(
                fn ($package) =>
                    (int) $package->catalog_product_id
            );

        $themes = $themes->map(
            function ($theme) use ($nativeThemePackages) {

                $package = $nativeThemePackages->get(
                    (int) $theme->id
                );

                $theme->wizard_preview_url =
                    $package
                        ? route(
                            'marketplace.themes.preview',
                            [
                                'themePackageId' =>
                                    $package->id,
                            ]
                        )
                        : null;

                return $theme;
            }
        );

        return view(
            'websites.theme',
            [
                'website' => $website,
                'wizard' => $wizard,
                'themes' => $themes,
                'defaultTheme' => $defaultTheme,
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

            /*
             * ESUBIZ_WEBSITE_INFORMATION_IDENTITY_V5
             *
             * The primary website Administrator identity is persisted
             * before Core provisioning.
             *
             * Plaintext passwords never enter wizard_data.
             */
            $validated = $request->validate([
                'name' => [
                    'required',
                    'string',
                    'max:255',
                ],

                'subdomain' => [
                    'required',
                    'string',
                    'max:255',
                ],

                'domain' => [
                    'nullable',
                    'string',
                    'max:255',
                ],

                'admin_name' => [
                    'required',
                    'string',
                    'max:255',
                ],

                'admin_email' => [
                    'required',
                    'email',
                    'max:255',
                ],

                'admin_phone' => [
                    'nullable',
                    'string',
                    'max:50',
                    'regex:/^[0-9]+$/',
                ],

                'password' => [
                    'required',
                    'string',
                    'min:8',
                    'confirmed',
                ],

                'upload_branding' => [
                    'required',
                    'in:no,yes',
                ],

                'website_logo' => [
                    'nullable',
                    'file',
                    'mimes:jpg,jpeg,png,webp,svg',
                    'max:3072',
                ],

                'website_favicon' => [
                    'nullable',
                    'file',
                    'mimes:png,ico,jpg,jpeg,webp',
                    'max:1024',
                ],
            ]);

            $name = trim(
                (string) $validated['name']
            );

            $subdomain =
                \Illuminate\Support\Str::slug(
                    (string) $validated['subdomain']
                );

            if ($subdomain === '') {
                return back()
                    ->withInput()
                    ->withErrors([
                        'subdomain' =>
                            'Please enter a subdomain name.',
                    ]);
            }

            $taken = Website::query()
                ->where(
                    'subdomain',
                    $subdomain
                )
                ->where(
                    'id',
                    '!=',
                    $website->id
                )
                ->exists();

            if ($taken) {
                return back()
                    ->withInput()
                    ->withErrors([
                        'subdomain' =>
                            'This subdomain is already taken. Please choose another name.',
                    ]);
            }

            $adminName = trim(
                (string) $validated['admin_name']
            );

            $adminEmail = strtolower(
                trim(
                    (string) $validated['admin_email']
                )
            );

            $adminCountryCode =
                strtoupper(
                    trim(
                        (string)
                        $validated['admin_country_code']
                    )
                );

            $adminPhone =
                isset($validated['admin_phone'])
                    ? trim(
                        (string)
                        $validated['admin_phone']
                    )
                    : null;

            /*
             * Generate the password hash exactly once.
             *
             * TenantCoreInitializer copies this exact hash unchanged
             * into site_users.password.
             */
            /*
             * ESUBIZ_SAAS_MAILBOX_PASSWORD_HANDOFF_V3
             *
             * SaaS managed mail initially uses the same password entered
             * for the Website Administrator.
             *
             * The Website model encrypts this temporary credential at rest.
             * It is deliberately excluded from wizard_data and is cleared
             * after successful managed-mail provisioning.
             */
            $mailboxProvisioningPassword =
                (string) $validated['password'];

            $passwordHash = Hash::make(
                $validated['password']
            );

            $website->update([
                'name' =>
                    $name,

                'subdomain' =>
                    $subdomain,

                'domain' =>
                    $validated['domain']
                    ?? null,

                'admin_name' =>
                    $adminName,

                'admin_email' =>
                    $adminEmail,

                'admin_password' =>
                    $passwordHash,

                'mailbox_provisioning_password' =>
                    $mailboxProvisioningPassword,
            ]);

            /*
             * ESUBIZ_DEPLOYMENT_BRANDING_STAGING_V1
             *
             * Branding is website installation data, never Central
             * account/profile data.
             *
             * During the wizard it is held only in private temporary
             * deployment storage.
             */
            $brandingEnabled =
                (
                    $validated['upload_branding']
                    ?? 'no'
                ) === 'yes';

            $temporaryDirectory =
                'deployment-branding/'
                . (int) $website->id;

            if (!$brandingEnabled) {

                Storage::disk('local')
                    ->deleteDirectory(
                        $temporaryDirectory
                    );

            } else {

                if (
                    $request->hasFile(
                        'website_logo'
                    )
                ) {
                    foreach (
                        Storage::disk('local')
                            ->files(
                                $temporaryDirectory
                            )
                        as $existing
                    ) {
                        if (
                            str_starts_with(
                                strtolower(
                                    basename($existing)
                                ),
                                'logo.'
                            )
                        ) {
                            Storage::disk('local')
                                ->delete($existing);
                        }
                    }

                    $file =
                        $request->file(
                            'website_logo'
                        );

                    $file->storeAs(
                        $temporaryDirectory,
                        'logo.'
                        . strtolower(
                            $file
                                ->getClientOriginalExtension()
                        ),
                        'local'
                    );
                }

                if (
                    $request->hasFile(
                        'website_favicon'
                    )
                ) {
                    foreach (
                        Storage::disk('local')
                            ->files(
                                $temporaryDirectory
                            )
                        as $existing
                    ) {
                        if (
                            str_starts_with(
                                strtolower(
                                    basename($existing)
                                ),
                                'favicon.'
                            )
                        ) {
                            Storage::disk('local')
                                ->delete($existing);
                        }
                    }

                    $file =
                        $request->file(
                            'website_favicon'
                        );

                    $file->storeAs(
                        $temporaryDirectory,
                        'favicon.'
                        . strtolower(
                            $file
                                ->getClientOriginalExtension()
                        ),
                        'local'
                    );
                }
            }

            /*
             * Only safe deployment metadata enters wizard_data.
             */
            $data = [
                'name' =>
                    $name,

                'subdomain' =>
                    $subdomain,

                'domain' =>
                    $validated['domain']
                    ?? null,

                'admin_name' =>
                    $adminName,

                'admin_email' =>
                    $adminEmail,

                'admin_country_code' =>
                    $adminCountryCode,

                'admin_phone' =>
                    $adminPhone,

                'upload_branding' =>
                    $brandingEnabled
                        ? 'yes'
                        : 'no',
            ];

            $this->draftService->save(
                $website,
                $data,
                2
            );

            /*
             * ESUBIZ_LIVE_STEP2_THEME_HANDOFF_V38
             *
             * The live Website Information page contains the complete
             * logical Step 2:
             * Website Information + Domain + Address + Administrator.
             *
             * After Step 2, always hand control to the Theme route.
             *
             * theme() is the single authority:
             * - visible Theme selection => render Step 3
             * - hidden Theme selection  => apply Admin default => Plan
             */
            return redirect()->route(
                'websites.theme',
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
    ): View|RedirectResponse {

        if ($request->all()) {
            /*
             * ESUBIZ_USER_WIZARD_STEP2_DOMAIN_V26
             *
             * Domain remains an internal screen of Step 2.
             * Save here, then continue to Address.
             */
            $this->draftService->save(
                $website,
                $request->except('_token'),
                2
            );

            return redirect()->route(
                'websites.address',
                $website
            );
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
    ): View|RedirectResponse {

        if ($request->all()) {
            /*
             * ESUBIZ_USER_WIZARD_STEP2_ADDRESS_V26
             *
             * Address remains an internal screen of Step 2.
             * Save here, then continue to Administrator.
             */
            $this->draftService->save(
                $website,
                $request->except('_token'),
                2
            );

            return redirect()->route(
                'websites.administrator',
                $website
            );
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
    ): View|RedirectResponse {

        /*
         * ESUBIZ_ADMINISTRATOR_THEME_HANDOFF_V33
         *
         * Administrator is the final screen inside logical Step 2.
         *
         * POST always saves Administrator data and hands control to
         * the Theme route.
         *
         * The Theme controller is the single authority for deciding:
         *
         * - visible Theme selection -> render logical Step 3
         * - hidden Theme selection  -> apply Admin default -> Plan
         *
         * This keeps the behavior generic for every current and future
         * Website Type and removes duplicate Theme visibility logic.
         */
        if ($request->isMethod('post')) {
            $this->draftService->save(
                $website,
                $request->except('_token'),
                2
            );

            return redirect()->route(
                'websites.theme',
                $website
            );
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
         * ESUBIZ_DEPLOYMENT_BRANDING_SOURCE_V5
         *
         * Branding was collected and staged during
         * Website Information.
         */


        /*
        |--------------------------------------------------------------------------
        | Final wizard data merge
        |--------------------------------------------------------------------------
        */

        $draftInput = $request->except([
            '_token',
            'website_logo',
            'website_favicon',
            'password',
            'password_confirmation',
        ]);

        if (!empty($draftInput)) {
            $this->draftService->save(
                $website,
                $draftInput,
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
         * ESUBIZ_DEPLOYMENT_PROGRESS_STAGE_01_V1
         *
         * Fixed backend milestone:
         * 1% = deployment request accepted.
         */
        $website->forceFill([
            'deployment_progress' => 1,
        ])->save();

        /*
         * Release PHP session lock so the progress endpoint can
         * continue responding while deployment is running.
         */
        if ($request->hasSession()) {
            $request->session()->save();

            if (function_exists('session_write_close')) {
                @session_write_close();
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Launch Website
        |--------------------------------------------------------------------------
        */

        try {
            $deployedWebsite = $this->websiteService->create(
                array_merge(
                    $website->wizard_data ?? [],
                    [
                        'website_id' => $website->id,
                    ]
                )
            );

            /*
             * ESUBIZ_DEPLOYMENT_INTEGRITY_GUARD_V2
             *
             * Deployment cannot reach 100% until the actual Core
             * Administrator and published Core pages exist.
             */
            $tenantDatabaseService = app(
                \App\Services\Website\WebsiteTenantDatabaseService::class
            );

            $tenantDatabaseService
                ->connect(
                    $deployedWebsite
                );

            try {
                $tenantDb =
                    $tenantDatabaseService
                        ->connection();

                $administratorExists =
                    $tenantDb
                        ->table(
                            'site_users as users'
                        )
                        ->join(
                            'site_user_roles as user_roles',
                            'user_roles.user_id',
                            '=',
                            'users.id'
                        )
                        ->join(
                            'site_roles as roles',
                            'roles.id',
                            '=',
                            'user_roles.role_id'
                        )
                        ->whereRaw(
                            'LOWER(users.email) = ?',
                            [
                                strtolower(
                                    trim(
                                        (string)
                                        $deployedWebsite
                                            ->admin_email
                                    )
                                ),
                            ]
                        )
                        ->where(
                            'roles.slug',
                            'administrator'
                        )
                        ->exists();

                if (!$administratorExists) {
                    throw new \RuntimeException(
                        'Website Administrator account was not installed.'
                    );
                }

                $publishedPageCount =
                    $tenantDb
                        ->table('pages')
                        ->where(
                            'status',
                            'published'
                        )
                        ->count();

                if ($publishedPageCount < 6) {
                    throw new \RuntimeException(
                        'Website default pages were not installed.'
                    );
                }

                /*
                 * Branding is promoted only after Core passes
                 * installation verification.
                 */
                $brandingEnabled =
                    (
                        $deployedWebsite
                            ->wizard_data[
                                'upload_branding'
                            ]
                        ?? 'no'
                    ) === 'yes';

                if ($brandingEnabled) {

                    $temporaryDirectory =
                        'deployment-branding/'
                        . (int)
                            $deployedWebsite->id;

                    $temporaryFiles =
                        Storage::disk('local')
                            ->files(
                                $temporaryDirectory
                            );

                    $logoTemporaryPath =
                        null;

                    $faviconTemporaryPath =
                        null;

                    foreach (
                        $temporaryFiles
                        as $temporaryFile
                    ) {
                        $basename =
                            strtolower(
                                basename(
                                    $temporaryFile
                                )
                            );

                        if (
                            str_starts_with(
                                $basename,
                                'logo.'
                            )
                        ) {
                            $logoTemporaryPath =
                                $temporaryFile;
                        }

                        if (
                            str_starts_with(
                                $basename,
                                'favicon.'
                            )
                        ) {
                            $faviconTemporaryPath =
                                $temporaryFile;
                        }
                    }

                    if ($logoTemporaryPath) {

                        $extension =
                            strtolower(
                                pathinfo(
                                    $logoTemporaryPath,
                                    PATHINFO_EXTENSION
                                )
                            );

                        $logoPath =
                            'tenant-websites/'
                            . (int)
                                $deployedWebsite->id
                            . '/media/branding/logo.'
                            . $extension;

                        // ESUBIZ_CORE_CANONICAL_BRANDING_MEDIA_V1
                        Storage::disk('local')
                            ->put(
                                $logoPath,
                                Storage::disk('local')
                                    ->get(
                                        $logoTemporaryPath
                                    )
                            );

                        foreach (
                            [
                                'site_logo_path',
                                'theme.corporate.logo_path',
                            ]
                            as $key
                        ) {
                            $tenantDb
                                ->table(
                                    'site_settings'
                                )
                                ->updateOrInsert(
                                    [
                                        'key' =>
                                            $key,
                                    ],
                                    [
                                        'value' =>
                                            $logoPath,

                                        'updated_at' =>
                                            now(),
                                    ]
                                );
                        }
                    }

                    if ($faviconTemporaryPath) {

                        $extension =
                            strtolower(
                                pathinfo(
                                    $faviconTemporaryPath,
                                    PATHINFO_EXTENSION
                                )
                            );

                        $faviconPath =
                            'tenant-websites/'
                            . (int)
                                $deployedWebsite->id
                            . '/media/branding/favicon.'
                            . $extension;

                        Storage::disk('local')
                            ->put(
                                $faviconPath,
                                Storage::disk('local')
                                    ->get(
                                        $faviconTemporaryPath
                                    )
                            );

                        foreach (
                            [
                                'site_favicon_path',
                                'theme.corporate.favicon_path',
                            ]
                            as $key
                        ) {
                            $tenantDb
                                ->table(
                                    'site_settings'
                                )
                                ->updateOrInsert(
                                    [
                                        'key' =>
                                            $key,
                                    ],
                                    [
                                        'value' =>
                                            $faviconPath,

                                        'updated_at' =>
                                            now(),
                                    ]
                                );
                        }
                    }

                    Storage::disk('local')
                        ->deleteDirectory(
                            $temporaryDirectory
                        );
                }

            } finally {
                $tenantDatabaseService
                    ->disconnect();
            }

            /*
             * 100% = verified success only.
             */
            $deployedWebsite->forceFill([
                'status' =>
                    'active',

                'deployment_progress' =>
                    100,
            ])->save();

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'status' => 'active',
                    'progress' => 100,
                    /*
                     * ESUBIZ_CENTRAL_MANAGE_WEBSITE_HTTPS_V1
                     *
                     * Only force HTTPS for this trusted Central
                     * Manage Website entry URL.
                     */
                    'manage_url' => secure_url(
                        '/websites/'
                        . (int) $deployedWebsite->id
                        . '/dashboard'
                    ),
                    'dashboard_url' => route(
                        'user.dashboard'
                    ),
                ]);
            }

            return redirect()
                ->route('user.websites.index')
                ->with(
                    'deployment_success',
                    $deployedWebsite->id
                );

        } catch (\Throwable $e) {
            report($e);

            $website->refresh();

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'status' => 'failed',
                    'progress' => (int) (
                        $website->deployment_progress ?? 0
                    ),
                    'message' =>
                        'Website creation was not completed. Please try again.',
                ], 422);
            }

            return redirect()
                ->route('websites.review', $website)
                ->withInput()
                ->withErrors([
                    'deployment' =>
                        'Website creation was not completed. Please try again.',
                ]);
        }
    }

    /*
     * ESUBIZ_DEPLOYMENT_PROGRESS_ENDPOINT_V2
     */
    public function deploymentProgress(
        Request $request,
        Website $website
    ) {
        abort_unless(
            (int) $website->owner_id
                === (int) $request->user()->id,
            403
        );

        $website->refresh();

        $progress = (int) (
            $website->deployment_progress ?? 0
        );

        /*
         * ESUBIZ_DEPLOYMENT_PROGRESS_DEFAULT_TEXT_V1
         *
         * Percentages are fixed backend milestones.
         * Central Admin will later manage only the display wording
         * associated with these milestone percentages.
         */
        $stages = [
            1 => 'Starting website creation...',
            10 => 'Preparing your website...',
            45 => 'Setting up your website database...',
            60 => 'Installing Esubiz Core...',
            90 => 'Configuring your website...',
            98 => 'Running final deployment checks...',
            100 => 'Website created successfully.',
        ];

        $stageProgress = 1;

        foreach (array_keys($stages) as $milestone) {
            if ($progress >= $milestone) {
                $stageProgress = $milestone;
            }
        }

        return response()->json([
            'status' => (string) $website->status,
            'progress' => $progress,
            'stage' => $stages[$stageProgress],
            'stage_progress' => $stageProgress,
        ]);
    }
}
