{{--
|--------------------------------------------------------------------------
| Canonical Tenant Core CMS Sidebar
|--------------------------------------------------------------------------
|
| This is the ONLY tenant CMS sidebar source.
| Do not create page-specific sidebar copies.
|
--}}
        {{-- ESUBIZ_CORE_ROLE_AWARE_SIDEBAR_V51 --}}
    @php
        $coreSidebarPermissionService = app(
            \App\Services\Core\CorePermissionService::class
        );

        $coreSidebarRoles =
            $coreSidebarPermissionService->roles();

        $coreSidebarUserOnly =
            count($coreSidebarRoles) === 1
            && in_array('user', $coreSidebarRoles, true);

        $coreSidebarLanding =
            $coreSidebarPermissionService->internalLandingTarget()
            ?? '/admin/dashboard';
    @endphp

<aside
    id="tenantCmsSidebar"
    class="fixed inset-y-0 left-0 z-50 w-[280px] -translate-x-full overflow-y-auto border-r border-white/10 text-white shadow-xl transition-transform duration-300 lg:translate-x-0"
    style="background:linear-gradient(180deg,#0b1739 0%,#10245a 100%);"
>

    {{-- Mobile close --}}

    <div
        class="flex justify-end px-4 pt-4 lg:hidden"
    >

        <button
            type="button"
            id="tenantCmsCloseButton"
            class="flex h-10 w-10 items-center justify-center rounded-xl border border-white/10 bg-white/5 text-xl text-white hover:bg-white/10"
            aria-label="Close navigation"
        >
            ×
        </button>

    </div>


    {{-- Website identity --}}

    <div
        class="border-b border-white/10 px-5 py-5"
    >

        {{-- ESUBIZ_CORE_UNIFIED_SIDEBAR_BRAND_V48 --}}
{{-- ESUBIZ_CORE_SIDEBAR_SOURCE_OF_TRUTH_V56 --}}
{{-- ESUBIZ_CORE_SINGLE_BRAND_SOURCE_V54 --}}
        @php
            /*
             * Canonical Core sidebar branding.
             *
             * The homepage/footer white-variant logo is the
             * authoritative internal Core logo.
             *
             * No hardcoded Core CMS text or website-name copy
             * belongs beside the logo.
             */
            /*
             * ESUBIZ_CORE_INTERNAL_WHITE_LOGO_AUTHORITY_V1
             *
             * Explicit theme footer logo wins.
             * Otherwise use Core's generated white variant.
             * Main logo is the final legacy/processing fallback.
             */
            /*
             * ESUBIZ_CORE_INTERNAL_LOGO_EMPTY_FALLBACK_V2
             *
             * Empty branding settings mean "inherit", not "override".
             * first() therefore selects the first non-empty candidate instead
             * of allowing an empty theme/footer value to block the canonical
             * generated white logo.
             */
            $coreSidebarLogo =
                collect([
                    $settings['theme.corporate.footer_logo_path'] ?? null,
                    data_get(
                        $settings ?? [],
                        'theme.corporate.footer_logo_path'
                    ),
                    $settings['footer_logo_path'] ?? null,
                    data_get(
                        $settings ?? [],
                        'footer_logo_path'
                    ),
                    $settings['site_logo_white_path'] ?? null,
                    data_get(
                        $settings ?? [],
                        'site_logo_white_path'
                    ),
                    $settings['site_logo_path'] ?? null,
                    data_get(
                        $settings ?? [],
                        'site_logo_path'
                    ),
                ])->first(
                    static fn ($value) =>
                        trim((string) $value) !== ''
                );

            $coreSidebarLogoUrl =
                !empty($coreSidebarLogo)
                    ? request()->getSchemeAndHttpHost()
                        . '/media/'
                        . implode(
                            '/',
                            array_map(
                                'rawurlencode',
                                explode(
                                    '/',
                                    ltrim(
                                        $coreSidebarLogo,
                                        '/'
                                    )
                                )
                            )
                        )
                    : null;
        @endphp

        <a
            href="{{ $coreSidebarLanding }}"
            class="flex min-h-[60px] items-center"
            aria-label="{{ $settings['website_name'] ?? $website->name }}"
        >
            @if($coreSidebarLogoUrl)

                <img
                    src="{{ $coreSidebarLogoUrl }}"
                    alt="{{ $settings['website_name'] ?? $website->name }}"
                    width="180"
                    height="60"
                    class="block h-[60px] w-[180px] max-w-full object-contain object-left"
                >

            @else

                <div
                    class="flex h-[60px] w-[180px] max-w-full items-center text-xl font-black text-white"
                >
                    {{ $settings['website_name']
                        ?? $website->name }}
                </div>

            @endif
        </a>




    </div>




    <nav class="space-y-1 px-3 py-5">


        {{-- Dashboard --}}

        <a
            href="{{ $coreSidebarLanding }}"
            class="flex items-center gap-3 rounded-xl bg-blue-600 px-4 py-3 text-sm font-black text-white"
        >
            <span>⌂</span>
            Dashboard
        </a>

        {{-- ESUBIZ_CORE_BRANCHES_MENU_V66 --}}
        @if(!$coreSidebarUserOnly)
            <a
                href="#"
                class="flex items-center gap-3 rounded-xl px-4 py-3 text-sm font-bold text-blue-100 hover:bg-white/10 hover:text-white"
            >
                {{-- ESUBIZ_CORE_BRANCHES_ICON_V67 --}}
                <span
                    class="inline-flex h-5 w-5 shrink-0 items-center justify-center"
                    aria-hidden="true"
                >
                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke-width="1.8"
                        stroke="currentColor"
                        class="h-5 w-5"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            d="M3.75 21h16.5M5.25 21V4.5A1.5 1.5 0 0 1 6.75 3h7.5a1.5 1.5 0 0 1 1.5 1.5V21m0-12h2.25a.75.75 0 0 1 .75.75V21M8.25 7.5h1.5m2.25 0h1.5m-5.25 3h1.5m2.25 0h1.5m-5.25 3h1.5m2.25 0h1.5m-3.75 7.5v-3.75h2.25V21"
                        />
                    </svg>
                </span>

                <span>Branches</span>
            </a>
        @endif




        {{-- ESUBIZ_CORE_USER_NAV_V51 --}}
        @if($coreSidebarUserOnly)

        {{-- ESUBIZ_CORE_USER_NAV_COMPLETE_V62 --}}
        @php
            /*
             * Normal Core User navigation.
             *
             * Dashboard remains the shared first menu above this
             * role split.
             *
             * CRM and Modules expose only features assigned to the
             * logged-in Core User and only when a User-safe URL is
             * available.
             */

            $coreUserFeatures = collect();

            try {
                $coreUserFeatures = collect(
                    app(
                        \App\Services\Core\CoreFeatureRegistry::class
                    )->availableFeatures()
                );
            } catch (\Throwable $e) {
                $coreUserFeatures = collect();
            }

            $coreUserFeatureAllowed = function ($feature)
                use ($coreSidebarPermissionService) {

                $permission =
                    data_get(
                        $feature,
                        'permission'
                    )
                    ?? data_get(
                        $feature,
                        'navigation.permission'
                    );

                if (
                    $permission
                    && !$coreSidebarPermissionService->can(
                        $permission
                    )
                ) {
                    return false;
                }

                if (
                    data_get(
                        $feature,
                        'available',
                        true
                    ) === false
                ) {
                    return false;
                }

                if (
                    data_get(
                        $feature,
                        'navigation.enabled',
                        true
                    ) === false
                ) {
                    return false;
                }

                return true;
            };


            /*
             * CRM FEATURES
             */
            $coreUserCrmFeatures =
                $coreUserFeatures
                    ->filter(
                        function ($feature)
                            use ($coreUserFeatureAllowed) {

                            $sourceType =
                                strtolower(
                                    (string) data_get(
                                        $feature,
                                        'source_type',
                                        ''
                                    )
                                );

                            if (
                                !in_array(
                                    $sourceType,
                                    [
                                        'crm',
                                        'core_crm',
                                    ],
                                    true
                                )
                            ) {
                                return false;
                            }

                            if (
                                !$coreUserFeatureAllowed(
                                    $feature
                                )
                            ) {
                                return false;
                            }

                            /*
                             * Never expose an Admin navigation URL
                             * to a normal Core User.
                             */
                            $userUrl =
                                data_get(
                                    $feature,
                                    'navigation.user_url'
                                )
                                ?? data_get(
                                    $feature,
                                    'user_url'
                                );

                            return !empty($userUrl);
                        }
                    )
                    ->sortBy(
                        fn ($feature) =>
                            (int) (
                                data_get(
                                    $feature,
                                    'navigation.sort_order'
                                )
                                ?? data_get(
                                    $feature,
                                    'sort_order'
                                )
                                ?? 999
                            )
                    )
                    ->values();


            /*
             * MODULE FEATURES
             */
            $coreUserModuleFeatures =
                $coreUserFeatures
                    ->filter(
                        function ($feature)
                            use ($coreUserFeatureAllowed) {

                            $sourceType =
                                strtolower(
                                    (string) data_get(
                                        $feature,
                                        'source_type',
                                        ''
                                    )
                                );

                            if ($sourceType !== 'module') {
                                return false;
                            }

                            if (
                                !$coreUserFeatureAllowed(
                                    $feature
                                )
                            ) {
                                return false;
                            }

                            $userUrl =
                                data_get(
                                    $feature,
                                    'navigation.user_url'
                                )
                                ?? data_get(
                                    $feature,
                                    'user_url'
                                );

                            return !empty($userUrl);
                        }
                    )
                    ->sortBy(
                        fn ($feature) =>
                            (int) (
                                data_get(
                                    $feature,
                                    'navigation.sort_order'
                                )
                                ?? data_get(
                                    $feature,
                                    'sort_order'
                                )
                                ?? 999
                            )
                    )
                    ->values();


            /*
             * SUPPORT
             *
             * Use the real User ticket route when available.
             * Otherwise keep the menu in place with # until the
             * User ticket page is wired.
             */
            $coreUserSupportUrl =
                \Illuminate\Support\Facades\Route::has(
                    'user.tickets.index'
                )
                    ? route('user.tickets.index')
                    : '#';


            /*
             * WALLET
             *
             * Core Admin remains authoritative.
             * Both flat and nested settings formats are supported.
             */
            $coreUserWalletEnabledRaw =
                $settings['wallet_enabled']
                    ?? $settings['wallet.enabled']
                    ?? data_get(
                        $settings,
                        'wallet.enabled',
                        false
                    );

            $coreUserWalletEnabled =
                filter_var(
                    $coreUserWalletEnabledRaw,
                    FILTER_VALIDATE_BOOLEAN
                );

            $coreUserWalletUrl =
                \Illuminate\Support\Facades\Route::has(
                    'wallet.index'
                )
                    ? route('wallet.index')
                    : url('/wallet');


            /*
             * REFERRAL
             *
             * Use a User route when one exists. Until then the
             * canonical menu remains visible with #.
             */
            $coreUserReferralUrl =
                \Illuminate\Support\Facades\Route::has(
                    'user.referrals.index'
                )
                    ? route(
                        'user.referrals.index'
                    )
                    : '#';
        @endphp


        {{-- CRM --}}
        <details
            class="group"
            @if(
                request()->routeIs('user.crm.*')
            )
                open
            @endif
        >
            <summary
                class="flex cursor-pointer list-none items-center justify-between rounded-xl px-3 py-2.5 text-sm font-medium text-slate-300 transition hover:bg-white/10 hover:text-white"
            >
                <span class="flex items-center gap-3">
                    <span>CRM</span>
                </span>

                <span
                    class="transition group-open:rotate-180"
                >
                    ▾
                </span>
            </summary>

            <div class="mt-1 space-y-1 pl-4">

                <a
                    href="{{ route('user.crm.index') }}"
                    class="block rounded-lg px-3 py-2 text-sm text-slate-300 transition hover:bg-white/10 hover:text-white"
                >
                    Overview
                </a>

                @foreach(
                    $coreUserCrmFeatures
                    as $coreUserCrmFeature
                )
                    @php
                        $coreUserCrmUrl =
                            data_get(
                                $coreUserCrmFeature,
                                'navigation.user_url'
                            )
                            ?? data_get(
                                $coreUserCrmFeature,
                                'user_url'
                            );

                        $coreUserCrmLabel =
                            data_get(
                                $coreUserCrmFeature,
                                'navigation.label'
                            )
                            ?? data_get(
                                $coreUserCrmFeature,
                                'label'
                            )
                            ?? data_get(
                                $coreUserCrmFeature,
                                'name'
                            )
                            ?? 'CRM';
                    @endphp

                    <a
                        href="{{ $coreUserCrmUrl }}"
                        class="block rounded-lg px-3 py-2 text-sm text-slate-300 transition hover:bg-white/10 hover:text-white"
                    >
                        {{ $coreUserCrmLabel }}
                    </a>
                @endforeach
            </div>
        </details>


        {{-- MODULES --}}
        <details class="group">
            <summary
                class="flex cursor-pointer list-none items-center justify-between rounded-xl px-3 py-2.5 text-sm font-medium text-slate-300 transition hover:bg-white/10 hover:text-white"
            >
                <span>Modules</span>

                <span
                    class="transition group-open:rotate-180"
                >
                    ▾
                </span>
            </summary>

            <div class="mt-1 space-y-1 pl-4">

                @forelse(
                    $coreUserModuleFeatures
                    as $coreUserModuleFeature
                )
                    @php
                        $coreUserModuleUrl =
                            data_get(
                                $coreUserModuleFeature,
                                'navigation.user_url'
                            )
                            ?? data_get(
                                $coreUserModuleFeature,
                                'user_url'
                            );

                        $coreUserModuleLabel =
                            data_get(
                                $coreUserModuleFeature,
                                'navigation.label'
                            )
                            ?? data_get(
                                $coreUserModuleFeature,
                                'label'
                            )
                            ?? data_get(
                                $coreUserModuleFeature,
                                'name'
                            )
                            ?? 'Module';
                    @endphp

                    <a
                        href="{{ $coreUserModuleUrl }}"
                        class="block rounded-lg px-3 py-2 text-sm text-slate-300 transition hover:bg-white/10 hover:text-white"
                    >
                        {{ $coreUserModuleLabel }}
                    </a>
                @empty
                    <span
                        class="block px-3 py-2 text-xs text-slate-500"
                    >
                        No assigned module features
                    </span>
                @endforelse

            </div>
        </details>


        {{-- SUPPORT --}}
        <a
            href="{{ $coreUserSupportUrl }}"
            class="flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium text-slate-300 transition hover:bg-white/10 hover:text-white"
        >
            <span>Support</span>
        </a>


        {{-- WALLET --}}
        @if($coreUserWalletEnabled)
            <a
                href="{{ $coreUserWalletUrl }}"
                class="flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium text-slate-300 transition hover:bg-white/10 hover:text-white"
            >
                <span>Wallet</span>
            </a>
        @endif


        {{-- REFERRAL --}}
        <a
            href="{{ $coreUserReferralUrl }}"
            class="flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium text-slate-300 transition hover:bg-white/10 hover:text-white"
        >
            <span>Referral</span>
        </a>

