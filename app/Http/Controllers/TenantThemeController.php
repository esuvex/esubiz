<?php

namespace App\Http\Controllers;

use App\Models\Website;
use App\Models\WebsiteTenant;
use App\Services\Website\WebsiteTenantDatabaseService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Spatie\Multitenancy\Models\Tenant;

class TenantThemeController extends Controller
{
    public function __construct(
        protected WebsiteTenantDatabaseService $tenantDatabaseService
    ) {
    }

    protected function website(): Website
    {
        /** @var WebsiteTenant|null $tenant */
        $tenant = Tenant::current();

        abort_unless(
            $tenant,
            404,
            'Website tenant not found.'
        );

        return Website::query()
            ->where('id', $tenant->website_id)
            ->where('status', 'active')
            ->firstOrFail();
    }

    public static function defaults(): array
    {
        return [
            /*
             * Brand / Header
             */
            'primary_color' => '#2563eb',
            'secondary_color' => '#0f172a',

            'logo_path' => '',
            'favicon_path' => '',

            'nav_home_label' => 'Home',
            'nav_about_label' => 'About',
            'nav_faq_label' => 'FAQs',
            'nav_contact_label' => 'Contact',
            'nav_login_label' => 'Login',
            'nav_register_label' => 'Register',

            /*
             * Hero
             */
            'hero_badge' => 'Welcome',
            'hero_title' => 'Helping you move forward.',
            'hero_subtitle' =>
                'Thoughtful solutions designed around people, quality and dependable service.',
            'hero_image_path' => '',

            'cta_label' => 'Get Started',
            'cta_url' => '/contact',

            'secondary_cta_label' => 'Learn More',
            'secondary_cta_url' => '/about',

            /*
             * Features
             */
            'features_badge' => 'What we offer',
            'features_title' =>
                'Everything starts with understanding what matters.',
            'features_subtitle' =>
                'Our approach combines clear thinking, practical execution and consistent support.',

            /*
             * ESUBIZ_BUSINESS_STRUCTURED_ITEMS_V1
             *
             * Business Features use the same structured item
             * capabilities as Basic Page Builder Cards.
             */
            'features_json' => '[{"enabled":true,"title":"Professional Service","text":"Reliable solutions delivered with care, clarity and attention to detail.","image_path":"","show_image":false,"icon":"","show_icon":false,"show_button":false,"button_label":"","button_url":"","button_url_active":false},{"enabled":true,"title":"Customer Focus","text":"We listen first and shape the experience around the people we serve.","image_path":"","show_image":false,"icon":"","show_icon":false,"show_button":false,"button_label":"","button_url":"","button_url_active":false},{"enabled":true,"title":"Long-term Value","text":"Our goal is not simply to deliver today, but to create value that lasts.","image_path":"","show_image":false,"icon":"","show_icon":false,"show_button":false,"button_label":"","button_url":"","button_url_active":false}]',

            /*
             * Statistics
             */
            'stats_badge' => 'Our approach',

            'stat_1_value' => '100%',
            'stat_1_label' => 'Customer Focus',

            'stat_2_value' => '24/7',
            'stat_2_label' => 'Digital Access',

            'stat_3_value' => '01',
            'stat_3_label' => 'Clear Standard',

            /*
             * About
             */
            'about_badge' => 'About us',
            'about_title' =>
                'Built on clarity, consistency and trust.',
            'about_text' =>
                'We believe good business is built by solving real problems well. We focus on practical solutions, meaningful relationships and experiences customers are happy to return to.',
            'about_image_path' => '',

            'about_cta_label' => 'Our Story',
            'about_cta_url' => '/about',

            /*
             * Testimonials
             */
            'testimonials_badge' => 'Testimonials',
            'testimonials_title' =>
                'What people say about us.',
            'testimonials_subtitle' =>
                'Real experiences from customers and clients we have served.',

            'testimonials_json' => '[{"enabled":true,"name":"Amaka N.","role":"Business Owner","text":"Professional, responsive and easy to work with from beginning to end.","photo_path":"","show_image":false,"icon":"","show_icon":false,"show_button":false,"button_label":"","button_url":"","button_url_active":false},{"enabled":true,"name":"David O.","role":"Client","text":"The experience was clear, efficient and thoughtfully handled.","photo_path":"","show_image":false,"icon":"","show_icon":false,"show_button":false,"button_label":"","button_url":"","button_url_active":false},{"enabled":true,"name":"Sarah K.","role":"Customer","text":"Strong communication and attention to detail made the process easy.","photo_path":"","show_image":false,"icon":"","show_icon":false,"show_button":false,"button_label":"","button_url":"","button_url_active":false},{"enabled":true,"name":"Michael A.","role":"Business Client","text":"A dependable team with a practical approach and consistent delivery.","photo_path":"","show_image":false,"icon":"","show_icon":false,"show_button":false,"button_label":"","button_url":"","button_url_active":false},{"enabled":true,"name":"Ifeoma C.","role":"Customer","text":"Everything felt organized, professional and focused on the right outcome.","photo_path":"","show_image":false,"icon":"","show_icon":false,"show_button":false,"button_label":"","button_url":"","button_url_active":false},{"enabled":true,"name":"Tunde B.","role":"Returning Client","text":"The quality of service gave us the confidence to work together again.","photo_path":"","show_image":false,"icon":"","show_icon":false,"show_button":false,"button_label":"","button_url":"","button_url_active":false}]',

            /*
             * Final CTA
             */
            'final_cta_badge' => "Let's talk",
            'final_cta_title' =>
                'Ready to work with us?',
            'final_cta_text' =>
                'Tell us what you need and let us start the conversation.',
            'final_cta_label' =>
                'Contact Us',
            'final_cta_url' =>
                '/contact',

            /*
             * Footer
             */
            'footer_logo_path' => '',
            'footer_background_path' => '',

            /*
             * Footer four-section layout.
             */
            'footer_brand_enabled' => '1',
            'footer_logo_enabled' => '1',
            'footer_text_enabled' => '1',

            'footer_contact_enabled' => '1',
            'footer_menu_1_enabled' => '1',
            'footer_menu_2_enabled' => '1',

            'footer_menu_1_title' => 'Quick Links',
            'footer_menu_2_title' => 'More',

            'footer_menu_1_links_json' =>
                '[{"label":"About","url":"/about"},{"label":"FAQs","url":"/faqs"},{"label":"Privacy","url":"/privacy"}]',

            'footer_menu_2_links_json' =>
                '[{"label":"Contact","url":"/contact"},{"label":"Terms","url":"/terms"}]',

            /*
             * Homepage section visibility.
             */
            'show_hero' => '1',
            'show_features' => '1',
            'show_stats' => '1',
            'show_about' => '1',
            'show_testimonials' => '1',
            'show_cta' => '1',

            /*
             * Floating website tools.
             *
             * Device values:
             * desktop,tablet,mobile
             */
            'whatsapp_enabled' => '0',
            'whatsapp_number' => '',
            'whatsapp_position' => 'right',
            'whatsapp_devices' => 'desktop,tablet,mobile',

            'live_chat_enabled' => '0',
            'live_chat_position' => 'right',
            'live_chat_devices' => 'desktop,tablet,mobile',

            'back_to_top_enabled' => '1',
            'back_to_top_position' => 'right',
            'back_to_top_devices' => 'desktop,tablet,mobile',

            'footer_heading' => '',
            'footer_text' =>
                'Thoughtful solutions, dependable service and a better experience for every customer.',

            'footer_links_json' => '[{"label": "About", "url": "/about"}, {"label": "Contact", "url": "/contact"}, {"label": "FAQs", "url": "/faqs"}, {"label": "Terms", "url": "/terms"}, {"label": "Privacy", "url": "/privacy"}]',

            'footer_copyright' =>
                '© {year} {website}. All rights reserved.',

            /*
             * Footer contact information.
             */
            'footer_phone_enabled' => '1',
            'footer_phone' => '',
            'footer_phone_icon' => 'phone',

            'footer_email_enabled' => '1',
            'footer_email' => '',
            'footer_email_icon' => 'mail',

            'footer_address_enabled' => '1',
            'footer_address' => '',
            'footer_address_icon' => 'location',

            /*
             * Footer social links.
             */
            'footer_socials_enabled' => '1',
            'footer_socials_json' => '[]',
        ];
    }

