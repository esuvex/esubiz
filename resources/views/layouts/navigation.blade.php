<!-- Mobile Overlay -->

<div
    x-show="sidebar"
    x-transition.opacity
    x-cloak
    @click="sidebar=false"
    class="fixed inset-0 z-40 bg-black/60 lg:hidden">
</div>

<!-- Sidebar -->

<aside

    class="fixed inset-y-0 left-0 z-50
           flex w-72 flex-col
           bg-slate-900 text-white
           shadow-2xl
           transition-transform duration-300
           lg:translate-x-0"

    :class="sidebar
        ? 'translate-x-0'
        : '-translate-x-full lg:translate-x-0'">

    <!-- Logo -->

    <div class="border-b border-slate-800 px-6 py-6">

        <a href="{{ route('dashboard') }}">

            <h1 class="text-3xl font-extrabold tracking-wide">

                ESUBIZ

            </h1>

            <p class="mt-2 text-sm text-slate-400">

                Business Operating System

            </p>

        </a>

    </div>

    <!-- Navigation -->

    <nav class="flex-1 overflow-y-auto px-4 py-6 space-y-2">

        <a
            href="{{ route('dashboard') }}"
            class="flex items-center rounded-xl px-5 py-3 font-medium transition hover:bg-slate-800 {{ request()->routeIs('dashboard') ? 'bg-blue-600 text-white' : '' }}">

            Dashboard

        </a>

        <a
            href="{{ route('websites.create') }}"
            class="flex items-center rounded-xl px-5 py-3 transition hover:bg-slate-800 {{ request()->routeIs('websites.*') ? 'bg-blue-600 text-white' : '' }}">

            Website Builder

        </a>

        <a
            href="#"
            class="flex items-center rounded-xl px-5 py-3 transition hover:bg-slate-800">

            Domains

        </a>

        <a
            href="#"
            class="flex items-center rounded-xl px-5 py-3 transition hover:bg-slate-800">

            Templates

        </a>

        <a
            href="#"
            class="flex items-center rounded-xl px-5 py-3 transition hover:bg-slate-800">

            Marketplace

        </a>

        <a
            href="#"
            class="flex items-center rounded-xl px-5 py-3 transition hover:bg-slate-800">

            Billing

        </a>
        <a
            href="#"
            class="flex items-center rounded-xl px-5 py-3 transition hover:bg-slate-800">

            Team

        </a>

        <a
            href="#"
            class="flex items-center rounded-xl px-5 py-3 transition hover:bg-slate-800">

            Notifications

        </a>

        <a
            href="#"
            class="flex items-center rounded-xl px-5 py-3 transition hover:bg-slate-800">

            Settings

        </a>

        <a
            href="#"
            class="flex items-center rounded-xl px-5 py-3 transition hover:bg-slate-800">

            Help & Support

        </a>

    </nav>

    <!-- Sidebar Footer -->

    <div class="border-t border-slate-800 p-6">

        <div class="text-xs uppercase tracking-wider text-slate-500">

            Logged in as

        </div>

        <div class="mt-2 font-semibold">

            {{ auth()->user()->name }}

        </div>

        <div class="text-sm text-slate-400 truncate">

            {{ auth()->user()->email }}

        </div>

    </div>

</aside>
{{-- End of Esubiz Navigation --}}
