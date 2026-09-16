<!DOCTYPE html>
<html lang="en">

<head>

    {-- ESUBIZ_CORE_AUTH_CANONICAL_SETTINGS_V69 --}
    @php
    /*
     * ==========================================================
     * CANONICAL CORE SHELL SETTINGS
     * ==========================================================
     *
     * resources/views/tenant/admin/layouts/app.blade.php
     * is the ONE internal Core layout/header source.
     *
     * resources/views/tenant/admin/partials/sidebar.blade.php
     * is the ONE Core sidebar source.
     *
     * Role changes affect menu visibility only.
     * Branding is shared by every Core role.
     */

    $coreExistingSettings =
        isset($settings) && is_array($settings)
            ? $settings
            : [];

    $coreFallbackSettings = [];

    /*
     * Preserve Website model settings exactly as stored.
     * Do NOT transform dotted keys into nested arrays.
     */
    if (isset($website) && $website) {

        $rawWebsiteSettings =
            $website->settings ?? null;

        if (is_string($rawWebsiteSettings)) {

            $decodedWebsiteSettings =
                json_decode(
                    $rawWebsiteSettings,
                    true
                );

            if (is_array($decodedWebsiteSettings)) {
                $coreFallbackSettings =
                    array_replace_recursive(
                        $coreFallbackSettings,
                        $decodedWebsiteSettings
                    );
            }

        } elseif (is_array($rawWebsiteSettings)) {

            $coreFallbackSettings =
                array_replace_recursive(
                    $coreFallbackSettings,
                    $rawWebsiteSettings
                );

        } elseif (
            $rawWebsiteSettings instanceof
                \Illuminate\Contracts\Support\Arrayable
        ) {

            $coreFallbackSettings =
                array_replace_recursive(
                    $coreFallbackSettings,
                    $rawWebsiteSettings->toArray()
                );
        }
    }

    /*
     * Read tenant settings when the current route did not already
     * receive them from its controller.
     *
     * IMPORTANT:
     * Keep the DB key literally as stored.
     *
     * Example:
     * theme.corporate.footer_logo_path
     *
     * Do NOT use data_set() here because that would change the
     * established Core settings structure.
     */
    if (empty($coreExistingSettings)) {

        try {

            $tenantSchema =
                \Illuminate\Support\Facades\Schema::connection(
                    'tenant'
                );

            $settingsTable = null;

            if ($tenantSchema->hasTable('site_settings')) {
                $settingsTable = 'site_settings';
            } elseif ($tenantSchema->hasTable('settings')) {
                $settingsTable = 'settings';
            }

            if ($settingsTable) {

                $rows =
                    \Illuminate\Support\Facades\DB::connection(
                        'tenant'
                    )
                    ->table($settingsTable)
                    ->get();

                foreach ($rows as $row) {

                    $key =
                        $row->key
                        ?? $row->name
                        ?? $row->setting_key
                        ?? null;

                    if (!$key) {
                        continue;
                    }

                    $value =
                        $row->value
                        ?? $row->setting_value
                        ?? null;

                    if (is_string($value)) {

                        $decoded =
                            json_decode(
                                $value,
                                true
                            );

                        if (
                            json_last_error()
                                === JSON_ERROR_NONE
                            && (
                                is_array($decoded)
                                || is_bool($decoded)
                                || is_numeric($decoded)
                            )
                        ) {
                            $value = $decoded;
                        }
                    }

                    /*
                     * FLAT KEY IS INTENTIONAL.
                     */
                    $coreFallbackSettings[
                        (string) $key
                    ] = $value;
                }
            }

        } catch (\Throwable $e) {
            /*
             * Shell must remain usable even if optional fallback
             * storage cannot be read.
             */
        }
    }

    /*
     * Existing controller-provided settings remain authoritative.
     */
    $settings = array_replace_recursive(
        $coreFallbackSettings,
        $coreExistingSettings
    );

    /*
     * Compatibility aliases from page/theme variables.
     * These do not create a second branding source.
     */
    if (
        empty(
            $settings[
                'theme.corporate.footer_logo_path'
            ] ?? null
        )
        && isset($theme)
        && is_array($theme)
        && !empty($theme['footer_logo_path'] ?? null)
    ) {
        $settings[
            'theme.corporate.footer_logo_path'
        ] = $theme['footer_logo_path'];
    }

    if (
        empty(
            $settings[
                'theme.corporate.footer_logo_path'
            ] ?? null
        )
        && isset($siteConfig)
        && is_array($siteConfig)
        && !empty(
            $siteConfig['footer_logo_path'] ?? null
        )
    ) {
        $settings[
            'theme.corporate.footer_logo_path'
        ] = $siteConfig['footer_logo_path'];
    }