    protected function themeSettings(
        Website $website
    ): array {
        $defaults = static::defaults();

        $this->tenantDatabaseService->connect($website);

        try {
            $stored = $this->tenantDatabaseService
                ->connection()
                ->table('site_settings')
                ->where('key', 'like', 'theme.corporate.%')
                ->pluck('value', 'key')
                ->all();

            foreach ($defaults as $key => $default) {
                $defaults[$key] =
                    $stored['theme.corporate.' . $key]
                    ?? $default;
            }

            return $defaults;

        } finally {
            $this->tenantDatabaseService->disconnect();
        }
    }

    protected function activeTheme(
        Website $website
    ): string {
        $this->tenantDatabaseService->connect($website);

        try {
            $storedTheme =
                $this->tenantDatabaseService
                    ->connection()
                    ->table('site_settings')
                    ->where(
                        'key',
                        'theme.active'
                    )
                    ->value('value');

            /*
             * An empty value means there is intentionally
             * no active theme.
             *
             * Do NOT fall back to Business here, otherwise
             * disabling Business immediately appears active
             * again in the Themes interface.
             */
            return trim(
                (string) (
                    $storedTheme
                    ?? ''
                )
            );

        } finally {
            $this->tenantDatabaseService->disconnect();
        }
    }

    /*
     * ESUBIZ_GENERIC_INSTALLED_THEME_PREVIEW_V6C
     *
     * Installed Theme Registry.
     *
     * Preview metadata belongs to each theme rather than
     * to the Theme Hub or a Business-specific controller.
     *
     * Future Marketplace/theme installers should populate
     * this information from package/database metadata.
     */
    /*
     * ESUBIZ_CORE_GENERIC_THEME_HUB_V1
     *
     * Installed Themes belong to Core.
     *
     * No Theme definitions are hardcoded in this controller.
     * SaaS and off-server Core use the same installed-theme registry.
     */
    protected function installedThemes(
        string $activeTheme = '',
        ?int $websiteId = null
    ): array {
        return app(
            \App\Services\Core\Themes\InstalledThemeRegistry::class
        )->all(
            $activeTheme,
            $websiteId
        );
    }


    public function index(): View
    {
        $website = $this->website();

        $activeTheme =
            $this->activeTheme(
                $website
            );

        /*
         * Installed Themes are local Core state.
         */
        $themes =
            $this->installedThemes(
                $activeTheme,
                (int) $website->id
            );

        /*
         * Marketplace Themes are resolved through the universal
         * Core catalog adapter:
         *
         * SaaS       -> authoritative Central Esubiz catalog locally.
         * Off-server -> authoritative Central Esubiz catalog API.
         *
         * Installed state is automatically decorated from this
         * Core website's InstalledThemeRegistry.
         */
        $marketplaceThemes =
            app(
                \App\Services\Core\Themes\ThemeMarketplaceCatalogService::class
            )->themes(
                null,
                (int) $website->id
            );

        /*
         * ESUBIZ_CORE_THEME_DISPLAY_NAME_SYNC_V1
         *
         * Installed Theme identity remains local and immutable:
         * slug + version + Marketplace package ID.
         *
         * Presentation metadata such as the Theme display name follows
         * the current authoritative Marketplace catalog value whenever
         * that value is available.
         *
         * The locally stored installed name remains the safe fallback
         * for unavailable/offline catalog records.
         */
        $marketplaceThemeNames =
            collect($marketplaceThemes)
                ->filter(
                    fn ($theme) =>
                        (int) ($theme['id'] ?? 0) > 0
                        && trim((string) ($theme['name'] ?? '')) !== ''
                )
                ->mapWithKeys(
                    fn ($theme) => [
                        (int) $theme['id'] =>
                            trim((string) $theme['name']),
                    ]
                );

        $themes =
            collect($themes)
                ->map(
                    function (array $theme) use ($marketplaceThemeNames) {
                        $packageId =
                            (int) (
                                $theme['marketplace_theme_package_id']
                                ?? 0
                            );

                        if (
                            $packageId > 0
                            && $marketplaceThemeNames->has($packageId)
                        ) {
                            $theme['name'] =
                                $marketplaceThemeNames->get($packageId);
                        }

                        return $theme;
                    }
                )
                ->values()
                ->all();

        return view(
            'tenant.admin.themes.index',
            compact(
                'website',
                'themes',
                'activeTheme',
                'marketplaceThemes'
            )
        );
    }

    /*
     * ESUBIZ_CORE_THEME_MARKETPLACE_PAGE_V1
     *
     * Dedicated Theme Marketplace for Core.
     *
     * The URL, route and UI are Core features and remain identical
     * regardless of where Core is deployed.
     *
     * ThemeMarketplaceCatalogService internally resolves the correct
     * catalog/commerce source for the current Core installation.
     *
     * Installed state belongs to the current Core website.
     */
    public function marketplace(): View
    {
        $website =
            $this->website();

        $themes =
            app(
                \App\Services\Core\Themes\ThemeMarketplaceCatalogService::class
            )->themes(
                null,
                (int) $website->id
            );

        $categories =
            $themes
                ->pluck(
                    'marketplace.category'
                )
                ->filter()
                ->unique()
                ->sort()
                ->values();

        $featuredThemes =
            $themes
                ->filter(
                    fn ($theme) =>
                        (bool) data_get(
                            $theme,
                            'marketplace.featured',
                            false
                        )
                )
                ->values();

        /*
         * Most Purchased will become authoritative when Theme
         * purchase statistics are added to the Marketplace catalog.
         *
         * Do not invent purchase ranking.
         */
        $mostPurchasedThemes =
            collect();

        /*
         * Explicit publication timestamps will later drive Newest.
         * Until then the authoritative catalog order is preserved.
         */
        $newestThemes =
            $themes->values();

        return view(
            'tenant.admin.themes.marketplace',
            compact(
                'website',
                'themes',
                'categories',
                'featuredThemes',
                'mostPurchasedThemes',
                'newestThemes'
            )
        );
    }


