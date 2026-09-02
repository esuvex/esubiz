
{{-- ESUBIZ_RESOURCE_SALES_TRIGGER_DASHBOARD_STYLE_V1 --}}
<style>
    /*
     * Dashboard-only compact Premium sales area.
     * Shared sales-trigger component is intentionally untouched.
     */

    .esubiz-resource-sales-trigger {
        margin-top: 12px;
        width: 100%;
    }

    .esubiz-resource-sales-trigger > * {
        width: 100% !important;
        max-width: 100% !important;
    }

    .esubiz-resource-sales-trigger [class*="rounded"] {
        border-radius: 10px !important;
    }

    .esubiz-resource-sales-trigger [class*="p-"] {
        padding: 10px !important;
    }

    .esubiz-resource-sales-trigger h1,
    .esubiz-resource-sales-trigger h2,
    .esubiz-resource-sales-trigger h3,
    .esubiz-resource-sales-trigger h4,
    .esubiz-resource-sales-trigger h5,
    .esubiz-resource-sales-trigger h6 {
        margin: 0 0 3px !important;
        font-size: 13px !important;
        line-height: 18px !important;
    }

    .esubiz-resource-sales-trigger p {
        margin: 0 0 7px !important;
        font-size: 12px !important;
        line-height: 17px !important;
    }

    .esubiz-resource-sales-trigger button,
    .esubiz-resource-sales-trigger a {
        min-height: 0 !important;
        padding: 6px 10px !important;
        font-size: 11px !important;
        line-height: 15px !important;
        border-radius: 7px !important;
    }

    /*
     * ESUBIZ_RESOURCE_SALES_TRIGGER_ICON_HIDE_V1
     *
     * Dashboard resource cards do not need the generic
     * Premium Feature globe/icon.
     */
    .esubiz-resource-sales-trigger svg {
        display: none !important;
    }

    .esubiz-resource-sales-trigger [class*="w-"][class*="h-"]:has(svg) {
        display: none !important;
    }

    .esubiz-resource-sales-trigger [class*="gap-"] {
        gap: 7px !important;
    }

    /*
     * ESUBIZ_RESOURCE_SALES_TRIGGER_STACK_FIX_V1
     *
     * Keep Premium content and CTA fully inside narrow
     * Dashboard resource cards on desktop and mobile.
     */
    .esubiz-resource-sales-trigger [class*="flex"] {
        flex-direction: column !important;
        align-items: stretch !important;
    }

    .esubiz-resource-sales-trigger [class*="justify-"] {
        justify-content: flex-start !important;
    }

    .esubiz-resource-sales-trigger [class*="items-"] {
        align-items: stretch !important;
    }

    .esubiz-resource-sales-trigger button,
    .esubiz-resource-sales-trigger a {
        display: inline-flex !important;
        width: auto !important;
        max-width: 100% !important;
        align-self: flex-start !important;
        justify-content: center !important;
        white-space: normal !important;
        overflow-wrap: anywhere !important;
        text-align: left !important;
    }

    .esubiz-resource-sales-trigger * {
        min-width: 0 !important;
        max-width: 100% !important;
        box-sizing: border-box !important;
    }

    /*
     * ESUBIZ_RESOURCE_SALES_BADGE_POSITION_FIX_V1
     *
     * Keep the PRO FEATURE badge in normal document flow so
     * it cannot cover the Admin-configured Premium title.
     */
    .esubiz-resource-sales-trigger [class*="absolute"] {
        position: static !important;
        inset: auto !important;
        top: auto !important;
        right: auto !important;
        bottom: auto !important;
        left: auto !important;
        transform: none !important;
    }

    .esubiz-resource-sales-trigger [class*="uppercase"] {
        align-self: flex-start !important;
        width: auto !important;
        margin: 0 0 6px 0 !important;
        white-space: nowrap !important;
    }