@else

        {{-- Site Management --}}

        <details class="group">

            <summary
                class="flex cursor-pointer items-center justify-between rounded-xl px-4 py-3 text-sm font-bold text-blue-100 hover:bg-white/10"
            >

                <span class="flex items-center gap-3">
                    <span>▦</span>
                    Site Management
                </span>

                <span
                    class="menu-chevron text-xs transition"
                >
                    ▼
                </span>

            </summary>


            <div
                class="ml-4 space-y-1 border-l border-white/10 pl-3"
            >

                <a
                    href="{{ route(
                        'tenant.cms.pages.index',
                        ['subdomain' => $website->subdomain]
                    ) }}"
                    class="block rounded-lg px-3 py-2.5 text-sm text-slate-300 hover:bg-white/10 hover:text-white"
                >
                    Pages
                </a>



                <a
                    href="#"
                    class="block rounded-lg px-3 py-2.5 text-sm text-slate-300 hover:bg-white/10 hover:text-white"
                >
                    Media
                </a>

                <a
                    href="#"
                    class="block rounded-lg px-3 py-2.5 text-sm text-slate-300 hover:bg-white/10 hover:text-white"
                >
                    Menus
                </a>

                <a
                    href="{{ route('tenant.cms.forms.index', ['subdomain' => $website->subdomain]) }}"
                    class="block rounded-lg px-3 py-2.5 text-sm text-slate-300 hover:bg-white/10 hover:text-white {{ request()->routeIs('tenant.cms.forms.*') ? 'bg-white/10 text-white' : '' }}"
                >
                    Forms
                </a>

                {{-- ESUBIZ_CORE_USERS_SIDEBAR_V1 --}}
                @coreCan('users.view')
