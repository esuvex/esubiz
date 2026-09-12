{{-- ESUBIZ_CORE_CANONICAL_WEBSITE_CONTEXT_V53 --}}
@php
    /*
     * Canonical Core layout website context.
     *
     * Internal CMS pages normally receive $website directly.
     * User-facing Core pages may not, so resolve the same Website
     * before any title/header/sidebar code attempts to use it.
     */
    if (!isset($website) || !$website) {
        $website = request()->user()?->websites()->first();
    }

    if (!$website) {
        $host = strtolower((string) request()->getHost());
        $subdomain = explode('.', $host)[0] ?? null;

        if ($subdomain) {
            $website = \App\Models\Website::query()
                ->where('subdomain', $subdomain)
                ->first();
        }
    }

    if (!$website) {
        throw new \RuntimeException(
            'Unable to resolve the current Core website.'
        );
    }
@endphp

{{-- ESUBIZ_CORE_SHELL_SOURCE_OF_TRUTH_V56 --}}
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


<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>
        @yield('title', 'Esubiz Core CMS')
        - {{ $website->name }}
    </title>


    {{-- ESUBIZ_CORE_GLOBAL_FAVICON_V44 --}}
    @php
        /*
         * Core website favicon is authoritative.
         *
         * Site Settings favicon is preferred. The existing corporate
         * theme favicon remains a compatibility fallback.
         */
        $tenantAdminFavicon =
            $settings['website_favicon_path']
                ?? $settings['favicon_path']
                ?? $settings['theme.corporate.favicon_path']
                ?? null;
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

    <script src="https://cdn.tailwindcss.com"></script>

    @stack('head')

    <style>

        * {
            box-sizing: border-box;
        }

        html,
        body {
            margin: 0;
            min-height: 100%;
        }

        body {
            background: #f1f5f9;
        }

        #tenantCmsSidebar::-webkit-scrollbar {
            width: 5px;
        }

        #tenantCmsSidebar::-webkit-scrollbar-thumb {
            background: rgba(255,255,255,.18);
            border-radius: 999px;
        }

        details > summary {
            list-style: none;
        }

        details > summary::-webkit-details-marker {
            display: none;
        }

        details[open] .menu-chevron {
            transform: rotate(180deg);
        }

    </style>

</head>


<body class="min-h-screen text-slate-900">


{{-- =========================================================
     MOBILE OVERLAY
========================================================= --}}

<div
    id="tenantCmsOverlay"
    class="fixed inset-0 z-40 hidden bg-slate-950/50 lg:hidden"
></div>


{{-- =========================================================
     PERMANENT SIDEBAR
========================================================= --}}

@include(
    'tenant.admin.partials.sidebar',
    ['website' => $website]
)


{{-- =========================================================
     PERMANENT HEADER
========================================================= --}}

{{-- ESUBIZ_CORE_CANONICAL_HEADER_INCLUDE_V58 --}}
@include('tenant.admin.partials.header')


{{-- =========================================================
     PAGE CONTENT
========================================================= --}}

<div
    class="min-h-screen pt-[72px] lg:pl-[280px]"
>

    <main
        class="w-full p-4 sm:p-6 lg:p-8"
    >

        @if(session('success'))

            <div
                class="mb-6 rounded-2xl border border-emerald-200 bg-emerald-50 p-4 font-bold text-emerald-700"
            >
                {{ session('success') }}
            </div>

        @endif


        @if($errors->any())

            <div
                class="mb-6 rounded-2xl border border-red-200 bg-red-50 p-4 text-sm text-red-700"
            >

                <div class="font-black">
                    Please correct the following:
                </div>

                <ul class="mt-2 list-disc pl-5">

                    @foreach($errors->all() as $error)

                        <li>
                            {{ $error }}
                        </li>

                    @endforeach

                </ul>

            </div>

        @endif


        @yield('content')

        @include('tenant.partials.internal-footer')

    </main>

</div>


{{-- =========================================================
     PERMANENT MOBILE NAVIGATION
========================================================= --}}

<script>

document.addEventListener(
    'DOMContentLoaded',
    function () {

        const sidebar =
            document.getElementById(
                'tenantCmsSidebar'
            );

        const overlay =
            document.getElementById(
                'tenantCmsOverlay'
            );

        const menuButton =
            document.getElementById(
                'tenantCmsMenuButton'
            );

        const closeButton =
            document.getElementById(
                'tenantCmsCloseButton'
            );


        const openMenu = function () {

            if (!sidebar || !overlay) {
                return;
            }

            sidebar.classList.remove(
                '-translate-x-full'
            );

            overlay.classList.remove(
                'hidden'
            );

            document.body.classList.add(
                'overflow-hidden'
            );

        };


        const closeMenu = function () {

            if (!sidebar || !overlay) {
                return;
            }

            sidebar.classList.add(
                '-translate-x-full'
            );

            overlay.classList.add(
                'hidden'
            );

            document.body.classList.remove(
                'overflow-hidden'
            );

        };


        if (menuButton) {

            menuButton.addEventListener(
                'click',
                openMenu
            );

        }


        if (closeButton) {

            closeButton.addEventListener(
                'click',
                closeMenu
            );

        }


        if (overlay) {

            overlay.addEventListener(
                'click',
                closeMenu
            );

        }


        document.addEventListener(
            'keydown',
            function (event) {

                if (event.key === 'Escape') {
                    closeMenu();
                }

            }
        );

    }
);

</script>


@stack('scripts')


    {{-- ESUBIZ_GLOBAL_AI_ASSISTANT --}}
    @once
        <x-site-ai.assistant />
    @endonce


    <x-settings.ajax-autosave />
</body>
</html>
