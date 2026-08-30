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
                    class="flex h-10 items-center justify-center rounded-xl border border-red-200 bg-red-50 px-4 text-sm font-bold text-red-600"
                >
                    Logout
                </button>

            </form>

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

                                    @if(!empty($stat['resource_warning']))
                                        <div
                                            class="mt-3 rounded-xl border px-3 py-2 text-xs font-semibold leading-5
                                                {{
                                                    ($stat['resource_status'] ?? '') === 'critical'
                                                        ? 'border-red-200 bg-red-50 text-red-700'
                                                        : 'border-amber-200 bg-amber-50 text-amber-700'
                                                }}"
                                        >
                                            {{ $stat['resource_warning'] }}
                                        </div>
                                    @endif

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

                </div>

            @endforeach

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