    public function configureBusiness(): View
    {
        /*
         * Legacy route compatibility only.
         * Configuration itself is resolved by the universal Core method.
         */
        return $this->configure('business');
    }

    public function configure(
        string $theme
    ): View {
        $website = $this->website();

        $installedTheme =
            app(
                \App\Services\Core\Themes\InstalledThemeRegistry::class
            )->find(
                $theme,
                null,
                (int) $website->id
            );

        /*
         * Some legacy routes use an alias rather than the canonical
         * installed slug. Resolve aliases through the same registry.
         */
        if (!$installedTheme) {
            $installedTheme =
                collect(
                    $this->installedThemes(
                        $this->activeTheme($website),
                        (int) $website->id
                    )
                )->first(
                    function (array $candidate) use ($theme) {
                        if (
                            strtolower(
                                (string) ($candidate['slug'] ?? '')
                            ) === strtolower($theme)
                        ) {
                            return true;
                        }

                        return in_array(
                            strtolower($theme),
                            array_map(
                                'strtolower',
                                $candidate['aliases'] ?? []
                            ),
                            true
                        );
                    }
                );
        }

        abort_unless(
            is_array($installedTheme),
            404,
            'Theme not found.'
        );

        $themeSlug =
            (string) (
                $installedTheme['slug']
                ?? $theme
            );

        /*
         * Current Central/Marketplace display metadata overlays the
         * local installed snapshot. Technical identity never changes.
         */
        try {
            $marketplaceThemes =
                app(
                    \App\Services\Core\Themes\ThemeMarketplaceCatalogService::class
                )->themes(
                    null,
                    (int) $website->id
                );

            $packageId =
                (int) (
                    $installedTheme['marketplace_theme_package_id']
                    ?? 0
                );

            if ($packageId > 0) {
                $centralTheme =
                    collect($marketplaceThemes)->first(
                        fn ($candidate) =>
                            (int) ($candidate['id'] ?? 0)
                            === $packageId
                    );

                if (
                    is_array($centralTheme)
                    && trim(
                        (string) ($centralTheme['name'] ?? '')
                    ) !== ''
                ) {
                    $installedTheme['name'] =
                        trim(
                            (string) $centralTheme['name']
                        );
                }
            }
        } catch (\Throwable $exception) {
            /*
             * Central catalog availability must never break
             * an installed Theme's configuration page.
             */
            report($exception);
        }

        $themeDisplayName =
            trim(
                (string) (
                    $installedTheme['name']
                    ?? $themeSlug
                )
            );

        $themeSettings =
            $this->themeSettings($website);

        $features = json_decode(
            (string) (
                $themeSettings['features_json']
                ?? '[]'
            ),
            true
        );

        $testimonials = json_decode(
            (string) (
                $themeSettings['testimonials_json']
                ?? '[]'
            ),
            true
        );

        $footerLinks = json_decode(
            (string) (
                $themeSettings['footer_links_json']
                ?? '[]'
            ),
            true
        );

        if (!is_array($features)) {
            $features = [];
        }

        if (!is_array($testimonials)) {
            $testimonials = [];
        }

        if (!is_array($footerLinks)) {
            $footerLinks = [];
        }

        return view(
            'tenant.admin.themes.configure',
            [
                'website' => $website,
                'theme' => $themeSettings,
                'themeSlug' => $themeSlug,
                'themeDisplayName' => $themeDisplayName,
                'installedTheme' => $installedTheme,
                'features' => $features,
                'testimonials' => $testimonials,
                'footerLinks' => $footerLinks,
            ]
        );
    }

    /**
     * Save Business homepage CONTENT from Pages > Business Home.
     *
     * This endpoint deliberately preserves Theme Configuration
     * values that are not present in the homepage editor.
     *
     * Header, footer, navigation, favicon, floating tools and
     * global theme settings therefore remain Theme-owned.
     */
    public function updateBusinessHomepageContent(
        Request $request
    ): RedirectResponse {

        $website =
            $this->website();

        $current =
            $this->themeSettings(
                $website
            );

        /*
         * Preserve all ordinary scalar theme settings that are
         * absent from the Business Home content request.
         */
        $preserved = [];

        foreach (
            $current
            as $key => $value
        ) {
            if (
                $request->exists(
                    $key
                )
            ) {
                continue;
            }

            $preserved[
                $key
            ] = $value;
        }


        /*
         * Existing update() expects repeatable form arrays rather
         * than their JSON storage representation.
         */
        if (
            !$request->exists(
                'footer_links'
            )
        ) {
            $preserved[
                'footer_links'
            ] =
                json_decode(
                    (string) (
                        $current[
                            'footer_links_json'
                        ]
                        ?? '[]'
                    ),
                    true
                ) ?: [];
        }

        if (
            !$request->exists(
                'footer_socials'
            )
        ) {
            $preserved[
                'footer_socials'
            ] =
                json_decode(
                    (string) (
                        $current[
                            'footer_socials_json'
                        ]
                        ?? '[]'
                    ),
                    true
                ) ?: [];
        }


        $request->merge(
            $preserved
        );


        /*
         * Mark this as homepage-only so the update method can
         * return to the referring Business Home edit page.
         */
        $request->merge([
            '_business_home_content' =>
                '1',
        ]);


        return $this->update(
            $request,
            'business'
        );
    }


    public function updateBusiness(
        Request $request
    ): RedirectResponse {
        return $this->update(
            $request,
            'business'
        );
    }