<a
                    href="{{ route('tenant.cms.users.index', ['subdomain' => $website->subdomain]) }}"
                    class="block rounded-lg px-3 py-2.5 text-sm text-slate-300 hover:bg-white/10 hover:text-white {{ request()->routeIs('tenant.cms.users.*') ? 'bg-white/10 text-white' : '' }}"
                >
                    Users
                </a>

                @endcoreCan

                {{-- ESUBIZ_CORE_QR_MENU_V66 --}}
                <a
                    href="#"
                    class="block rounded-lg px-3 py-2.5 text-sm text-slate-300 hover:bg-white/10 hover:text-white"
                >
                    QR Code
                </a>





                <div
                    class="px-3 pb-1 pt-3 text-[10px] font-black uppercase tracking-[.14em] text-blue-300"
                >
                    Installed Website Products
                </div>


                <a
                    href="#"
                    class="block rounded-lg px-3 py-2.5 text-sm text-slate-300 hover:bg-white/10 hover:text-white"
                >
                    Add-ons
                </a>

                <a
                    href="{{ route('tenant.cms.themes.index', ['subdomain' => $website->subdomain]) }}"
                    class="block rounded-lg px-3 py-2.5 text-sm text-slate-300 hover:bg-white/10 hover:text-white"
                >
                    Themes
                </a>

                <a
                    href="{{ route(
                        'tenant.cms.modules.index',
                        ['subdomain' => $website->subdomain]
                    ) }}"
                    class="block rounded-lg px-3 py-2.5 text-sm text-slate-300 hover:bg-white/10 hover:text-white {{ request()->routeIs('tenant.cms.modules.*') ? 'bg-white/10 text-white' : '' }}"
                >
                    Modules
                </a>

            </div>

        </details>



        {{-- ESUBIZ_CORE_PAYMENT_GATEWAYS_SIDEBAR_V1 --}}
        @coreCanAny(
            'payment_gateways.offline.view',
            'payment_gateways.online.view'
        )
            <details class="group">
                <summary
                    class="flex cursor-pointer items-center justify-between rounded-xl px-4 py-3 text-sm font-bold text-blue-100 hover:bg-white/10"
                >
                    <span class="flex items-center gap-3">
                        <span>▣</span>
                        Payment Gateways
                    </span>

                    <span
                        class="menu-chevron text-xs transition"
                    >
                        ▼
                    </span>
                </summary>

                <div
                    class="ml-4 space-y-1 border-l border-white/10 pl-3"
                >
                    @coreCan('payment_gateways.offline.view')
                        <a
                            href="#"
                            class="block rounded-lg px-3 py-2.5 text-sm text-slate-300 hover:bg-white/10 hover:text-white"
                            data-core-feature="payment_gateways.offline"
                        >
                            Offline
                        </a>
                    @endcoreCan

                    @coreCan('payment_gateways.online.view')
                        <a
                            href="#"
                            class="block rounded-lg px-3 py-2.5 text-sm text-slate-300 hover:bg-white/10 hover:text-white"
                            data-core-feature="payment_gateways.online"
                        >
                            Online
                        </a>
                    @endcoreCan
                </div>
            </details>
        @endcoreCanAny