</style>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>
        Dashboard - {{ $website->name }}
    </title>


    {{-- ESUBIZ_TENANT_ADMIN_FAVICON_V1 --}}
    @php
        $tenantAdminFavicon =
            $settings['theme.corporate.favicon_path']
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

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

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

        [x-cloak] {
            display: none !important;
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


<body class="text-slate-900">


{{-- =========================================================
     MOBILE OVERLAY
========================================================= --}}

<div
    id="tenantCmsOverlay"
    class="fixed inset-0 z-40 hidden bg-slate-950/50 lg:hidden"
></div>


{{-- =========================================================
     SIDEBAR
========================================================= --}}

@include(
    'tenant.admin.partials.sidebar',
    ['website' => $website]
)


{{-- =========================================================
     HEADER
========================================================= --}}

<header
    class="fixed left-0 right-0 top-0 z-30 h-[72px] border-b border-slate-200 bg-white lg:left-[280px]"
>

    <div
        class="flex h-full items-center justify-between gap-4 px-4 sm:px-6 lg:px-8"
    >

        <div
            class="flex min-w-0 items-center gap-3"
        >

            <button
                type="button"
                id="tenantCmsMenuButton"
                class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl border border-slate-200 bg-white lg:hidden"
            >
                ☰
            </button>


            <div class="min-w-0">

                <div
                    class="hidden text-[10px] font-black uppercase tracking-[.18em] text-blue-600 sm:block"
                >
                    Esubiz Core CMS
                </div>

                <div
                    class="truncate text-lg font-black"
                >
                    {{ $settings['website_name']
                        ?? $website->name }}
                </div>

            </div>

        </div>


        <div class="flex items-center gap-2">


            <a
                href="/"
                target="_blank"
                rel="noopener noreferrer"
                class="flex h-10 items-center justify-center rounded-xl bg-blue-600 px-4 text-sm font-bold text-white"
            >
                <span class="hidden sm:inline">
                    View Website
                </span>

                <span class="sm:ml-2">
                    ↗
                </span>
            </a>


            {{-- ESUBIZ_TENANT_PROFILE_MENU_V1 --}}
            @php
                $tenantProfileUser = auth()->user();

                $tenantProfileName =
                    $tenantProfileUser->name
                    ?? 'Account';

                $tenantProfilePhoto =
                    $tenantProfileUser->profile_photo_url
                    ?? $tenantProfileUser->avatar_url
                    ?? null;

                $tenantProfileInitial =
                    strtoupper(
                        substr(
                            trim($tenantProfileName),
                            0,
                            1
                        )
                    );

                $tenantSettingsUrl = route(
                    'tenant.cms.dashboard',
                    ['subdomain' => $website->subdomain]
                ) . '#settings';

                $tenantProfileUrl = route(
                    'tenant.cms.dashboard',
                    ['subdomain' => $website->subdomain]
                ) . '#profile';

                $esubizSupportUrl =
                    rtrim(config('app.url'), '/');
            @endphp

            <div
                class="relative"
                data-tenant-profile-menu
            >
                <button
                    type="button"
                    class="flex h-10 items-center gap-2 rounded-xl border border-slate-200 bg-white px-2 sm:px-3"
                    data-tenant-profile-button
                    aria-expanded="false"
                    aria-label="Open profile menu"
                >
                    @if($tenantProfilePhoto)

                        <img
                            src="{{ $tenantProfilePhoto }}"
                            alt="{{ $tenantProfileName }}"
                            class="h-7 w-7 rounded-full object-cover"
                        >

                    @else

                        <span
                            class="flex h-7 w-7 items-center justify-center rounded-full bg-blue-600 text-xs font-black text-white"
                        >
                            {{ $tenantProfileInitial ?: 'A' }}
                        </span>

                    @endif

                    <span
                        class="hidden max-w-[130px] truncate text-sm font-bold text-slate-700 sm:block"
                    >
                        {{ $tenantProfileName }}
                    </span>

                    <span
                        class="text-xs text-slate-400"
                    >
                        ▾
                    </span>
                </button>


                <div
                    class="absolute right-0 top-full z-50 mt-2 hidden w-64 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-xl"
                    data-tenant-profile-dropdown
                >
                    <a
                        href="{{ $tenantProfileUrl }}"
                        class="flex items-center gap-3 border-b border-slate-100 px-4 py-4 hover:bg-slate-50"
                    >
                        @if($tenantProfilePhoto)

                            <img
                                src="{{ $tenantProfilePhoto }}"
                                alt="{{ $tenantProfileName }}"
                                class="h-10 w-10 rounded-full object-cover"
                            >

                        @else

                            <span
                                class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-blue-600 text-sm font-black text-white"
                            >
                                {{ $tenantProfileInitial ?: 'A' }}
                            </span>

                        @endif

                        <span class="min-w-0">
                            <span
                                class="block truncate text-sm font-black text-slate-900"
                            >
                                {{ $tenantProfileName }}
                            </span>

                            <span
                                class="block text-xs font-semibold text-blue-600"
                            >
                                Profile
                            </span>
                        </span>
                    </a>


                    <div class="py-2">

                        <a
                            href="{{ $esubizSupportUrl }}"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="flex items-center gap-3 px-4 py-3 text-sm font-bold text-slate-700 hover:bg-slate-50"
                        >
                            <span class="flex w-5 justify-center">
                                ?
                            </span>

                            <span>
                                Esubiz Support
                            </span>
                        </a>


                        <a
                            href="{{ $tenantSettingsUrl }}"
                            class="flex items-center gap-3 px-4 py-3 text-sm font-bold text-slate-700 hover:bg-slate-50"
                        >
                            <span class="flex w-5 justify-center">
                                ⚙
                            </span>

                            <span>
                                Settings
                            </span>
                        </a>

                    </div>


                    <div class="border-t border-slate-100 p-2">

                        <form
                            method="POST"
                            action="{{ route(
                                'tenant.cms.logout',
                                [
                                    'subdomain' =>
                                        $website->subdomain
                                ]
                            ) }}"
                        >
                            @csrf

                            <button
                                type="submit"
                                class="flex w-full items-center gap-3 rounded-xl px-3 py-3 text-left text-sm font-bold text-red-600 hover:bg-red-50"
                            >
                                <span class="flex w-5 justify-center">
                                    ↪
                                </span>

                                <span>
                                    Logout
                                </span>
                            </button>
                        </form>

                    </div>
                </div>
            </div>

            <script>
                /*
                 * ESUBIZ_TENANT_PROFILE_MENU_JS_V1
                 */
                document.addEventListener(
                    'DOMContentLoaded',
                    function () {
                        document
                            .querySelectorAll(
                                '[data-tenant-profile-menu]'
                            )
                            .forEach(function (menu) {
                                const button =
                                    menu.querySelector(
                                        '[data-tenant-profile-button]'
                                    );

                                const dropdown =
                                    menu.querySelector(
                                        '[data-tenant-profile-dropdown]'
                                    );

                                if (!button || !dropdown) {
                                    return;
                                }

                                button.addEventListener(
                                    'click',
                                    function (event) {
                                        event.stopPropagation();

                                        const isHidden =
                                            dropdown.classList.contains(
                                                'hidden'
                                            );

                                        document
                                            .querySelectorAll(
                                                '[data-tenant-profile-dropdown]'
                                            )
                                            .forEach(function (item) {
                                                item.classList.add(
                                                    'hidden'
                                                );
                                            });

                                        if (isHidden) {
                                            dropdown.classList.remove(
                                                'hidden'
                                            );

                                            button.setAttribute(
                                                'aria-expanded',
                                                'true'
                                            );
                                        } else {
                                            button.setAttribute(
                                                'aria-expanded',
                                                'false'
                                            );
                                        }
                                    }
                                );

                                dropdown.addEventListener(
                                    'click',
                                    function (event) {
                                        event.stopPropagation();
                                    }
                                );
                            });

                        document.addEventListener(
                            'click',
                            function () {
                                document
                                    .querySelectorAll(
                                        '[data-tenant-profile-dropdown]'
                                    )
                                    .forEach(function (dropdown) {
                                        dropdown.classList.add(
                                            'hidden'
                                        );
                                    });

                                document
                                    .querySelectorAll(
                                        '[data-tenant-profile-button]'
                                    )
                                    .forEach(function (button) {
                                        button.setAttribute(
                                            'aria-expanded',
                                            'false'
                                        );
                                    });
                            }
                        );
                    }
                );
            </script>

        </div>

    </div>