    /**
     * AJAX theme activation endpoint.
     *
     * Theme and action come from the request body rather than
     * a dynamic URL segment. This is also suitable for future
     * marketplace-installed themes.
     */
    public function toggle(
        Request $request
    ) {
        $data = $request->validate([
            'theme' => [
                'required',
                'string',
                'max:100',
            ],

            'theme_action' => [
                'required',
                'in:enable,disable',
            ],
        ]);

        /*
         * Installed-theme resolution will become package-driven
         * as Marketplace themes are introduced.
         *
         * Business is the currently installed built-in theme.
         */
        abort_unless(
            in_array(
                $data['theme'],
                [
                    'business',
                    'corporate-default',
                ],
                true
            ),
            404,
            'Theme not found.'
        );

        $theme =
            $data['theme'] === 'corporate-default'
                ? 'business'
                : $data['theme'];

        $website =
            $this->website();

        $this->tenantDatabaseService
            ->connect($website);

        try {

            $db =
                $this->tenantDatabaseService
                    ->connection();

            if ($data['theme_action'] === 'enable') {

                /*
                 * Every theme contributes exactly its own
                 * editable theme homepage.
                 */
                if ($theme === 'business') {

                }

                $db->table('site_settings')
                    ->updateOrInsert(
                        [
                            'key' =>
                                'theme.active',
                        ],
                        [
                            'value' =>
                                $theme,
                        ]
                    );

                return response()->json([
                    'success' =>
                        true,

                    'active' =>
                        true,

                    'theme' =>
                        $theme,

                    'message' =>
                        'Business theme enabled.',
                ]);
            }


            /*
             * Disable only when this theme is currently active.
             */
            $active =
                $db->table('site_settings')
                    ->where(
                        'key',
                        'theme.active'
                    )
                    ->value('value');

            if (
                in_array(
                    $active,
                    [
                        'business',
                        'corporate-default',
                    ],
                    true
                )
            ) {
                $db->table('site_settings')
                    ->updateOrInsert(
                        [
                            'key' =>
                                'theme.active',
                        ],
                        [
                            'value' =>
                                '',
                        ]
                    );
            }

            return response()->json([
                'success' =>
                    true,

                'active' =>
                    false,

                'theme' =>
                    $theme,

                'message' =>
                    'Business theme disabled.',
            ]);

        } finally {

            $this->tenantDatabaseService
                ->disconnect();
        }
    }


    public function enable(
        Request $request,
        string $theme
    ) {
        abort_unless(
            in_array(
                $theme,
                ['business', 'corporate-default'],
                true
            ),
            404,
            'Theme not found.'
        );

        $website =
            $this->website();

        $this->tenantDatabaseService
            ->connect($website);



        try {
            $this->tenantDatabaseService
                ->connection()
                ->table('site_settings')
                ->updateOrInsert(
                    ['key' => 'theme.active'],
                    ['value' => 'business']
                );

        } finally {
            $this->tenantDatabaseService
                ->disconnect();
        }

        if (
            $request->expectsJson()
            || $request->ajax()
        ) {
            return response()->json([
                'success' => true,
                'active' => true,
                'theme' => 'business',
                'message' =>
                    'Business theme enabled.',
            ]);
        }

        return redirect()
            ->to(
                request()->getSchemeAndHttpHost()
                . '/admin/themes'
            )
            ->with(
                'success',
                'Business theme enabled.'
            );
    }

    public function disable(
        Request $request,
        string $theme
    ) {
        abort_unless(
            in_array(
                $theme,
                ['business', 'corporate-default'],
                true
            ),
            404,
            'Theme not found.'
        );

        $website =
            $this->website();

        $this->tenantDatabaseService
            ->connect($website);

        try {
            $this->tenantDatabaseService
                ->connection()
                ->table('site_settings')
                ->updateOrInsert(
                    ['key' => 'theme.active'],
                    ['value' => '']
                );

        } finally {
            $this->tenantDatabaseService
                ->disconnect();
        }

        if (
            $request->expectsJson()
            || $request->ajax()
        ) {
            return response()->json([
                'success' => true,
                'active' => false,
                'theme' => 'business',
                'message' =>
                    'Business theme disabled.',
            ]);
        }

        return redirect()
            ->to(
                request()->getSchemeAndHttpHost()
                . '/admin/themes'
            )
            ->with(
                'success',
                'Business theme disabled.'
            );
    }

    /**
     * Store theme media inside the website's canonical media bucket.
     *
     * All website-owned media shares:
     *
     * tenant-websites/{website_id}/media/...
     *
     * Theme files are namespaced below /theme so they remain
     * organised while consuming the same website storage quota.
     */
    /**
     * Store theme media in the website's canonical Media Library.
     */
    protected function storeThemeUpload(
        Website $website,
        $file,
        string $folder
    ): string {
        $folder =
            trim(
                preg_replace(
                    '/[^a-zA-Z0-9_-]/',
                    '',
                    $folder
                ),
                '/'
            );

        abort_if(
            $folder === '',
            422,
            'Invalid theme media folder.'
        );

        $extension =
            strtolower(
                $file->getClientOriginalExtension()
            );

        $filename =
            now()->format('YmdHis')
            . '-'
            . \Illuminate\Support\Str::lower(
                \Illuminate\Support\Str::random(12)
            )
            . '.'
            . $extension;

        $directory =
            'tenant-websites/'
            . $website->id
            . '/media/theme/'
            . $folder;

        /*
         * ESUBIZ_TENANT_THEME_IMAGE_OPTIMIZATION_V1
         *
         * Theme images use the same centralized optimization
         * policy as Media Library images while retaining their
         * canonical tenant storage path.
         */
        $stored =
            /* ESUBIZ_TENANT_UNIVERSAL_MEDIA_STORE_V1 */
        app(
                \App\Services\Media\CentralMediaService::class
            )->storeMediaToDisk(
                $file,
                'local',
                $directory,
                [
                    'maximum_edge' => 1920,
                    'image_quality' => 82,
                ]
            );


        if (!$stored) {

            $stored =
                \Illuminate\Support\Facades\Storage::disk('local')
                    ->putFileAs(
                        $directory,
                        $file,
                        $filename
                    );
        }


        abort_unless(
            $stored,
            500,
            'Theme image could not be stored.'
        );


        /*
         * Optimized assets are WEBP and receive a generated
         * filename, so registration must use the stored asset.
         */
        $filename =
            basename(
                $stored
            );

        /*
         * Register the uploaded theme asset in website_media
         * when the tenant Media Library table exists.
         */
        try {

            $db =
                $this->tenantDatabaseService
                    ->connection();

            if (
                \Illuminate\Support\Facades\Schema::connection(
                    'tenant'
                )->hasTable(
                    'website_media'
                )
            ) {

                $db->table(
                    'website_media'
                )
                    ->updateOrInsert(
                        [
                            'path' =>
                                $stored,
                        ],
                        [
                            'uuid' =>
                                (string)
                                \Illuminate\Support\Str::uuid(),

                            'filename' =>
                                $filename,

                            'original_name' =>
                                $file
                                    ->getClientOriginalName(),

                            'title' =>
                                pathinfo(
                                    $file
                                        ->getClientOriginalName(),
                                    PATHINFO_FILENAME
                                ),

                            'mime_type' =>
                                strtolower(
                                    pathinfo(
                                        $filename,
                                        PATHINFO_EXTENSION
                                    )
                                ) === 'webp'
                                    ? 'image/webp'
                                    : $file->getMimeType(),

                            'media_type' =>
                                'image',

                            'size_bytes' =>
                                (int) (
                                    \Illuminate\Support\Facades\Storage::disk(
                                        'local'
                                    )->size(
                                        $stored
                                    )
                                    ?: $file->getSize()
                                ),

                            'source' =>
                                'theme',

                            'source_context' =>
                                'theme/'
                                . $folder,

                            'updated_at' =>
                                now(),

                            'created_at' =>
                                now(),
                        ]
                    );
            }

        } catch (\Throwable $e) {

            /*
             * Media registration must never block the theme save.
             * Physical file storage remains authoritative.
             */
            report($e);
        }

        return $stored;
    }