{{-- ESUBIZ_CORE_DYNAMIC_MODULE_MANAGERS_V1 --}}
        @php
            $coreModuleRegistry = app(
                \App\Services\Core\CoreFeatureRegistry::class
            );

            $coreModulePermissions = app(
                \App\Services\Core\CorePermissionService::class
            );

            /*
             * A module appears here only when:
             *
             * 1. it registered through CoreFeatureRegistry;
             * 2. source_type is "module";
             * 3. its availability resolver says it is active;
             * 4. it supplies admin navigation;
             * 5. the current user's role has its view permission.
             *
             * Therefore "Store Manager", "Hotel Manager",
             * "Real Estate Manager", etc. come from the module itself.
             * There is no generic hardcoded "Module Managers" menu.
             */
            $coreActiveModuleManagers = collect(
                $coreModuleRegistry->availableFeatures()
            )
                ->filter(function (array $feature) use (
                    $coreModulePermissions
                ) {
                    if (
                        strtolower(
                            (string) (
                                $feature['source_type'] ?? ''
                            )
                        ) !== 'module'
                    ) {
                        return false;
                    }

                    $navigation =
                        $feature['navigation'] ?? null;

                    if (!is_array($navigation)) {
                        return false;
                    }

                    $permission =
                        $navigation['permission'] ?? null;

                    return !$permission
                        || $coreModulePermissions->can(
                            (string) $permission
                        );
                })
                ->sortBy(function (array $feature) {
                    return (int) (
                        $feature['navigation']['order']
                        ?? PHP_INT_MAX
                    );
                });
        @endphp

        @foreach($coreActiveModuleManagers as $coreModuleManager)
            @php
                $coreModuleNav =
                    $coreModuleManager['navigation'];

                $coreModuleLabel =
                    $coreModuleNav['label']
                    ?? $coreModuleManager['label'];

                $coreModuleUrl =
                    $coreModuleNav['url'] ?? null;

                $coreModuleChildren =
                    collect(
                        $coreModuleNav['children'] ?? []
                    )->filter(function (array $child) use (
                        $coreModulePermissions
                    ) {
                        $permission =
                            $child['permission'] ?? null;

                        return !$permission
                            || $coreModulePermissions->can(
                                (string) $permission
                            );
                    });
            @endphp

            @if($coreModuleChildren->isNotEmpty())
                <details class="group">
                    <summary
                        class="flex cursor-pointer items-center justify-between rounded-xl px-4 py-3 text-sm font-bold text-blue-100 hover:bg-white/10"
                    >
                        <span class="flex items-center gap-3">
                            <span>◫</span>
                            {{ $coreModuleLabel }}
                        </span>

                        <span
                            class="menu-chevron text-xs transition"
                        >
                            ▼
                        </span>
                    </summary>

                    <div
                        class="ml-4 space-y-1 border-l border-white/10 pl-3"
                    >
                        @foreach($coreModuleChildren as $coreModuleChild)
                            @if(!empty($coreModuleChild['url']))
                                <a
                                    href="{{ $coreModuleChild['url'] }}"
                                    class="block rounded-lg px-3 py-2.5 text-sm text-slate-300 hover:bg-white/10 hover:text-white"
                                >
                                    {{ $coreModuleChild['label'] }}
                                </a>
                            @endif
                        @endforeach
                    </div>
                </details>

            @elseif($coreModuleUrl)
                <a
                    href="{{ $coreModuleUrl }}"
                    class="flex items-center gap-3 rounded-xl px-4 py-3 text-sm font-bold text-blue-100 hover:bg-white/10"
                >
                    <span>◫</span>
                    {{ $coreModuleLabel }}
                </a>
            @endif
        @endforeach


        <div
            class="px-4 pb-1 pt-5 text-[10px] font-black uppercase tracking-[.15em] text-blue-300"
        >
            Business Tools
        </div>

        {{-- CRM --}}
        <details class="group">

            <summary
                class="flex cursor-pointer items-center justify-between rounded-xl px-4 py-3 text-sm font-bold text-blue-100 hover:bg-white/10"
            >
                <span class="flex items-center gap-3">
                    <span>◎</span>
                    <span>CRM</span>
                </span>

                <span class="menu-chevron text-xs transition">
                    ▼
                </span>
            </summary>

            <div class="ml-4 space-y-1 border-l border-white/10 pl-3">

                @foreach([
                    ['CRM Overview', '/admin/crm'],
                    ['Clients', '/admin/crm/clients'],
                    ['Leads', '/admin/crm/leads'],
                    ['Projects', '/admin/crm/projects'],
                    ['Tasks', '/admin/crm/tasks'],
                    ['Invoices', '/admin/crm/invoices'],
                    ['Incomes', '/admin/crm/incomes'],
                    ['Payments', '/admin/crm/payments'],
                    ['Refunds', '/admin/crm/refunds'],
                    ['Estimates', '/admin/crm/estimates'],
                    ['Subscriptions', '/admin/crm/subscriptions'],
                    ['Products', '/admin/crm/products'],
                    ['Expenses', '/admin/crm/expenses'],
                    ['Proposals', '/admin/crm/proposals'],
                    ['Contracts', '/admin/crm/contracts'],
                    ['Quotations', '/admin/crm/quotations'],
                ] as [$label, $url])

                    <a
                        href="{{ url($url) }}"
                        class="block rounded-lg px-3 py-2.5 text-sm text-slate-300 hover:bg-white/10 hover:text-white"
                    >
                        {{ $label }}
                    </a>

                @endforeach

            </div>

        </details>

