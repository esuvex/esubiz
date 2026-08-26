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

@include(
    'tenant.admin.partials.sidebar',
    ['website' => $website]
)


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

                <a
                    href="{{ route('tenant.cms.themes.index', ['subdomain' => $website->subdomain]) }}"
                    class="flex items-center gap-3 rounded-xl px-4 py-3 text-sm font-bold transition hover:bg-white/10"
                >
                    <span>🎨</span>
                    <span>Themes</span>
                </a>


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


    {{-- ESUBIZ_GLOBAL_AI_ASSISTANT --}}
    @once
        <x-site-ai.assistant />
    @endonce

</body>
</html>