</header>


{{-- =========================================================
     MAIN APPLICATION AREA
========================================================= --}}

<div
    class="min-h-screen pt-[72px] lg:pl-[280px]"
>

    <main
        class="w-full p-4 sm:p-6 lg:p-8"
    >


        {{-- Heading --}}

        <div
            class="mb-7 flex flex-col gap-2"
        >

            <h1
                class="text-3xl font-black tracking-tight lg:text-4xl"
            >
                Dashboard
            </h1>

            <p
                class="text-sm text-slate-500 sm:text-base"
            >
                Overview of your website activity and business performance.
            </p>

        </div>


        {{-- =================================================
             QUICK OVERVIEW
        ================================================== --}}

        <section
            class="mb-6 rounded-3xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6"
        >

            <div
                class="flex flex-col gap-2 sm:flex-row sm:items-start sm:justify-between"
            >

                <div>

                    <div
                        class="text-xs font-black uppercase tracking-[.14em] text-blue-600"
                    >
                        Quick Overview
                    </div>

                    <h2
                        class="mt-2 text-xl font-black"
                    >
                        {{ $website->name }}
                    </h2>

                    <p
                        class="mt-1 text-sm text-slate-500"
                    >
                        Your website and Core CMS at a glance.
                    </p>

                </div>


                <span
                    class="w-fit rounded-full bg-emerald-100 px-3 py-1 text-xs font-black text-emerald-700"
                >
                    Active
                </span>

            </div>


            <div
                class="mt-6 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4"
            >

                <div
                    class="rounded-2xl bg-slate-50 p-4"
                >
                    <div
                        class="text-[11px] font-black uppercase tracking-wide text-slate-400"
                    >
                        Website
                    </div>

                    <div
                        class="mt-2 font-black text-slate-900"
                    >
                        {{ $website->name }}
                    </div>
                </div>


                <div
                    class="rounded-2xl bg-slate-50 p-4"
                >
                    <div
                        class="text-[11px] font-black uppercase tracking-wide text-slate-400"
                    >
                        CMS
                    </div>

                    <div
                        class="mt-2 font-black text-slate-900"
                    >
                        Esubiz Core
                    </div>
                </div>


                <div
                    class="rounded-2xl bg-slate-50 p-4"
                >
                    <div
                        class="text-[11px] font-black uppercase tracking-wide text-slate-400"
                    >
                        Website Type
                    </div>

                    <div
                        class="mt-2 font-black text-slate-900"
                    >
                        {{ ucfirst(
                            (string) (
                                $settings['website_type']
                                ?? $website->type
                                ?? 'Core'
                            )
                        ) }}
                    </div>
                </div>


                <div
                    class="rounded-2xl bg-slate-50 p-4"
                >
                    <div
                        class="text-[11px] font-black uppercase tracking-wide text-slate-400"
                    >
                        Address
                    </div>

                    <div
                        class="mt-2 truncate font-black text-blue-600"
                    >
                        {{ $website->subdomain }}.esubiz.com
                    </div>
                </div>

            </div>

        </section>


        {{-- =================================================
             MODULE-AWARE STATS
        ================================================== --}}

        <section
            class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4"
        >

            @foreach($dashboardStats as $stat)

                @php
                    /*
                     * ESUBIZ_GENERIC_RESOURCE_CARD_DISPLAY_GATE_V1
                     *
                     * Normal dashboard statistics remain unchanged.
                     * Resource cards obey Central Admin Resource Settings.
                     */
                    $showDashboardStat = true;

                    if (!empty($stat['resource'])) {
                        $dashboardStatResourceKey =
                            $stat['resource_key']
                            ?? $stat['key']
                            ?? $stat['icon']
                            ?? null;

                        $dashboardStatResourceSetting =
                            $dashboardStatResourceKey
                                ? \Illuminate\Support\Facades\DB::table(
                                    'core_resource_settings'
                                )
                                    ->where(
                                        'resource_key',
                                        $dashboardStatResourceKey
                                    )
                                    ->first()
                                : null;

                        $dashboardStatDeploymentVisible =
                            $dashboardStatResourceSetting
                            && (bool) (
                                $dashboardStatResourceSetting
                                    ->is_active
                                ?? false
                            )
                            && (
                                !empty($website->is_off_server)
                                    ? (bool) (
                                        $dashboardStatResourceSetting
                                            ->off_server_visible
                                        ?? false
                                    )
                                    : (bool) (
                                        $dashboardStatResourceSetting
                                            ->saas_visible
                                        ?? false
                                    )
                            );

                        $dashboardStatPercentage = (float) (
                            $stat['percentage'] ?? 0
                        );

                        $dashboardStatDisplayThreshold = (float) (
                            $dashboardStatResourceSetting
                                ->dashboard_threshold_percentage
                            ?? 100
                        );

                        $showDashboardStat =
                            $dashboardStatDeploymentVisible
                            && $dashboardStatPercentage
                                >= $dashboardStatDisplayThreshold;
                    }
                @endphp

                @if($showDashboardStat)

                <div
                    class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm"
                >

                    <div
                        class="flex items-start justify-between gap-4"
                    >

                        <div>

                            <div
                                class="text-xs font-black uppercase tracking-wide text-slate-400"
                            >
                                {{ $stat['label'] }}
                            </div>

                            <div
                                class="mt-3 break-words text-2xl font-black text-slate-950"
                            >
                                {{ $stat['value'] }}
                            </div>

                            @if(!empty($stat['resource']))

                                <div class="mt-3">

                                    <div
                                        class="mb-2 flex items-center justify-between gap-3 text-[11px] font-bold text-slate-500"
                                    >
                                        <span>
                                            {{ $stat['resource_detail'] ?? '' }}
                                        </span>

                                        <span>
                                            {{ number_format(
                                                (float) ($stat['percentage'] ?? 0),
                                                1
                                            ) }}%
                                        </span>
                                    </div>

                                    @php
                                        /*
                                         * ESUBIZ_RESOURCE_METER_THRESHOLD_UI_V1
                                         */
                                        $resourceStatus =
                                            $stat['resource_status']
                                            ?? 'normal';

                                        $resourceBarClass =
                                            $resourceStatus === 'critical'
                                                ? 'bg-red-600'
                                                : (
                                                    $resourceStatus === 'warning'
                                                        ? 'bg-amber-500'
                                                        : 'bg-blue-600'
                                                );
                                    @endphp

                                    <div
                                        class="h-2 overflow-hidden rounded-full bg-slate-100"
                                    >
                                        <div
                                            class="h-full rounded-full {{ $resourceBarClass }} transition-all"
                                            style="width: {{ min(
                                                100,
                                                max(
                                                    0,
                                                    (float) ($stat['percentage'] ?? 0)
                                                )
                                            ) }}%"
                                        ></div>
                                    </div>






                                </div>

                            @endif

                        </div>


                        <div
                            class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-blue-50 text-lg font-black text-blue-600"
                        >
                            @switch($stat['icon'])

                                @case('storage')
                                    ▣
                                    @break

                                @case('bandwidth')
                                    ↕
                                    @break

                                @case('orders')
                                    ≡
                                    @break

                                @case('sales')
                                    ₦
                                    @break

                                @case('products')
                                    □
                                    @break

                                @case('clients')
                                    ◎
                                    @break

                                @case('staff')
                                    ♙
                                    @break

                                @case('tickets')
                                    ✉
                                    @break

                                @case('bookings')
                                    ◫
                                    @break

                                @case('users')
                                    ◉
                                    @break

                                @default
                                    ▦

                            @endswitch
                        </div>

                    </div>

                @if(
                    !empty($stat['resource'])
                    && !empty(
                        $stat['resource_key']
                        ?? $stat['key']
                        ?? $stat['icon']
                        ?? null
                    )
                )
                    @php
                        /*
                         * ESUBIZ_RESOURCE_CARD_SALES_TRIGGER_V2
                         *
                         * Sales Trigger belongs inside the resource card.
                         * No Add-on/resource names are hardcoded.
                         */
                        $resourceSalesKey =
                            $stat['resource_key']
                            ?? $stat['key']
                            ?? $stat['icon'];
                    @endphp

                    {{-- ESUBIZ_COMPACT_RESOURCE_SALES_TRIGGER_V1 --}}
                    <div class="esubiz-resource-sales-trigger">
                        <x-core-addon-sales-triggers
                            location="dashboard"
                            :website="$website"
                            :context="[
                                'resources' => [
                                    $resourceSalesKey => [
                                        'percentage' => (float) (
                                            $stat['percentage'] ?? 0
                                        ),
                                        'used_percentage' => (float) (
                                            $stat['percentage'] ?? 0
                                        ),
                                        'usage_percentage' => (float) (
                                            $stat['percentage'] ?? 0
                                        ),
                                        'status' =>
                                            $stat['resource_status']
                                            ?? 'normal',
                                    ],
                                ],
                            ]"
                        />
                    </div>
                @endif

                </div>

                @endif

            @endforeach

            {{-- ESUBIZ_UNIVERSAL_ADDON_PLACEMENT_DASHBOARD_V1 --}}
            @php
                /*
                 * Universal Dashboard Add-on context.
                 *
                 * ESUBIZ_UNIVERSAL_DASHBOARD_RESOURCE_DISPLAY_GATE_V1
                 *
                 * No resource/Add-on is hardcoded here.
                 *
                 * A resource is exposed to Dashboard sales triggers only when:
                 *  - Central Admin has an active resource setting;
                 *  - it is visible for this deployment type;
                 *  - its configured Dashboard display threshold is reached.
                 *
                 * The Add-on's own Dashboard sales threshold/limit is then
                 * evaluated independently by the generic trigger resolver.
                 */
                $addonDashboardResources = [];

                $dashboardDeploymentType =
                    !empty($website->is_off_server)
                        ? 'off_server'
                        : 'saas';

                $dashboardResourceSettings = \Illuminate\Support\Facades\DB::table(
                    'core_resource_settings'
                )
                    ->where('is_active', 1)
                    ->get()
                    ->keyBy('resource_key');

                foreach (($dashboardStats ?? []) as $dashboardResourceStat) {
                    $dashboardResourceKey =
                        $dashboardResourceStat['resource_key']
                        ?? $dashboardResourceStat['key']
                        ?? $dashboardResourceStat['icon']
                        ?? null;

                    if (empty($dashboardResourceKey)) {
                        continue;
                    }

                    $dashboardResourceSetting =
                        $dashboardResourceSettings->get(
                            $dashboardResourceKey
                        );

                    if (!$dashboardResourceSetting) {
                        continue;
                    }

                    $dashboardResourceVisible =
                        $dashboardDeploymentType === 'off_server'
                            ? (bool) (
                                $dashboardResourceSetting->off_server_visible
                                ?? false
                            )
                            : (bool) (
                                $dashboardResourceSetting->saas_visible
                                ?? false
                            );

                    if (!$dashboardResourceVisible) {
                        continue;
                    }

                    $dashboardResourcePercentage = (float) (
                        $dashboardResourceStat['percentage'] ?? 0
                    );

                    $dashboardDisplayThreshold = (float) (
                        $dashboardResourceSetting
                            ->dashboard_threshold_percentage
                        ?? 100
                    );

                    if (
                        $dashboardResourcePercentage
                        < $dashboardDisplayThreshold
                    ) {
                        continue;
                    }

                    $addonDashboardResources[$dashboardResourceKey] = [
                        'percentage' =>
                            $dashboardResourcePercentage,

                        'used_percentage' =>
                            $dashboardResourcePercentage,

                        'usage_percentage' =>
                            $dashboardResourcePercentage,

                        'status' =>
                            $dashboardResourceStat[
                                'resource_status'
                            ]
                            ?? 'normal',
                    ];
                }
            @endphp


        </section>


        {{-- =================================================
             INSTALLED MODULES
        ================================================== --}}

        <section
            class="mt-6 rounded-3xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6"
        >

            <div>

                <h2
                    class="text-xl font-black"
                >
                    Installed Modules
                </h2>

                <p
                    class="mt-1 text-sm text-slate-500"
                >
                    Enabled modules and their activity will appear here automatically.
                </p>

            </div>


            <div
                class="mt-5 rounded-2xl border border-dashed border-slate-200 bg-slate-50 p-5"
            >

                <p
                    class="text-sm leading-6 text-slate-500"
                >
                    Ecommerce, Hotel, Restaurant and other installed
                    module summaries will be loaded here from the
                    tenant module registry.
                </p>

            </div>

        </section>


        {{-- =================================================
             FINANCIAL CHART
        ================================================== --}}

        <section
            class="mt-6 rounded-3xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6"
        >

            <div
                class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between"
            >

                <div>

                    <h2
                        class="text-xl font-black"
                    >
                        Income vs Expenses
                    </h2>

                    <p
                        class="mt-1 text-sm text-slate-500"
                    >
                        Financial performance for the last 7 days.
                    </p>

                </div>


                <span
                    class="w-fit rounded-full bg-slate-100 px-3 py-1 text-xs font-bold text-slate-500"
                >
                    Last 7 days
                </span>

            </div>


            <div
                class="mt-6 h-[300px] w-full sm:h-[360px]"
            >

                <canvas
                    id="incomeExpenseChart"
                ></canvas>

            </div>

        </section>