{{-- HR --}}

        <details class="group">

            <summary
                class="flex cursor-pointer items-center justify-between rounded-xl px-4 py-3 text-sm font-bold text-blue-100 hover:bg-white/10"
            >
                <span class="flex items-center gap-3">
                    <span>♙</span>
                    HR
                </span>

                <span class="menu-chevron text-xs transition">
                    ▼
                </span>
            </summary>

            <div
                class="ml-4 space-y-1 border-l border-white/10 pl-3"
            >
                <a
                    href="#"
                    class="block rounded-lg px-3 py-2.5 text-sm text-slate-300 hover:bg-white/10 hover:text-white"
                >
                    HR Overview
                </a>

                <a
                    href="#"
                    class="block rounded-lg px-3 py-2.5 text-sm text-slate-300 hover:bg-white/10 hover:text-white"
                >
                    Employees
                </a>

                <a
                    href="#"
                    class="block rounded-lg px-3 py-2.5 text-sm text-slate-300 hover:bg-white/10 hover:text-white"
                >
                    Departments
                </a>

                <a
                    href="#"
                    class="block rounded-lg px-3 py-2.5 text-sm text-slate-300 hover:bg-white/10 hover:text-white"
                >
                    Attendance
                </a>

                <a
                    href="#"
                    class="block rounded-lg px-3 py-2.5 text-sm text-slate-300 hover:bg-white/10 hover:text-white"
                >
                    Leave
                </a>

                <a
                    href="#"
                    class="block rounded-lg px-3 py-2.5 text-sm text-slate-300 hover:bg-white/10 hover:text-white"
                >
                    Payroll
                </a>
            </div>

        </details>


        {{-- POS --}}

        <details class="group">

            <summary
                class="flex cursor-pointer items-center justify-between rounded-xl px-4 py-3 text-sm font-bold text-blue-100 hover:bg-white/10"
            >
                <span class="flex items-center gap-3">
                    <span>▣</span>
                    POS
                </span>

                <span class="menu-chevron text-xs transition">
                    ▼
                </span>
            </summary>

            <div
                class="ml-4 space-y-1 border-l border-white/10 pl-3"
            >
                <a
                    href="#"
                    class="block rounded-lg px-3 py-2.5 text-sm text-slate-300 hover:bg-white/10 hover:text-white"
                >
                    POS Overview
                </a>

                <a
                    href="#"
                    class="block rounded-lg px-3 py-2.5 text-sm text-slate-300 hover:bg-white/10 hover:text-white"
                >
                    POS Terminal
                </a>

                <a
                    href="#"
                    class="block rounded-lg px-3 py-2.5 text-sm text-slate-300 hover:bg-white/10 hover:text-white"
                >
                    Sales
                </a>

                <a
                    href="#"
                    class="block rounded-lg px-3 py-2.5 text-sm text-slate-300 hover:bg-white/10 hover:text-white"
                >
                    Customers
                </a>

                <a
                    href="#"
                    class="block rounded-lg px-3 py-2.5 text-sm text-slate-300 hover:bg-white/10 hover:text-white"
                >
                    Registers
                </a>

                <a
                    href="#"
                    class="block rounded-lg px-3 py-2.5 text-sm text-slate-300 hover:bg-white/10 hover:text-white"
                >
                    POS Reports
                </a>
            </div>

        </details>


        {{-- Communication --}}

        <details class="group">

            <summary
                class="flex cursor-pointer items-center justify-between rounded-xl px-4 py-3 text-sm font-bold text-blue-100 hover:bg-white/10"
            >

                <span class="flex items-center gap-3">
                    <span>✉</span>
                    Communication
                </span>

                <span
                    class="menu-chevron text-xs transition"
                >
                    ▼
                </span>

            </summary>


            <div
                class="ml-4 space-y-1 border-l border-white/10 pl-3"
            >

                {{-- ESUBIZ_CORE_EMAIL_SIDEBAR_LINK_V1 --}}
                <a
                    href="{{ route('tenant.cms.email.index', ['subdomain' => request()->route('subdomain')]) }}"
                    class="block rounded-lg px-3 py-2.5 text-sm text-slate-300 hover:bg-white/10 hover:text-white {{ request()->routeIs('tenant.cms.email.*') ? 'bg-white/10 text-white' : '' }}"
                >
                    Email
                </a>

                <a href="#" class="block rounded-lg px-3 py-2.5 text-sm text-slate-300 hover:bg-white/10">
                    Live Chat
                </a>

                <a href="#" class="block rounded-lg px-3 py-2.5 text-sm text-slate-300 hover:bg-white/10">
                    SMS
                </a>

                <a href="#" class="block rounded-lg px-3 py-2.5 text-sm text-slate-300 hover:bg-white/10">
                    WhatsApp
                </a>

                <a href="#" class="block rounded-lg px-3 py-2.5 text-sm text-slate-300 hover:bg-white/10">
                    Tickets
                </a>

            </div>

        </details>


        {{-- Marketing --}}

        <details class="group">

            <summary
                class="flex cursor-pointer items-center justify-between rounded-xl px-4 py-3 text-sm font-bold text-blue-100 hover:bg-white/10"
            >

                <span class="flex items-center gap-3">
                    <span>◉</span>
                    Marketing & Ads
                </span>

                <span
                    class="menu-chevron text-xs transition"
                >
                    ▼
                </span>

            </summary>


            <div
                class="ml-4 space-y-1 border-l border-white/10 pl-3"
            >

                <a href="#" class="block rounded-lg px-3 py-2.5 text-sm text-slate-300 hover:bg-white/10">
                    Facebook
                </a>

                <a href="#" class="block rounded-lg px-3 py-2.5 text-sm text-slate-300 hover:bg-white/10">
                    Instagram
                </a>

                <a href="#" class="block rounded-lg px-3 py-2.5 text-sm text-slate-300 hover:bg-white/10">
                    Google
                </a>

                <a href="#" class="block rounded-lg px-3 py-2.5 text-sm text-slate-300 hover:bg-white/10">
                    TikTok
                </a>

                <a href="#" class="block rounded-lg px-3 py-2.5 text-sm text-slate-300 hover:bg-white/10">
                    X
                </a>

            </div>

        </details>


        {{-- Marketplace --}}

        <details class="group">

            <summary
                class="flex cursor-pointer items-center justify-between rounded-xl px-4 py-3 text-sm font-bold text-blue-100 hover:bg-white/10"
            >

                <span class="flex items-center gap-3">
                    <span>◇</span>
                    Esubiz Marketplace
                </span>

                <span
                    class="menu-chevron text-xs transition"
                >
                    ▼
                </span>

            </summary>


            <div
                class="ml-4 space-y-1 border-l border-white/10 pl-3"
            >

                <a href="#" class="block rounded-lg px-3 py-2.5 text-sm text-slate-300 hover:bg-white/10">
                    Add-ons
                </a>

                <a href="#" class="block rounded-lg px-3 py-2.5 text-sm text-slate-300 hover:bg-white/10">
                    Bundles
                </a>

                <a href="#" class="block rounded-lg px-3 py-2.5 text-sm text-slate-300 hover:bg-white/10">
                    Modules
                </a>



                <a href="#" class="block rounded-lg px-3 py-2.5 text-sm text-slate-300 hover:bg-white/10">
                    Credits
                </a>

                <a href="#" class="block rounded-lg px-3 py-2.5 text-sm text-slate-300 hover:bg-white/10">
                    Gift Cards
                </a>

            </div>

        </details>



        {{-- Referral --}}
        <details class="group">

            <summary
                class="flex cursor-pointer items-center justify-between rounded-xl px-4 py-3 text-sm font-bold text-blue-100 hover:bg-white/10"
            >
                <span class="flex items-center gap-3">
                    <span>🤝</span>
                    <span>Referral</span>
                </span>

                <span class="menu-chevron text-xs transition">
                    ▼
                </span>
            </summary>

            <div class="ml-4 space-y-1 border-l border-white/10 pl-3">

                <a href="{{ url('/admin/referrals') }}"
                   class="block rounded-lg px-3 py-2.5 text-sm text-slate-300 hover:bg-white/10 hover:text-white">
                    Overview
                </a>

                <a href="{{ url('/admin/referrals/links') }}"
                   class="block rounded-lg px-3 py-2.5 text-sm text-slate-300 hover:bg-white/10 hover:text-white">
                    Referral Links
                </a>

                <a href="{{ url('/admin/referrals/customers') }}"
                   class="block rounded-lg px-3 py-2.5 text-sm text-slate-300 hover:bg-white/10 hover:text-white">
                    Referred Customers
                </a>

                <a href="{{ url('/admin/referrals/earnings') }}"
                   class="block rounded-lg px-3 py-2.5 text-sm text-slate-300 hover:bg-white/10 hover:text-white">
                    Earnings
                </a>

                <a href="{{ route(
                        'tenant.cms.site-ai.settings',
                        [
                            'subdomain' =>
                                $website->subdomain,
                        ]
                    ) }}"
                   class="block rounded-lg px-3 py-2.5 text-sm text-slate-300 hover:bg-white/10 hover:text-white">
                    Payouts
                </a>

            </div>

        </details>


        {{-- Esubiz AI --}}
        <details class="group">

            <summary
                class="flex cursor-pointer items-center justify-between rounded-xl px-4 py-3 text-sm font-bold text-blue-100 hover:bg-white/10"
            >
                <span class="flex items-center gap-3">
                    <span>✦</span>
                    <span>Esubiz AI</span>
                </span>

                <span class="menu-chevron text-xs transition">
                    ▼
                </span>
            </summary>

            <div class="ml-4 space-y-1 border-l border-white/10 pl-3">

                <a href="{{ url('/admin/ai') }}"
                   class="block rounded-lg px-3 py-2.5 text-sm text-slate-300 hover:bg-white/10 hover:text-white">
                    AI Dashboard
                </a>

                <a href="{{ url('/admin/ai/content') }}"
                   class="block rounded-lg px-3 py-2.5 text-sm text-slate-300 hover:bg-white/10 hover:text-white">
                    Content Assistant
                </a>

                <a href="{{ url('/admin/ai/live-chat') }}"
                   class="block rounded-lg px-3 py-2.5 text-sm text-slate-300 hover:bg-white/10 hover:text-white">
                    AI Live Chat
                </a>

                <a href="{{ url('/admin/ai/whatsapp') }}"
                   class="block rounded-lg px-3 py-2.5 text-sm text-slate-300 hover:bg-white/10 hover:text-white">
                    WhatsApp AI
                </a>

                <a href="{{ url('/admin/ai/credits') }}"
                   class="block rounded-lg px-3 py-2.5 text-sm text-slate-300 hover:bg-white/10 hover:text-white">
                    AI Credits
                </a>

                {{-- ESUBIZ_TENANT_AI_USAGE_PRICING_SIDEBAR_V1 --}}
                <a href="{{ url('/admin/ai/usage') }}"
                   class="block rounded-lg px-3 py-2.5 text-sm text-slate-300 hover:bg-white/10 hover:text-white">
                    Usage & Pricing
                </a>

            </div>

        </details>


        {{-- Resource Monitor --}}
        <details class="group">

            <summary
                class="flex cursor-pointer items-center justify-between rounded-xl px-4 py-3 text-sm font-bold text-blue-100 hover:bg-white/10"
            >
                <span class="flex items-center gap-3">
                    <span>◈</span>
                    <span>Resource Monitor</span>
                </span>

                <span class="menu-chevron text-xs transition">
                    ▼
                </span>
            </summary>

            <div class="ml-4 space-y-1 border-l border-white/10 pl-3">

                <a href="{{ url('/admin/resources') }}"
                   class="block rounded-lg px-3 py-2.5 text-sm text-slate-300 hover:bg-white/10 hover:text-white">
                    Overview
                </a>

                <a href="{{ url('/admin/resources/storage') }}"
                   class="block rounded-lg px-3 py-2.5 text-sm text-slate-300 hover:bg-white/10 hover:text-white">
                    Storage
                </a>

                <a href="{{ url('/admin/resources/bandwidth') }}"
                   class="block rounded-lg px-3 py-2.5 text-sm text-slate-300 hover:bg-white/10 hover:text-white">
                    Bandwidth
                </a>

                <a href="{{ url('/admin/resources/usage') }}"
                   class="block rounded-lg px-3 py-2.5 text-sm text-slate-300 hover:bg-white/10 hover:text-white">
                    Usage
                </a>

            </div>

        </details>

{{-- ESUBIZ_UNIFIED_SITE_SETTINGS_MENU_V1 --}}

        <a
            href="{{ route('tenant.cms.settings.site', ['subdomain' => $website->subdomain]) }}"
            class="flex items-center gap-3 rounded-xl px-4 py-3 text-sm font-bold text-blue-100 hover:bg-white/10 hover:text-white
                {{ request()->routeIs('tenant.cms.settings.site') || request()->routeIs('tenant.cms.settings.authentication*') ? 'bg-white/10 text-white' : '' }}"
        >
            <span>⚙</span>
            <span>Settings</span>
        </a>

        {{-- ESUBIZ_MOBILE_PROFILE_SETTINGS_REMOVED_V50 --}}

        @endif
        {{-- /ESUBIZ_CORE_ROLE_AWARE_SIDEBAR_V51 --}}

    </nav>


    <div
        class="border-t border-white/10 px-6 py-4 text-center text-[10px] font-semibold tracking-wide text-blue-200/70"
    >
        Esubiz Core v1.0
    </div>

</aside>