    public function update(
        Request $request,
        string $theme
    ): RedirectResponse {
        abort_unless(
            in_array(
                $theme,
                ['business', 'corporate-default'],
                true
            ),
            404,
            'Theme not found.'
        );

        $website =
            $this->website();

        $data = $request->validate([

            /*
             * ESUBIZ_FOOTER_SOCIAL_AUTHORITATIVE_RULES
             */
            'footer_socials_enabled' => [
                'nullable',
                'in:0,1',
            ],

            'footer_socials' => [
                'nullable',
                'array',
            ],

            'footer_socials.*.platform' => [
                'nullable',
                'string',
                'in:facebook,instagram,x,linkedin,youtube,tiktok,whatsapp',
            ],

            'footer_socials.*.url' => [
                'nullable',
                'url',
                'max:1500',
            ],



            'footer_logo_enabled' => [
                'nullable',
                'in:0,1',
            ],

            'footer_text_enabled' => [
                'nullable',
                'in:0,1',
            ],

            'footer_brand_enabled' => [
                'nullable',
                'in:0,1',
            ],

            'footer_contact_enabled' => [
                'nullable',
                'in:0,1',
            ],

            'footer_menu_1_enabled' => [
                'nullable',
                'in:0,1',
            ],

            'footer_menu_2_enabled' => [
                'nullable',
                'in:0,1',
            ],

            'footer_menu_1_title' => [
                'nullable',
                'string',
                'max:120',
            ],

            'footer_menu_2_title' => [
                'nullable',
                'string',
                'max:120',
            ],


            'primary_color' => [
                'required',
                'regex:/^#[0-9A-Fa-f]{6}$/',
            ],

            'secondary_color' => [
                'required',
                'regex:/^#[0-9A-Fa-f]{6}$/',
            ],

            /*
             * Main images
             */
            'logo' => [
                'nullable',
                'image',
                'max:2048',
                'dimensions:max_width=1200,max_height=600',
            ],

            'favicon' => [
                'nullable',
                'image',
                'max:1024',
                'dimensions:max_width=512,max_height=512',
            ],

            'hero_image' => [
                'nullable',
                'image',
                'max:4096',
                'dimensions:min_width=600,max_width=3000,min_height=400,max_height=2200',
            ],

            'about_image' => [
                'nullable',
                'image',
                'max:4096',
                'dimensions:min_width=500,max_width=3000,min_height=400,max_height=2200',
            ],

            'footer_logo' => [
                'nullable',
                'image',
                'max:2048',
                'dimensions:max_width=1200,max_height=600',
            ],

            'footer_background' => [
                'nullable',
                'image',
                'max:4096',
                'dimensions:min_width=800,max_width=3500,min_height=400,max_height=2200',
            ],

            /*
             * Repeatable features
             */
            'features' => [
                'nullable',
                'array',
            ],

            'features.*.title' => [
                'nullable',
                'string',
                'max:180',
            ],

            'features.*.text' => [
                'nullable',
                'string',
                'max:1000',
            ],

            'features.*.enabled' => [
                'nullable',
                'boolean',
            ],

            'features.*.show_image' => [
                'nullable',
                'boolean',
            ],

            'features.*.icon' => [
                'nullable',
                'string',
                'max:180',
            ],

            'features.*.show_icon' => [
                'nullable',
                'boolean',
            ],

            'features.*.show_button' => [
                'nullable',
                'boolean',
            ],

            'features.*.button_label' => [
                'nullable',
                'string',
                'max:180',
            ],

            'features.*.button_url' => [
                'nullable',
                'string',
                'max:1000',
            ],

            'features.*.button_url_active' => [
                'nullable',
                'boolean',
            ],

            'features.*.existing_image' => [
                'nullable',
                'string',
                'max:1000',
            ],

            'features.*.image' => [
                'nullable',
                'image',
                'max:3072',
                'dimensions:min_width=400,max_width=2400,min_height=250,max_height=1800',
            ],

            /*
             * Repeatable testimonials
             */
            'testimonials' => [
                'nullable',
                'array',
            ],

            'testimonials.*.name' => [
                'nullable',
                'string',
                'max:180',
            ],

            'testimonials.*.role' => [
                'nullable',
                'string',
                'max:180',
            ],

            'testimonials.*.text' => [
                'nullable',
                'string',
                'max:1200',
            ],

            'testimonials.*.enabled' => [
                'nullable',
                'boolean',
            ],

            'testimonials.*.show_image' => [
                'nullable',
                'boolean',
            ],

            'testimonials.*.icon' => [
                'nullable',
                'string',
                'max:180',
            ],

            'testimonials.*.show_icon' => [
                'nullable',
                'boolean',
            ],

            'testimonials.*.show_button' => [
                'nullable',
                'boolean',
            ],

            'testimonials.*.button_label' => [
                'nullable',
                'string',
                'max:180',
            ],

            'testimonials.*.button_url' => [
                'nullable',
                'string',
                'max:1000',
            ],

            'testimonials.*.button_url_active' => [
                'nullable',
                'boolean',
            ],

            'testimonials.*.existing_photo' => [
                'nullable',
                'string',
                'max:1000',
            ],

            'testimonials.*.photo' => [
                'nullable',
                'image',
                'max:2048',
                'dimensions:max_width=1200,max_height=1200',
            ],

            /*
             * Footer links
             */
            /*
             * Theme image removal.
             *
             * Removing from Theme Configuration only removes the
             * theme reference. The Media Library file is retained
             * because another site feature may reuse it.
             */
            'remove_logo' => [
                'nullable',
                'boolean',
            ],

            'remove_favicon' => [
                'nullable',
                'boolean',
            ],

            'remove_hero_image' => [
                'nullable',
                'boolean',
            ],

            'remove_about_image' => [
                'nullable',
                'boolean',
            ],

            'remove_footer_logo' => [
                'nullable',
                'boolean',
            ],

            'remove_footer_background' => [
                'nullable',
                'boolean',
            ],

            'features.*.remove_image' => [
                'nullable',
                'boolean',
            ],

            'testimonials.*.remove_photo' => [
                'nullable',
                'boolean',
            ],


            'footer_links' => [
                'nullable',
                'array',
            ],

            'footer_links.*.label' => [
                'nullable',
                'string',
                'max:100',
            ],

            'footer_links.*.url' => [
                'nullable',
                'string',
                'max:500',
            ],

            'footer_socials' => [
                'nullable',
                'array',
            ],

            'footer_socials.*.icon' => [
                'nullable',
                'string',
                'max:50',
            ],

            'footer_socials.*.label' => [
                'nullable',
                'string',
                'max:100',
            ],

            'footer_socials.*.url' => [
                'nullable',
                'string',
                'max:500',
            ],

            'footer_socials.*.enabled' => [
                'nullable',
                'in:0,1',
            ],
        ]);

        /*
         * ESUBIZ_FOOTER_SOCIAL_AUTHORITATIVE_NORMALIZE
         *
         * Section 2 is authoritative.
         * An empty UI means there are ZERO social links.
         */
        $normalizedFooterSocials = [];

        foreach (
            (array) $request->input(
                'footer_socials',
                []
            )
            as $footerSocial
        ) {

            if (
                !is_array(
                    $footerSocial
                )
            ) {
                continue;
            }

            $platform =
                strtolower(
                    trim(
                        (string) (
                            $footerSocial[
                                'platform'
                            ] ?? ''
                        )
                    )
                );

            $url =
                trim(
                    (string) (
                        $footerSocial[
                            'url'
                        ] ?? ''
                    )
                );

            if (
                $platform === ''
                || $url === ''
            ) {
                continue;
            }

            if (
                !in_array(
                    $platform,
                    [
                        'facebook',
                        'instagram',
                        'x',
                        'linkedin',
                        'youtube',
                        'tiktok',
                        'whatsapp',
                    ],
                    true
                )
            ) {
                continue;
            }

            /*
             * Preserve compatibility with the earlier footer
             * renderer/save structure while making platform
             * the canonical identifier.
             */
            $normalizedFooterSocials[] = [
                'platform' =>
                    $platform,

                'label' =>
                    $platform,

                'icon' =>
                    $platform,

                'url' =>
                    $url,
            ];
        }

        /*
         * Important:
         *
         * Always merge footer_socials, even when empty.
         * This allows deleting the final/stuck social link.
         */
        $request->merge([
            'footer_socials' =>
                $normalizedFooterSocials,
        ]);



        $defaults =
            static::defaults();

        $skip = [
            'logo_path',
            'favicon_path',
            'hero_image_path',
            'about_image_path',
            'footer_logo_path',
            'footer_background_path',
            'features_json',
            'testimonials_json',
            'footer_links_json',
            'footer_socials_json',
        ];

        $this->tenantDatabaseService
            ->connect($website);

        /*
         * ESUBIZ_FOUR_SECTION_FOOTER_PERSISTENCE
         *
         * Theme-owned footer layout settings.
         */
        foreach (
            [
                'footer_brand_enabled',
                'footer_logo_enabled',
                'footer_text_enabled',
                'footer_contact_enabled',
                'footer_menu_1_enabled',
                'footer_menu_2_enabled',
                'footer_menu_1_title',
                'footer_menu_2_title',
            ]
            as $footerSetting
        ) {
            if (
                !$request->exists(
                    $footerSetting
                )
            ) {
                continue;
            }

            $this->tenantDatabaseService
                ->connection()
                ->table('site_settings')
                ->updateOrInsert(
                    [
                        'key' =>
                            'theme.corporate.'
                            . $footerSetting,
                    ],
                    [
                        'value' =>
                            (string) (
                                $request->input(
                                    $footerSetting
                                )
                                ?? ''
                            ),
                    ]
                );
        }



        try {
            $db =
                $this->tenantDatabaseService
                    ->connection();

            /*
             * Save every simple configurable key.
             */
            foreach ($defaults as $key => $default) {

                if (in_array($key, $skip, true)) {
                    continue;
                }

                if (!$request->exists($key)) {
                    continue;
                }

                $value =
                    (string) $request->input(
                        $key,
                        ''
                    );

                $db->table('site_settings')
                    ->updateOrInsert(
                        [
                            'key' =>
                                'theme.corporate.'
                                . $key,
                        ],
                        [
                            'value' =>
                                $value,
                        ]
                    );
            }

            /*
             * Section-level images.
             */
            /*
             * Remove current theme image references when requested.
             *
             * Physical Media Library files are deliberately retained.
             */
            $imageRemovalSettings = [
                'remove_logo' =>
                    'logo_path',

                'remove_favicon' =>
                    'favicon_path',

                'remove_hero_image' =>
                    'hero_image_path',

                'remove_about_image' =>
                    'about_image_path',

                'remove_footer_logo' =>
                    'footer_logo_path',

                'remove_footer_background' =>
                    'footer_background_path',
            ];

            foreach (
                $imageRemovalSettings
                as $removeInput => $setting
            ) {
                if (
                    !$request->boolean(
                        $removeInput
                    )
                ) {
                    continue;
                }

                $db->table('site_settings')
                    ->updateOrInsert(
                        [
                            'key' =>
                                'theme.corporate.'
                                . $setting,
                        ],
                        [
                            'value' => '',
                        ]
                    );
            }


            /*
             * Remove current theme image references when requested.
             *
             * Physical Media Library files are deliberately retained.
             */
            $imageRemovalSettings = [
                'remove_logo' =>
                    'logo_path',

                'remove_favicon' =>
                    'favicon_path',

                'remove_hero_image' =>
                    'hero_image_path',

                'remove_about_image' =>
                    'about_image_path',

                'remove_footer_logo' =>
                    'footer_logo_path',

                'remove_footer_background' =>
                    'footer_background_path',
            ];

            foreach (
                $imageRemovalSettings
                as $removeInput => $setting
            ) {
                if (
                    !$request->boolean(
                        $removeInput
                    )
                ) {
                    continue;
                }

                $db->table('site_settings')
                    ->updateOrInsert(
                        [
                            'key' =>
                                'theme.corporate.'
                                . $setting,
                        ],
                        [
                            'value' => '',
                        ]
                    );
            }


            /*
             * Remove current theme image references when requested.
             *
             * Physical Media Library files are deliberately retained.
             */
            $imageRemovalSettings = [
                'remove_logo' =>
                    'logo_path',

                'remove_favicon' =>
                    'favicon_path',

                'remove_hero_image' =>
                    'hero_image_path',

                'remove_about_image' =>
                    'about_image_path',

                'remove_footer_logo' =>
                    'footer_logo_path',

                'remove_footer_background' =>
                    'footer_background_path',
            ];

            foreach (
                $imageRemovalSettings
                as $removeInput => $setting
            ) {
                if (
                    !$request->boolean(
                        $removeInput
                    )
                ) {
                    continue;
                }

                $db->table('site_settings')
                    ->updateOrInsert(
                        [
                            'key' =>
                                'theme.corporate.'
                                . $setting,
                        ],
                        [
                            'value' => '',
                        ]
                    );
            }


            $uploads = [
                'logo' => [
                    'setting' => 'logo_path',
                    'folder' => 'logo',
                ],

                'favicon' => [
                    'setting' => 'favicon_path',
                    'folder' => 'favicon',
                ],

                'hero_image' => [
                    'setting' => 'hero_image_path',
                    'folder' => 'hero',
                ],

                'about_image' => [
                    'setting' => 'about_image_path',
                    'folder' => 'about',
                ],

                'footer_logo' => [
                    'setting' => 'footer_logo_path',
                    'folder' => 'footer',
                ],

                'footer_background' => [
                    'setting' => 'footer_background_path',
                    'folder' => 'footer',
                ],
            ];

            foreach ($uploads as $input => $config) {

                if (!$request->hasFile($input)) {
                    continue;
                }

                $stored =
                    $this->storeThemeUpload(
                        $website,
                        $request->file($input),
                        $config['folder']
                    );

                $db->table('site_settings')
                    ->updateOrInsert(
                        [
                            'key' =>
                                'theme.corporate.'
                                . $config['setting'],
                        ],
                        [
                            'value' => $stored,
                        ]
                    );
            }

            /*
             * Features
             */
            $features = [];

            foreach (
                $request->input(
                    'features',
                    []
                )
                as $index => $feature
            ) {
                $title =
                    trim(
                        (string) (
                            $feature['title']
                            ?? ''
                        )
                    );

                $text =
                    trim(
                        (string) (
                            $feature['text']
                            ?? ''
                        )
                    );

                $imagePath =
                    (string) (
                        $feature['existing_image']
                        ?? ''
                    );

                if (
                    !empty(
                        $feature['remove_image']
                    )
                ) {
                    $imagePath = '';
                }

                if (
                    !empty(
                        $feature['remove_image']
                    )
                ) {
                    $imagePath = '';
                }

                if (
                    !empty(
                        $feature['remove_image']
                    )
                ) {
                    $imagePath = '';
                }

                if (
                    $request->hasFile(
                        'features.'
                        . $index
                        . '.image'
                    )
                ) {
                    $imagePath =
                        $this->storeThemeUpload(
                            $website,
                            $request->file(
                                'features.'
                                . $index
                                . '.image'
                            ),
                            'features'
                        );
                }

                if (
                    $title === ''
                    && $text === ''
                    && $imagePath === ''
                ) {
                    continue;
                }

                /*
                 * ESUBIZ_BUSINESS_STRUCTURED_SAVE_RENDER_V1
                 */
                $features[] = [
                    'enabled' =>
                        ((string) ($feature['enabled'] ?? '0')) === '1',

                    'title' => $title,
                    'text' => $text,
                    'image_path' => $imagePath,

                    'show_image' =>
                        ((string) (
                            $feature['show_image']
                            ?? ($imagePath !== '' ? '1' : '0')
                        )) === '1',

                    'icon' =>
                        trim((string) ($feature['icon'] ?? '')),

                    'show_icon' =>
                        ((string) ($feature['show_icon'] ?? '0')) === '1',

                    'show_button' =>
                        ((string) ($feature['show_button'] ?? '0')) === '1',

                    'button_label' =>
                        trim((string) ($feature['button_label'] ?? '')),

                    'button_url' =>
                        trim((string) ($feature['button_url'] ?? '')),

                    'button_url_active' =>
                        ((string) (
                            $feature['button_url_active'] ?? '0'
                        )) === '1',
                ];
            }

            $db->table('site_settings')
                ->updateOrInsert(
                    [
                        'key' =>
                            'theme.corporate.features_json',
                    ],
                    [
                        'value' =>
                            json_encode(
                                $features,
                                JSON_UNESCAPED_SLASHES
                            ),
                    ]
                );

            /*
             * Testimonials
             */
            $testimonials = [];

            foreach (
                $request->input(
                    'testimonials',
                    []
                )
                as $index => $testimonial
            ) {
                $name =
                    trim(
                        (string) (
                            $testimonial['name']
                            ?? ''
                        )
                    );

                $role =
                    trim(
                        (string) (
                            $testimonial['role']
                            ?? ''
                        )
                    );

                $text =
                    trim(
                        (string) (
                            $testimonial['text']
                            ?? ''
                        )
                    );

                $photoPath =
                    (string) (
                        $testimonial['existing_photo']
                        ?? ''
                    );

                if (
                    !empty(
                        $testimonial['remove_photo']
                    )
                ) {
                    $photoPath = '';
                }

                if (
                    $request->hasFile(
                        'testimonials.'
                        . $index
                        . '.photo'
                    )
                ) {
                    $photoPath =
                        $this->storeThemeUpload(
                            $website,
                            $request->file(
                                'testimonials.'
                                . $index
                                . '.photo'
                            ),
                            'testimonials'
                        );
                }

                if (
                    $name === ''
                    && $role === ''
                    && $text === ''
                    && $photoPath === ''
                ) {
                    continue;
                }

                $testimonials[] = [
                    'enabled' =>
                        ((string) (
                            $testimonial['enabled'] ?? '0'
                        )) === '1',

                    'name' => $name,
                    'role' => $role,
                    'text' => $text,
                    'photo_path' => $photoPath,

                    'show_image' =>
                        ((string) (
                            $testimonial['show_image']
                            ?? ($photoPath !== '' ? '1' : '0')
                        )) === '1',

                    'icon' =>
                        trim((string) ($testimonial['icon'] ?? '')),

                    'show_icon' =>
                        ((string) (
                            $testimonial['show_icon'] ?? '0'
                        )) === '1',

                    'show_button' =>
                        ((string) (
                            $testimonial['show_button'] ?? '0'
                        )) === '1',

                    'button_label' =>
                        trim((string) (
                            $testimonial['button_label'] ?? ''
                        )),

                    'button_url' =>
                        trim((string) (
                            $testimonial['button_url'] ?? ''
                        )),

                    'button_url_active' =>
                        ((string) (
                            $testimonial['button_url_active'] ?? '0'
                        )) === '1',
                ];
            }

            $db->table('site_settings')
                ->updateOrInsert(
                    [
                        'key' =>
                            'theme.corporate.testimonials_json',
                    ],
                    [
                        'value' =>
                            json_encode(
                                $testimonials,
                                JSON_UNESCAPED_SLASHES
                            ),
                    ]
                );

            /*
             * Footer links
             */
            $footerLinks = [];

            foreach (
                $request->input(
                    'footer_links',
                    []
                )
                as $link
            ) {
                $label =
                    trim(
                        (string) (
                            $link['label']
                            ?? ''
                        )
                    );

                $url =
                    trim(
                        (string) (
                            $link['url']
                            ?? ''
                        )
                    );

                if (
                    $label === ''
                    && $url === ''
                ) {
                    continue;
                }

                $footerLinks[] = [
                    'label' => $label,
                    'url' => $url,
                ];
            }

            $db->table('site_settings')
                ->updateOrInsert(
                    [
                        'key' =>
                            'theme.corporate.footer_links_json',
                    ],
                    [
                        'value' =>
                            json_encode(
                                $footerLinks,
                                JSON_UNESCAPED_SLASHES
                            ),
                    ]
                );


            /*
             * Footer social links.
             */
            $footerSocials = [];

            foreach (
                $request->input(
                    'footer_socials',
                    []
                )
                as $social
            ) {
                $icon =
                    trim(
                        (string) (
                            $social['icon']
                            ?? ''
                        )
                    );

                $label =
                    trim(
                        (string) (
                            $social['label']
                            ?? ''
                        )
                    );

                $url =
                    trim(
                        (string) (
                            $social['url']
                            ?? ''
                        )
                    );

                $enabled =
                    (
                        (string) (
                            $social['enabled']
                            ?? '1'
                        )
                    ) === '1';

                if (
                    $icon === ''
                    && $label === ''
                    && $url === ''
                ) {
                    continue;
                }

                $footerSocials[] = [
                    'icon' => $icon,
                    'label' => $label,
                    'url' => $url,
                    'enabled' => $enabled,
                ];
            }

            $db->table('site_settings')
                ->updateOrInsert(
                    [
                        'key' =>
                            'theme.corporate.footer_socials_json',
                    ],
                    [
                        'value' =>
                            json_encode(
                                $footerSocials,
                                JSON_UNESCAPED_SLASHES
                            ),
                    ]
                );


        } finally {
            
            /*
             * ESUBIZ_FOOTER_SOCIAL_AUTHORITATIVE_WRITE
             *
             * This write intentionally happens last.
             * Whatever currently exists in Section 2 becomes
             * the complete stored social-link collection.
             *
             * [] therefore deletes all previous links.
             */
            $this->tenantDatabaseService
                ->connection()
                ->table('site_settings')
                ->updateOrInsert(
                    [
                        'key' =>
                            'theme.corporate.footer_socials_json',
                    ],
                    [
                        'value' =>
                            json_encode(
                                $normalizedFooterSocials,
                                JSON_UNESCAPED_SLASHES
                                | JSON_UNESCAPED_UNICODE
                            ),
                    ]
                );

            $this->tenantDatabaseService
                ->connection()
                ->table('site_settings')
                ->updateOrInsert(
                    [
                        'key' =>
                            'theme.corporate.footer_socials_enabled',
                    ],
                    [
                        'value' =>
                            (string) $request->input(
                                'footer_socials_enabled',
                                '1'
                            ),
                    ]
                );


$this->tenantDatabaseService
                ->disconnect();
        }

        return back()->with(
            'success',
            'Business theme configuration saved.'
        );
    }