@endphp

    {{-- ESUBIZ_CORE_AUTH_CANONICAL_FAVICON_V69 --}}
    {{-- ESUBIZ_CORE_GLOBAL_FAVICON_V44 --}}
    @php
        /*
         * ESUBIZ_CORE_AUTH_CANONICAL_BRANDING_V73
         *
         * Core Site Settings is authoritative for auth branding.
         * site_favicon_path is the canonical favicon.
         *
         * Theme and historical keys remain compatibility fallbacks.
         * Empty values mean inherit.
         */
        $tenantAdminFavicon =
            collect([
                $settings['site_favicon_path'] ?? null,
                $settings['theme.corporate.favicon_path'] ?? null,
                $settings['website_favicon_path'] ?? null,
                $settings['favicon_path'] ?? null,
            ])->first(
                static fn ($value) =>
                    trim((string) $value) !== ''
            );
    @endphp

    @if(!empty($tenantAdminFavicon))
        <link
            rel="icon"
            href="{{ request()->getSchemeAndHttpHost()
                . '/media/'
                . implode(
                    '/',
                    array_map(
                        'rawurlencode',
                        explode(
                            '/',
                            ltrim(
                                $tenantAdminFavicon,
                                '/'
                            )
                        )
                    )
                ) }}"
        >
    @endif


    

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>
        Admin Login - {{ $website->name }}
    </title>

    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-slate-100">

<div
    class="flex min-h-screen items-center justify-center px-5 py-10"
>

    <div
        class="w-full max-w-md rounded-3xl border border-slate-200 bg-white p-8 shadow-xl"
    >

        @php
            /*
             * Canonical Core authentication identity.
             *
             * The standard Site Settings logo is authoritative because
             * the authentication card uses a light background.
             *
             * Empty values mean inherit.
             */
            $tenantAdminLogo =
                collect([
                    $settings['site_logo_path'] ?? null,
                    $settings['site_logo_white_path'] ?? null,
                    $settings['theme.corporate.logo_path'] ?? null,
                    $settings['logo_path'] ?? null,
                ])->first(
                    static fn ($value) =>
                        trim((string) $value) !== ''
                );

            $tenantAdminLogoUrl =
                !empty($tenantAdminLogo)
                    ? request()->getSchemeAndHttpHost()
                        . '/media/'
                        . implode(
                            '/',
                            array_map(
                                'rawurlencode',
                                explode(
                                    '/',
                                    ltrim(
                                        $tenantAdminLogo,
                                        '/'
                                    )
                                )
                            )
                        )
                    : null;
        @endphp

        @if($tenantAdminLogoUrl)
            <a
                href="/"
                class="inline-flex max-w-full items-center"
                aria-label="{{ $website->name }}"
            >
                <img
                    src="{{ $tenantAdminLogoUrl }}"
                    alt="{{ $website->name }}"
                    width="180"
                    height="60"
                    class="block h-[60px] w-[180px] max-w-full object-contain object-left"
                >
            </a>
        @else
            <h1
                class="text-3xl font-black tracking-tight text-slate-900"
            >
                {{ $website->name }}
            </h1>
        @endif

        <p
            class="mt-4 text-sm leading-6 text-slate-500"
        >
            Sign in to manage this website.
        </p>


        <div
            class="mt-8 rounded-2xl border border-blue-100 bg-blue-50 p-4 text-sm text-blue-800"
        >
            Tenant administrator authentication is being connected next.
            This page is now independent from the central Esubiz Admin login.
        </div>


        <div class="mt-7 space-y-3">

            <button
                type="button"
                disabled
                class="w-full cursor-not-allowed rounded-xl bg-slate-900 px-5 py-3.5 font-black text-white opacity-60"
            >
                Website Account Login — Coming Next
            </button>

            @if($ssoUrl)

                <a
                    href="https://esubiz.com/websites/{{ $website->id }}/dashboard"
                    class="block w-full rounded-xl border border-blue-200 bg-blue-50 px-5 py-3.5 text-center font-black text-blue-700 transition hover:bg-blue-100"
                >
                    Continue with Esubiz
                </a>

            @else

                <button
                    type="button"
                    disabled
                    class="w-full cursor-not-allowed rounded-xl border border-slate-300 bg-white px-5 py-3.5 font-black text-slate-400 opacity-70"
                >
                    Esubiz SSO Not Configured
                </button>

            @endif

        </div>


        <a
            href="/"
            class="mt-7 block text-center text-sm font-bold text-blue-600"
        >
            ← Back to Website
        </a>

    </div>

</div>

</body>
</html>