<script>

document.addEventListener(
    'DOMContentLoaded',
    function () {

        /*
         * Mobile navigation
         */
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


        /*
         * Income / expense chart
         */
        const chartElement =
            document.getElementById(
                'incomeExpenseChart'
            );

        if (
            chartElement
            && typeof Chart !== 'undefined'
        ) {

            new Chart(
                chartElement,
                {
                    type: 'line',

                    data: {
                        labels:
                            @json($chartLabels),

                        datasets: [
                            {
                                label: 'Income',
                                data:
                                    @json($chartIncome),
                                borderColor:
                                    '#2563eb',
                                backgroundColor:
                                    'rgba(37,99,235,.08)',
                                tension: .35,
                                fill: true
                            },
                            {
                                label: 'Expenses',
                                data:
                                    @json($chartExpenses),
                                borderColor:
                                    '#ef4444',
                                backgroundColor:
                                    'rgba(239,68,68,.05)',
                                tension: .35,
                                fill: true
                            }
                        ]
                    },

                    options: {
                        responsive: true,
                        maintainAspectRatio: false,

                        interaction: {
                            mode: 'index',
                            intersect: false
                        },

                        plugins: {
                            legend: {
                                position: 'top',
                                align: 'end'
                            }
                        },

                        scales: {
                            y: {
                                beginAtZero: true,

                                grid: {
                                    color:
                                        'rgba(148,163,184,.15)'
                                }
                            },

                            x: {
                                grid: {
                                    display: false
                                }
                            }
                        }
                    }
                }
            );

        }

    }
);

</script>

</body>
</html>