    /**
     * Serve a theme-owned media asset for the current tenant.
     *
     * Every configurable Business theme image uses this same
     * tenant-isolated storage contract:
     *
     * storage/app/public/tenants/{website_id}/theme/{path}
     *
     * Database values remain relative to the theme root, e.g.
     * logo/abc.png, hero/abc.jpg, features/abc.webp.
     */

    /**
     * Serve configurable theme media for the current tenant.
     *
     * Theme images are stored at:
     *
     * storage/app/public/tenants/{website_id}/theme/{path}
     *
     * Examples:
     * logo/file.png
     * favicon/file.png
     * hero/file.jpg
     * about/file.jpg
     * footer/file.jpg
     * features/file.jpg
     * testimonials/file.jpg
     */
    public function asset(
        string $path
    ) {
        $website =
            $this->website();


        $path =
            ltrim(
                rawurldecode(
                    $path
                ),
                '/'
            );


        /*
         * Allow nested theme folders but reject traversal,
         * Windows separators and null bytes.
         */
        abort_if(
            $path === ''
            || str_contains(
                $path,
                '..'
            )
            || str_contains(
                $path,
                chr(0)
            )
            || str_contains(
                $path,
                chr(92)
            ),
            404
        );


        $file =
            storage_path(
                'app/public/tenants/'
                . $website->id
                . '/theme/'
                . $path
            );


        abort_unless(
            is_file(
                $file
            ),
            404,
            'Theme asset not found.'
        );


        /*
         * ESUBIZ_TENANT_THEME_ASSET_BANDWIDTH_ACCOUNTING_V1
         *
         * Count the actual theme asset bytes served for this
         * tenant website.
         */
        try {
            $bytes =
                (int) filesize(
                    $file
                );

            app(
                \App\Services\Website\TenantBandwidthUsageService::class
            )->recordBytes(
                $website,
                $bytes,
                'tenant_theme_asset',
                null,
                [
                    'path' => $path,
                ]
            );
        } catch (\Throwable $e) {
            /*
             * Accounting must never block a valid theme asset.
             */
        }

        /*
         * Laravel determines the correct MIME type from
         * the actual stored file.
         */
        return response()->file(
            $file,
            [
                'Cache-Control' =>
                    'public, max-age=86400',

                'X-Content-Type-Options' =>
                    'nosniff',
            ]
        );
    }



    




