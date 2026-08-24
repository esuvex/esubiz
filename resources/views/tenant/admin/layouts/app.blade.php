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

<aside
    id="tenantCmsSidebar"
    class="fixed inset-y-0 left-0 z-50 w-[280px] -translate-x-full overflow-y-auto border-r border-white/10 text-white shadow-xl transition-transform duration-300 lg:translate-x-0"
    style="background:linear-gradient(180deg,#0b1739 0%,#10245a 100%);"
>

    {{-- Mobile close button --}}

    <div
        class="flex justify-end px-4 pt-4 lg:hidden"
    >

        <button
            type="button"
            id="tenantCmsCloseButton"
            class="flex h-10 w-10 items-center justify-center rounded-xl border border-white/10 bg-white/5 text-2xl text-white hover:bg-white/10"
            aria-label="Close navigation"
        >
            ×
        </button>

    </div>


    {{-- Website Identity --}}

    <div
        class="border-b border-white/10 px-5 py-5"
    >

        <div class="flex items-center gap-3">

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


    {{-- =====================================================
         NAVIGATION
    ====================================================== --}}

    <nav class="space-y-1 px-3 py-5">


        {{-- Dashboard --}}

        <a
            href="{{ route(
                'tenant.cms.dashboard',
                ['subdomain' => $website->subdomain]
            ) }}"
            class="flex items-center gap-3 rounded-xl px-4 py-3 text-sm font-bold
                {{ request()->routeIs('tenant.cms.dashboard')
                    ? 'bg-blue-600 text-white'
                    : 'text-blue-100 hover:bg-white/10' }}"
        >
            <span>⌂</span>
            Dashboard
        </a>


        {{-- =================================================
             SITE MANAGEMENT
        ================================================== --}}

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
                    class="block rounded-lg px-3 py-2.5 text-sm
                        {{ request()->routeIs('tenant.cms.pages.*')
                            ? 'bg-blue-600 font-bold text-white'
                            : 'text-slate-300 hover:bg-white/10 hover:text-white' }}"
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
                    href="#"
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


        {{-- =================================================
             MODULE MANAGERS
        ================================================== --}}

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


        {{-- =================================================
             CRM
        ================================================== --}}

        <details class="group">

            <summary
                class="flex cursor-pointer items-center justify-between rounded-xl px-4 py-3 text-sm font-bold text-blue-100 hover:bg-white/10"
            >

                <span class="flex items-center gap-3">
                    <span>◎</span>
                    CRM
                </span>

                <span class="menu-chevron text-xs transition">
                    ▼
                </span>

            </summary>


            <div
                class="ml-4 space-y-1 border-l border-white/10 pl-3"
            >

                <a href="#" class="block rounded-lg px-3 py-2.5 text-sm text-slate-300 hover:bg-white/10 hover:text-white">
                    CRM Overview
                </a>

                <a href="#" class="block rounded-lg px-3 py-2.5 text-sm text-slate-300 hover:bg-white/10 hover:text-white">
                    Clients
                </a>

                <a href="#" class="block rounded-lg px-3 py-2.5 text-sm text-slate-300 hover:bg-white/10 hover:text-white">
                    Leads
                </a>

                <a href="#" class="block rounded-lg px-3 py-2.5 text-sm text-slate-300 hover:bg-white/10 hover:text-white">
                    Projects
                </a>

                <a href="#" class="block rounded-lg px-3 py-2.5 text-sm text-slate-300 hover:bg-white/10 hover:text-white">
                    Tasks
                </a>


                <div
                    class="px-3 pb-1 pt-3 text-[10px] font-black uppercase tracking-[.14em] text-blue-300"
                >
                    Sales & Documents
                </div>


                <a href="#" class="block rounded-lg px-3 py-2.5 text-sm text-slate-300 hover:bg-white/10 hover:text-white">
                    Products
                </a>

                <a href="#" class="block rounded-lg px-3 py-2.5 text-sm text-slate-300 hover:bg-white/10 hover:text-white">
                    Estimates
                </a>

                <a href="#" class="block rounded-lg px-3 py-2.5 text-sm text-slate-300 hover:bg-white/10 hover:text-white">
                    Quotes
                </a>

                <a href="#" class="block rounded-lg px-3 py-2.5 text-sm text-slate-300 hover:bg-white/10 hover:text-white">
                    Proposals
                </a>

                <a href="#" class="block rounded-lg px-3 py-2.5 text-sm text-slate-300 hover:bg-white/10 hover:text-white">
                    Contracts
                </a>

                <a href="#" class="block rounded-lg px-3 py-2.5 text-sm text-slate-300 hover:bg-white/10 hover:text-white">
                    Invoices
                </a>


                <div
                    class="px-3 pb-1 pt-3 text-[10px] font-black uppercase tracking-[.14em] text-blue-300"
                >
                    Finance
                </div>


                <a href="#" class="block rounded-lg px-3 py-2.5 text-sm text-slate-300 hover:bg-white/10 hover:text-white">
                    Payments
                </a>

                <a href="#" class="block rounded-lg px-3 py-2.5 text-sm text-slate-300 hover:bg-white/10 hover:text-white">
                    Receipts
                </a>

                <a href="#" class="block rounded-lg px-3 py-2.5 text-sm text-slate-300 hover:bg-white/10 hover:text-white">
                    Income
                </a>

                <a href="#" class="block rounded-lg px-3 py-2.5 text-sm text-slate-300 hover:bg-white/10 hover:text-white">
                    Expenses
                </a>

                <a href="#" class="block rounded-lg px-3 py-2.5 text-sm text-slate-300 hover:bg-white/10 hover:text-white">
                    Subscriptions
                </a>

                <a href="#" class="block rounded-lg px-3 py-2.5 text-sm text-slate-300 hover:bg-white/10 hover:text-white">
                    Refunds
                </a>

            </div>

        </details>


        {{-- =================================================
             HR
        ================================================== --}}

        <details class="group">

            <summary
                class="flex cursor-pointer items-center justify-between rounded-xl px-4 py-3 text-sm font-bold text-blue-100 hover:bg-white/10"
            >

                <span class="flex items-center gap-3">
                    <span>♙</span>
                    HR
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
                    HR Overview
                </a>

                <a href="#" class="block rounded-lg px-3 py-2.5 text-sm text-slate-300 hover:bg-white/10">
                    Employees
                </a>

                <a href="#" class="block rounded-lg px-3 py-2.5 text-sm text-slate-300 hover:bg-white/10">
                    Departments
                </a>

                <a href="#" class="block rounded-lg px-3 py-2.5 text-sm text-slate-300 hover:bg-white/10">
                    Attendance
                </a>

                <a href="#" class="block rounded-lg px-3 py-2.5 text-sm text-slate-300 hover:bg-white/10">
                    Leave
                </a>

                <a href="#" class="block rounded-lg px-3 py-2.5 text-sm text-slate-300 hover:bg-white/10">
                    Payroll
                </a>

            </div>

        </details>


        {{-- =================================================
             POS
        ================================================== --}}

        <details class="group">

            <summary
                class="flex cursor-pointer items-center justify-between rounded-xl px-4 py-3 text-sm font-bold text-blue-100 hover:bg-white/10"
            >

                <span class="flex items-center gap-3">
                    <span>▣</span>
                    POS
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
                    POS Overview
                </a>

                <a href="#" class="block rounded-lg px-3 py-2.5 text-sm text-slate-300 hover:bg-white/10">
                    POS Terminal
                </a>

                <a href="#" class="block rounded-lg px-3 py-2.5 text-sm text-slate-300 hover:bg-white/10">
                    Sales
                </a>

                <a href="#" class="block rounded-lg px-3 py-2.5 text-sm text-slate-300 hover:bg-white/10">
                    Customers
                </a>

                <a href="#" class="block rounded-lg px-3 py-2.5 text-sm text-slate-300 hover:bg-white/10">
                    Registers
                </a>

                <a href="#" class="block rounded-lg px-3 py-2.5 text-sm text-slate-300 hover:bg-white/10">
                    POS Reports
                </a>

            </div>

        </details>


        {{-- =================================================
             COMMUNICATION
        ================================================== --}}

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



        {{-- =================================================
             PAYMENT MANAGEMENT
        ================================================== --}}

        <details class="group">

            <summary
                class="flex cursor-pointer items-center justify-between rounded-xl px-4 py-3 text-sm font-bold text-blue-100 hover:bg-white/10"
            >

                <span class="flex items-center gap-3">
                    <span>₦</span>
                    Payment
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
                    href="#"
                    class="block rounded-lg px-3 py-2.5 text-sm text-slate-300 hover:bg-white/10 hover:text-white"
                >
                    Online Gateways
                </a>

                <a
                    href="#"
                    class="block rounded-lg px-3 py-2.5 text-sm text-slate-300 hover:bg-white/10 hover:text-white"
                >
                    Offline Gateways
                </a>

            </div>

        </details>


        {{-- =================================================
             REFERRALS
        ================================================== --}}

        <details class="group">

            <summary
                class="flex cursor-pointer items-center justify-between rounded-xl px-4 py-3 text-sm font-bold text-blue-100 hover:bg-white/10"
            >

                <span class="flex items-center gap-3">
                    <span>↗</span>
                    Referrals
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
                    href="#"
                    class="block rounded-lg px-3 py-2.5 text-sm text-slate-300 hover:bg-white/10 hover:text-white"
                >
                    Referral Settings
                </a>

                <a
                    href="#"
                    class="block rounded-lg px-3 py-2.5 text-sm text-slate-300 hover:bg-white/10 hover:text-white"
                >
                    Referral Records
                </a>

                <a
                    href="#"
                    class="block rounded-lg px-3 py-2.5 text-sm text-slate-300 hover:bg-white/10 hover:text-white"
                >
                    Referral Payouts
                </a>

            </div>

        </details>


        {{-- =================================================
             MARKETING
        ================================================== --}}

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


        {{-- =================================================
             AI
        ================================================== --}}

        <details class="group">

            <summary
                class="flex cursor-pointer items-center justify-between rounded-xl px-4 py-3 text-sm font-bold text-blue-100 hover:bg-white/10"
            >

                <span class="flex items-center gap-3">
                    <span>✦</span>
                    Esubiz AI
                </span>

                <span class="menu-chevron text-xs transition">
                    ▼
                </span>

            </summary>


            <div
                class="ml-4 space-y-1 border-l border-white/10 pl-3"
            >

                <a href="#" class="block rounded-lg px-3 py-2.5 text-sm text-slate-300 hover:bg-white/10 hover:text-white">
                    AI Overview
                </a>

                <a href="#" class="block rounded-lg px-3 py-2.5 text-sm text-slate-300 hover:bg-white/10 hover:text-white">
                    AI Credits
                </a>

                <a href="#" class="block rounded-lg px-3 py-2.5 text-sm text-slate-300 hover:bg-white/10 hover:text-white">
                    AI Usage History
                </a>

                <a href="#" class="block rounded-lg px-3 py-2.5 text-sm text-slate-300 hover:bg-white/10 hover:text-white">
                    AI Activity
                </a>

                <a href="#" class="block rounded-lg px-3 py-2.5 text-sm text-slate-300 hover:bg-white/10 hover:text-white">
                    AI Settings
                </a>

            </div>

        </details>


        {{-- =================================================
             ESUBIZ MARKETPLACE
        ================================================== --}}

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
                    Themes
                </a>

                <a href="#" class="block rounded-lg px-3 py-2.5 text-sm text-slate-300 hover:bg-white/10">
                    Credits
                </a>

                <a href="#" class="block rounded-lg px-3 py-2.5 text-sm text-slate-300 hover:bg-white/10">
                    Gift Cards
                </a>

            </div>

        </details>


        {{-- =================================================
             SETTINGS
        ================================================== --}}

        <details class="group">

            <summary
                class="flex cursor-pointer items-center justify-between rounded-xl px-4 py-3 text-sm font-bold text-blue-100 hover:bg-white/10"
            >

                <span class="flex items-center gap-3">
                    <span>⚙</span>
                    Settings
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
                    General
                </a>

                <a href="#" class="block rounded-lg px-3 py-2.5 text-sm text-slate-300 hover:bg-white/10">
                    Site Identity
                </a>

                <a href="#" class="block rounded-lg px-3 py-2.5 text-sm text-slate-300 hover:bg-white/10">
                    Colors & Branding
                </a>

                <a href="#" class="block rounded-lg px-3 py-2.5 text-sm text-slate-300 hover:bg-white/10">
                    Typography
                </a>

                <a href="#" class="block rounded-lg px-3 py-2.5 text-sm text-slate-300 hover:bg-white/10">
                    Currency
                </a>

                <a href="#" class="block rounded-lg px-3 py-2.5 text-sm text-slate-300 hover:bg-white/10">
                    Timezone
                </a>

                <a href="#" class="block rounded-lg px-3 py-2.5 text-sm text-slate-300 hover:bg-white/10">
                    Module Settings
                </a>

                <a href="#" class="block rounded-lg px-3 py-2.5 text-sm text-slate-300 hover:bg-white/10">
                    Core Add-ons
                </a>

            </div>

        </details>


        {{-- =================================================
             RESOURCE MONITOR
        ================================================== --}}

        <details class="group">

            <summary
                class="flex cursor-pointer items-center justify-between rounded-xl px-4 py-3 text-sm font-bold text-blue-100 hover:bg-white/10"
            >

                <span class="flex items-center gap-3">
                    <span>◌</span>
                    Resource Monitor
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
                    href="#"
                    class="block rounded-lg px-3 py-2.5 text-sm text-slate-300 hover:bg-white/10 hover:text-white"
                >
                    Overview
                </a>

                <a
                    href="#"
                    class="block rounded-lg px-3 py-2.5 text-sm text-slate-300 hover:bg-white/10 hover:text-white"
                >
                    Core Usage
                </a>

                <a
                    href="#"
                    class="block rounded-lg px-3 py-2.5 text-sm text-slate-300 hover:bg-white/10 hover:text-white"
                >
                    Add-ons Usage
                </a>

                <a
                    href="#"
                    class="block rounded-lg px-3 py-2.5 text-sm text-slate-300 hover:bg-white/10 hover:text-white"
                >
                    Storage
                </a>

            </div>


    </nav>

</aside>


{{-- =========================================================
     PERMANENT HEADER
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
                aria-label="Open navigation"
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
                    @yield(
                        'header_title',
                        $settings['website_name']
                            ?? $website->name
                    )
                </div>

            </div>

        </div>


        <div class="flex shrink-0 items-center gap-2">


            <a
                href="/"
                target="_blank"
                rel="noopener noreferrer"
                class="flex h-10 items-center justify-center rounded-xl bg-blue-600 px-3 text-sm font-bold text-white sm:px-4"
            >
                <span class="hidden sm:inline">
                    View Website
                </span>

                <span class="sm:ml-2">
                    ↗
                </span>
            </a>


            <form
                method="POST"
                action="{{ route(
                    'tenant.cms.logout',
                    ['subdomain' => $website->subdomain]
                ) }}"
            >

                @csrf

                <button
                    type="submit"
                    class="flex h-10 items-center justify-center rounded-xl border border-red-200 bg-red-50 px-3 text-sm font-bold text-red-600 sm:px-4"
                >
                    Logout
                </button>

            </form>

        </div>

    </div>

</header>


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

</body>
</html>
