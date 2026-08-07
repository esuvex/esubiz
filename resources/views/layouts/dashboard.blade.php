<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>

    <meta charset="utf-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1">

    <title>

        {{ $title ?? 'Esubiz' }}

    </title>

    @vite([
        'resources/css/app.css',
        'resources/js/app.js',
    ])

</head>

<body class="bg-slate-100">

<div class="flex h-screen overflow-hidden">

    <!-- Sidebar -->

    <aside
        id="sidebar"
        class="fixed inset-y-0 left-0 z-50 flex w-72 -translate-x-full flex-col border-r border-slate-200 bg-white transition-all duration-300 lg:static lg:translate-x-0">

        <div class="flex h-20 items-center border-b border-slate-200 px-5">

            <div class="flex w-full items-center justify-between">

                <a href="{{ route('user.dashboard') }}"
                   class="flex min-w-0 items-center gap-3">

                    <div
                        id="logoIcon"
                        class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-blue-600 text-xl font-bold text-white">

                        E

                    </div>

                    <div
                        id="logoText"
                        class="transition-all duration-300">

                        <h1 class="text-3xl font-bold tracking-tight text-slate-900">

                            Esubiz

                        </h1>

                        <p class="text-xs uppercase tracking-[0.35em] text-slate-400">

                            Business OS

                        </p>

                    </div>

                </a>

                <button
                    id="sidebarToggle"
                    type="button"
                    class="hidden rounded-xl p-2 text-slate-600 transition hover:bg-slate-100 lg:flex">

                    ☰

                </button>

            </div>

        </div>

        <nav class="flex-1 overflow-y-auto px-4 py-6">

            <div class="space-y-1">

                <a href="{{ route('user.dashboard') }}"
                   class="flex items-center gap-3 rounded-xl px-4 py-3 font-medium
                   {{ request()->routeIs('user.dashboard') ? 'bg-blue-600 text-white' : 'text-slate-700 hover:bg-blue-50 hover:text-blue-700' }}">

                    <span>🏠</span>
                    <span>Dashboard</span>

                </a>

                <a href="{{ route('websites.create') }}"
                   class="flex items-center gap-3 rounded-xl px-4 py-3 font-medium
                   {{ request()->routeIs('websites.*') ? 'bg-blue-600 text-white' : 'text-slate-700 hover:bg-blue-50 hover:text-blue-700' }}">

                    <span>🌐</span>
                    <span>Website Builder</span>

                </a>

                <a href="#"
                   class="flex items-center gap-3 rounded-xl px-4 py-3 font-medium text-slate-700 hover:bg-blue-50 hover:text-blue-700">

                    <span>🌍</span>
                    <span>Domains</span>

                </a>

                <a href="#"
                   class="flex items-center gap-3 rounded-xl px-4 py-3 font-medium text-slate-700 hover:bg-blue-50 hover:text-blue-700">

                    <span>🎨</span>
                    <span>Templates</span>

                </a>

                <a href="#"
                   class="flex items-center gap-3 rounded-xl px-4 py-3 font-medium text-slate-700 hover:bg-blue-50 hover:text-blue-700">

                    <span>🛍️</span>
                    <span>Marketplace</span>

                </a>

            </div>

            <div class="my-6 border-t border-slate-200"></div>

            <div class="space-y-1">

                <a href="#"
                   class="flex items-center gap-3 rounded-xl px-4 py-3 font-medium text-slate-700 hover:bg-blue-50 hover:text-blue-700">

                    <span>💳</span>
                    <span>Billing</span>

                </a>

                <a href="#"
                   class="flex items-center gap-3 rounded-xl px-4 py-3 font-medium text-slate-700 hover:bg-blue-50 hover:text-blue-700">

                    <span>👥</span>
                    <span>Team</span>

                </a>

                <a href="#"
                   class="flex items-center gap-3 rounded-xl px-4 py-3 font-medium text-slate-700 hover:bg-blue-50 hover:text-blue-700">

                    <span>🔔</span>
                    <span>Notifications</span>

                </a>

                <a href="#"
                   class="flex items-center gap-3 rounded-xl px-4 py-3 font-medium text-slate-700 hover:bg-blue-50 hover:text-blue-700">

                    <span>⚙️</span>
                    <span>Settings</span>

                </a>

            </div>

        </nav>

        <div class="border-t border-slate-200 p-4">

            <div class="flex items-center gap-3 rounded-2xl bg-slate-50 p-3">

                <div class="flex h-12 w-12 items-center justify-center rounded-full bg-blue-600 font-bold text-white">

                    {{ strtoupper(substr(auth()->user()->name ?? 'U',0,1)) }}

                </div>

                <div>

                    <div class="font-semibold">

                        {{ auth()->user()->name }}

                    </div>

                    <div class="text-sm text-slate-500">

                        {{ auth()->user()->email }}

                    </div>

                </div>

            </div>

        </div>

    </aside>
    <div class="flex min-w-0 flex-1 flex-col overflow-hidden">

        <header class="flex h-20 items-center justify-between border-b border-slate-200 bg-white px-8">

            <div class="flex items-center gap-4">

                <button
                    id="sidebarToggleMobile"
                    type="button"
                    class="rounded-xl p-2 text-slate-600 hover:bg-slate-100 lg:hidden">

                    ☰

                </button>

                <div>

                    <h2 class="text-2xl font-bold text-slate-900">

                        {{ $title ?? 'Dashboard' }}

                    </h2>

                    <p class="text-sm text-slate-500">

                        {{ $subtitle ?? '' }}

                    </p>

                </div>

            </div>

            <div class="flex items-center gap-4">

                <button class="rounded-xl border border-slate-200 p-3 hover:bg-slate-100">

                    🔔

                </button>

                <button class="rounded-xl border border-slate-200 p-3 hover:bg-slate-100">

                    💬

                </button>

            </div>

        </header>

        <main class="flex-1 overflow-y-auto p-8">

            @yield('content')

            {{ $slot ?? '' }}

        </main>

    </div>

</div>

<script>

document.addEventListener('DOMContentLoaded', () => {

    const sidebar = document.getElementById('sidebar');

    const desktopToggle = document.getElementById('sidebarToggle');

    const mobileToggle = document.getElementById('sidebarToggleMobile');

    const logoText = document.getElementById('logoText');

    let collapsed = false;

    if (desktopToggle) {

        desktopToggle.addEventListener('click', () => {

            collapsed = !collapsed;

            sidebar.classList.toggle('w-72');
            sidebar.classList.toggle('w-20');

            if (logoText) {
                logoText.classList.toggle('hidden');
            }

            sidebar.querySelectorAll('nav span:last-child').forEach(el => {
                el.classList.toggle('hidden');
            });

        });

    }

    if (mobileToggle) {

        mobileToggle.addEventListener('click', () => {

            sidebar.classList.toggle('-translate-x-full');

        });

    }

});

</script>

</body>

</html>