    /*
     * Universal installed-theme preview delivery.
     */
    public function themePreview(
        string $theme
    ) {
        $website = $this->website();

        $definition = null;

        foreach (
            $this->installedThemes(
                '',
                (int) $website->id
            )
            as $installedTheme
        ) {
            $slug =
                (string) (
                    $installedTheme['slug']
                    ?? ''
                );

            $aliases =
                $installedTheme['aliases']
                ?? [];

            if (!is_array($aliases)) {
                $aliases = [];
            }

            if (
                $theme === $slug
                || in_array(
                    $theme,
                    $aliases,
                    true
                )
            ) {
                $definition =
                    $installedTheme;

                break;
            }
        }

        abort_unless(
            is_array($definition),
            404,
            'Theme not found.'
        );

        $previewPath =
            ltrim(
                (string) (
                    $definition['preview_path']
                    ?? ''
                ),
                '/'
            );

        abort_unless(
            $previewPath !== '',
            404,
            'Theme preview is not configured.'
        );

        $preview =
            public_path(
                $previewPath
            );

        /*
         * ESUBIZ_CORE_INSTALLED_THEME_PREVIEW_FALLBACK_V2
         *
         * Installed Theme preview resolution is a Core capability.
         *
         * 1. Prefer the locally installed Theme preview.
         * 2. If the local preview is unavailable and this installed
         *    Theme originated from Marketplace, use the protected
         *    Central Esubiz package preview endpoint.
         *
         * No Theme names are hardcoded here.
         */
        if (is_file($preview)) {
            $mime =
                mime_content_type(
                    $preview
                )
                ?: 'application/octet-stream';

            return response()->file(
                $preview,
                [
                    'Content-Type' =>
                        $mime,

                    'Cache-Control' =>
                        'public, max-age=3600',

                    'X-Content-Type-Options' =>
                        'nosniff',
                ]
            );
        }

        $marketplaceThemePackageId =
            (int) (
                $definition[
                    'marketplace_theme_package_id'
                ]
                ?? 0
            );

        if ($marketplaceThemePackageId > 0) {
            $centralMarketplaceUrl =
                rtrim(
                    (string) config(
                        'services.esubiz.marketplace_url',
                        config(
                            'app.url'
                        )
                    ),
                    '/'
                );

            abort_unless(
                $centralMarketplaceUrl !== '',
                404,
                'Theme preview source is not configured.'
            );

            $centralPreviewUrl =
                $centralMarketplaceUrl
                . '/marketplace/themes/'
                . $marketplaceThemePackageId
                . '/preview';

            return redirect()->away(
                $centralPreviewUrl
            );
        }

        abort(
            404,
            'Theme preview not found.'
        );
    }
}
