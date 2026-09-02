{{-- 
|--------------------------------------------------------------------------
| Canonical Tenant Core CMS Sidebar
|--------------------------------------------------------------------------
|
| This is the ONLY tenant CMS sidebar source.
| Do not create page-specific sidebar copies.
|
--}}

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

        <div class="flex items-center gap-3">

            {{-- ESUBIZ_TENANT_ADMIN_FOOTER_LOGO_BRANDING_V1 --}}
            @php
                $tenantAdminLogo =
                    $settings['theme.corporate.footer_logo_path']
                        ?? null;

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

                <div
                    class="flex h-12 w-12 shrink-0 items-center justify-center overflow-hidden rounded-2xl bg-white p-1"
                >
                    <img
                        src="{{ $tenantAdminLogoUrl }}"
                        alt="{{ $settings['website_name'] ?? $website->name }}"
                        class="h-full w-full object-contain"
                    >
                </div>

            @else

                <div
                    class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-blue-600 text-xl font-black"
                >
                    {{ strtoupper(
                        substr(
                            $settings['website_name']
                                ?? $website->name,
                            0,
                            1
                        )
                    ) }}
                </div>

            @endif


            <div class="min-w-0">

                <div
                    class="truncate text-base font-black"
                >
                    {{ $settings['website_name']
                        ?? $website->name }}
                </div>

                <div
                    class="mt-1 text-xs text-blue-200"
                >
                    Esubiz Core CMS
                </div>

            </div>

        </div>

    </div>


    <nav class="space-y-1 px-3 py-5">


        {{-- Dashboard --}}

        <a
            href="{{ route(
                'tenant.cms.dashboard',
                ['subdomain' => $website->subdomain]
            ) }}"
            class="flex items-center gap-3 rounded-xl bg-blue-600 px-4 py-3 text-sm font-black text-white"
        >
            <span>⌂</span>
            Dashboard
        </a>


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
                    href="#"
                    class="block rounded-lg px-3 py-2.5 text-sm text-slate-300 hover:bg-white/10 hover:text-white"
                >
                    Forms
                </a>

                <a
                    href="#"
                    class="block rounded-lg px-3 py-2.5 text-sm text-slate-300 hover:bg-white/10 hover:text-white"
                >
                    Users
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
                    href="#"
                    class="block rounded-lg px-3 py-2.5 text-sm text-slate-300 hover:bg-white/10 hover:text-white"
                >
                    Modules
                </a>

            </div>

        </details>


        {{-- Module Managers --}}

        <details class="group">

            <summary
                class="flex cursor-pointer items-center justify-between rounded-xl px-4 py-3 text-sm font-bold text-blue-100 hover:bg-white/10"
            >

                <span class="flex items-center gap-3">
                    <span>◫</span>
                    Module Managers
                </span>

                <span
                    class="menu-chevron text-xs transition"
                >
                    ▼
                </span>

            </summary>


            <div
                class="ml-4 border-l border-white/10 pl-3"
            >

                <div
                    class="rounded-xl border border-dashed border-white/10 px-3 py-3 text-xs leading-5 text-slate-400"
                >
                    Enabled module managers will appear here automatically.
                </div>

            </div>

        </details>


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

                <a href="#" class="block rounded-lg px-3 py-2.5 text-sm text-slate-300 hover:bg-white/10">
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

        {{-- ESUBIZ_MOBILE_PROFILE_SETTINGS_MENU_V1 --}}
        <a
            href="{{ route('tenant.cms.settings.profile', ['subdomain' => $website->subdomain]) }}"
            class="flex items-center gap-3 rounded-xl px-4 py-3 text-sm font-bold text-blue-100 hover:bg-white/10 hover:text-white lg:hidden
                {{ request()->routeIs('tenant.cms.settings.profile*') ? 'bg-white/10 text-white' : '' }}"
        >
            <span>👤</span>
            <span>Profile Settings</span>
        </a>

    </nav>


    <div
        class="border-t border-white/10 px-6 py-4 text-center text-[10px] font-semibold tracking-wide text-blue-200/70"
    >
        Esubiz Core v1.0
    </div>

</aside>
