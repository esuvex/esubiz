<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>@yield('title','Platform Console') | Esubiz</title>

    @vite(['resources/css/app.css','resources/js/app.js'])

    <style>
        /* Esubiz Admin UI Foundation */
        .esubiz-admin-card {
            border: 1px solid rgb(226 232 240 / .8);
            background: #fff;
            border-radius: 1rem;
            box-shadow: 0 8px 30px rgb(15 23 42 / .04);
        }

        .esubiz-admin-input {
            width: 100%;
            border-radius: .75rem;
            border-color: rgb(203 213 225);
            background: #fff;
            padding: .65rem .85rem;
            font-size: .875rem;
            line-height: 1.25rem;
            transition: border-color .15s ease, box-shadow .15s ease;
        }

        .esubiz-admin-input:focus {
            border-color: rgb(37 99 235);
            box-shadow: 0 0 0 3px rgb(37 99 235 / .10);
            outline: none;
        }

        .esubiz-admin-primary {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: .5rem;
            border-radius: .75rem;
            background: rgb(15 23 42);
            padding: .65rem 1rem;
            color: #fff;
            font-size: .875rem;
            font-weight: 600;
            transition: transform .15s ease, background .15s ease, box-shadow .15s ease;
        }

        .esubiz-admin-primary:hover {
            background: rgb(30 41 59);
            box-shadow: 0 8px 20px rgb(15 23 42 / .12);
            transform: translateY(-1px);
        }

        .esubiz-admin-secondary {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: .5rem;
            border: 1px solid rgb(226 232 240);
            border-radius: .75rem;
            background: #fff;
            padding: .65rem 1rem;
            color: rgb(51 65 85);
            font-size: .875rem;
            font-weight: 600;
        }

        .esubiz-admin-secondary:hover {
            background: rgb(248 250 252);
        }
    </style>

</head>

<body class="bg-slate-100">

<div x-data="{
        sidebar:false,
        profile:false,
        notifications:false,
        websiteMenu:false,
        plansMenu:false,
        billingMenu:false,
        referralsMenu:false,
        teamMenu:false,
        marketplaceMenu:false,
        workspaceMenu:false
    }"
    class="min-h-screen">

    <!-- Mobile Overlay -->
    <div
        x-show="sidebar"
        x-transition.opacity
        x-cloak
        @click="sidebar=false"
        class="fixed inset-0 bg-black/60 z-40 lg:hidden">
    </div>

    <!-- Sidebar -->
    <aside

       class="fixed inset-y-0 left-0 z-50
       w-64
       bg-slate-900
       text-white
       shadow-2xl
       overflow-y-auto
       overflow-x-hidden
       transition-transform
       duration-300
       lg:translate-x-0"

        :class="sidebar ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'">

        <div class="flex items-center justify-between px-6 py-6 border-b border-slate-800">

            <div>

                <h1 class="text-3xl font-extrabold tracking-wide">
                    ESUBIZ
                </h1>

                <p class="text-slate-400 text-sm mt-2">
                    Business Operating System
                </p>

            </div>

            <button
                @click="sidebar=false"
                class="lg:hidden text-3xl leading-none">

                ×

            </button>

        </div>


        <nav class="space-y-2">

    <!-- ================= USER MODE ================= -->

    @if(session('account_mode') === 'user')

        <a href="{{ route('user.dashboard') }}"
           class="flex items-center rounded-xl px-5 py-3 font-medium
           {{ request()->routeIs('user.dashboard')
                ? 'bg-blue-600 text-white'
                : 'hover:bg-slate-800' }}">
            Dashboard
        </a>

        <!-- Website Management -->
        <div>
            <button @click="websiteMenu=!websiteMenu"
                    class="w-full flex items-center justify-between rounded-xl px-5 py-3 hover:bg-slate-800">
                <span>Website Management</span>
                <span>⌄</span>
            </button>

            <div x-show="websiteMenu" x-cloak class="ml-4 mt-1 space-y-1">

                <a href="{{ route('websites.create') }}"
                   class="block rounded-xl px-5 py-2 text-sm hover:bg-slate-800">
                    Create Website
                </a>

                <a href="{{ route('user.websites.index') }}"
                   class="block rounded-xl px-5 py-2 text-sm hover:bg-slate-800">
                    My Websites
                </a>

            </div>
        </div>

        <!-- Plans -->
        <div>
            <button @click="plansMenu=!plansMenu"
                    class="w-full flex items-center justify-between rounded-xl px-5 py-3 hover:bg-slate-800">
                <span>Plans</span>
                <span>⌄</span>
            </button>

            <div x-show="plansMenu" x-cloak class="ml-4 mt-1 space-y-1">
                <a href="#"
                   class="block rounded-xl px-5 py-2 text-sm hover:bg-slate-800">
                    New Plan
                </a>

                <a href="#"
                   class="block rounded-xl px-5 py-2 text-sm hover:bg-slate-800">
                    My Plans
                </a>
            </div>
        </div>
<!-- Marketplace -->
        <div>
            <button @click="marketplaceMenu=!marketplaceMenu"
                    class="w-full flex items-center justify-between rounded-xl px-5 py-3 hover:bg-slate-800">
                <span>Marketplace</span>
                <span>⌄</span>
            </button>

            <div x-show="marketplaceMenu" x-cloak class="ml-4 mt-1 space-y-1">

                <a href="#" class="block rounded-xl px-5 py-2 text-sm hover:bg-slate-800">Add-ons</a>
                <a href="#" class="block rounded-xl px-5 py-2 text-sm hover:bg-slate-800">Themes</a>
                <a href="#" class="block rounded-xl px-5 py-2 text-sm hover:bg-slate-800">Modules</a>
                <a href="#" class="block rounded-xl px-5 py-2 text-sm hover:bg-slate-800">Others</a>
            </div>
        </div>

        <!-- Billing -->
        <div>
            <button @click="billingMenu=!billingMenu"
                    class="w-full flex items-center justify-between rounded-xl px-5 py-3 hover:bg-slate-800">
                <span>Billing</span>
                <span>⌄</span>
            </button>

            <div x-show="billingMenu" x-cloak class="ml-4 mt-1 space-y-1">
                <a href="#"
                   class="block rounded-xl px-5 py-2 text-sm hover:bg-slate-800">
                    Wallet
                </a>
            </div>
        </div>

        <a href="#" class="flex items-center rounded-xl px-5 py-3 hover:bg-slate-800">
            Tickets
        </a>

        <!-- Referrals -->
        <div>
            <button @click="referralsMenu=!referralsMenu"
                    class="w-full flex items-center justify-between rounded-xl px-5 py-3 hover:bg-slate-800">
                <span>Referrals</span>
                <span>⌄</span>
            </button>

            <div x-show="referralsMenu" x-cloak class="ml-4 mt-1 space-y-1">
                <a href="#" class="block rounded-xl px-5 py-2 text-sm hover:bg-slate-800">Earnings</a>
                <a href="#" class="block rounded-xl px-5 py-2 text-sm hover:bg-slate-800">Referral Tree</a>
            </div>
        </div>

        <!-- Team -->
        <div>
            <button @click="teamMenu=!teamMenu"
                    class="w-full flex items-center justify-between rounded-xl px-5 py-3 hover:bg-slate-800">
                <span>Team</span>
                <span>⌄</span>
            </button>

            <div x-show="teamMenu" x-cloak class="ml-4 mt-1 space-y-1">
                <a href="#" class="block rounded-xl px-5 py-2 text-sm hover:bg-slate-800">Role &amp; Permission</a>
                <a href="#" class="block rounded-xl px-5 py-2 text-sm hover:bg-slate-800">Team Members</a>
            </div>
        </div>

        <a href="#" class="flex items-center rounded-xl px-5 py-3 hover:bg-slate-800">
            Settings
        </a>

    <!-- ================= DEVELOPER MODE ================= -->

    @elseif(session('account_mode') === 'developer')

        <a href="{{ route('developer.dashboard') }}"
           class="flex items-center rounded-xl px-5 py-3 font-medium
           {{ request()->routeIs('developer.dashboard')
                ? 'bg-blue-600 text-white'
                : 'hover:bg-slate-800' }}">
            Dashboard
        </a>

        <!-- Website Developer -->
        <div>
            <button @click="websiteMenu=!websiteMenu"
                    class="w-full flex items-center justify-between rounded-xl px-5 py-3 hover:bg-slate-800">
                <span>Website Developer</span>
                <span>⌄</span>
            </button>

            <div x-show="websiteMenu" x-cloak class="ml-4 mt-1 space-y-1">
                <a href="{{ route('developer.builder') }}"
                   class="block rounded-xl px-5 py-2 text-sm hover:bg-slate-800">
                    Build Website
                </a>

                <a href="#"
                   class="block rounded-xl px-5 py-2 text-sm hover:bg-slate-800">
                    My Websites
                </a>
            </div>
        </div>

        <!-- Plans -->
        <div>
            <button @click="plansMenu=!plansMenu"
                    class="w-full flex items-center justify-between rounded-xl px-5 py-3 hover:bg-slate-800">
                <span>Plans</span>
                <span>⌄</span>
            </button>

            <div x-show="plansMenu" x-cloak class="ml-4 mt-1 space-y-1">
                <a href="#" class="block rounded-xl px-5 py-2 text-sm hover:bg-slate-800">New Plan</a>
                <a href="#" class="block rounded-xl px-5 py-2 text-sm hover:bg-slate-800">My Plans</a>
            </div>
        </div>

        <!-- Marketplace -->
        <div>
            <button @click="marketplaceMenu=!marketplaceMenu"
                    class="w-full flex items-center justify-between rounded-xl px-5 py-3 hover:bg-slate-800">
                <span>Marketplace</span>
                <span>⌄</span>
            </button>

            <div x-show="marketplaceMenu" x-cloak class="ml-4 mt-1 space-y-1">
                <a href="{{ config('sso.clients.marketplace.url') }}/sso/login"
                   class="block rounded-xl px-5 py-2 text-sm hover:bg-slate-800">
                    Marketplace Dashboard
                </a>

                <a href="{{ route('admin.core-addons.index') }}"
   class="block rounded-xl px-5 py-2 text-sm hover:bg-slate-800
   {{ request()->routeIs('admin.core-addons.*') ? 'bg-blue-600 text-white' : '' }}">
    Add-ons
</a>
                <a href="#" class="block rounded-xl px-5 py-2 text-sm hover:bg-slate-800">Themes</a>
                <a href="#" class="block rounded-xl px-5 py-2 text-sm hover:bg-slate-800">Modules</a>
                <a href="#" class="block rounded-xl px-5 py-2 text-sm hover:bg-slate-800">Others</a>
            </div>
        </div>

        <a href="#" class="flex items-center rounded-xl px-5 py-3 hover:bg-slate-800">
            APIs
        </a>

        <a href="#" class="flex items-center rounded-xl px-5 py-3 hover:bg-slate-800">
            Tickets
        </a>

        <!-- Billing -->
        <div>
            <button @click="billingMenu=!billingMenu"
                    class="w-full flex items-center justify-between rounded-xl px-5 py-3 hover:bg-slate-800">
                <span>Billing</span>
                <span>⌄</span>
            </button>

            <div x-show="billingMenu" x-cloak class="ml-4 mt-1 space-y-1">
                <a href="#" class="block rounded-xl px-5 py-2 text-sm hover:bg-slate-800">Wallet</a>
            </div>
        </div>

        <!-- Referrals -->
        <div>
            <button @click="referralsMenu=!referralsMenu"
                    class="w-full flex items-center justify-between rounded-xl px-5 py-3 hover:bg-slate-800">
                <span>Referrals</span>
                <span>⌄</span>
            </button>

            <div x-show="referralsMenu" x-cloak class="ml-4 mt-1 space-y-1">
                <a href="#" class="block rounded-xl px-5 py-2 text-sm hover:bg-slate-800">Earnings</a>
                <a href="#" class="block rounded-xl px-5 py-2 text-sm hover:bg-slate-800">Referral Tree</a>
            </div>
        </div>

        <!-- Team -->
        <div>
            <button @click="teamMenu=!teamMenu"
                    class="w-full flex items-center justify-between rounded-xl px-5 py-3 hover:bg-slate-800">
                <span>Team</span>
                <span>⌄</span>
            </button>

            <div x-show="teamMenu" x-cloak class="ml-4 mt-1 space-y-1">
                <a href="#" class="block rounded-xl px-5 py-2 text-sm hover:bg-slate-800">Role &amp; Permission</a>
                <a href="#" class="block rounded-xl px-5 py-2 text-sm hover:bg-slate-800">Team Members</a>
            </div>
        </div>

        <!-- Workspace -->
        <div>
            <button @click="workspaceMenu=!workspaceMenu"
                    class="w-full flex items-center justify-between rounded-xl px-5 py-3 hover:bg-slate-800">
                <span>Workspace</span>
                <span>⌄</span>
            </button>

            <div x-show="workspaceMenu" x-cloak class="ml-4 mt-1 space-y-1">
                <a href="#" class="block rounded-xl px-5 py-2 text-sm hover:bg-slate-800">New Project</a>
                <a href="#" class="block rounded-xl px-5 py-2 text-sm hover:bg-slate-800">My Projects</a>
            </div>
        </div>

        <a href="#" class="flex items-center rounded-xl px-5 py-3 hover:bg-slate-800">
            Settings
        </a>

    <!-- ================= ADMIN MODE ================= -->

    @elseif(session('account_mode') === 'admin')

        <a href="{{ route('admin.dashboard') }}"
           class="flex items-center rounded-xl px-5 py-3 font-medium
           {{ request()->routeIs('admin.dashboard')
                ? 'bg-blue-600 text-white'
                : 'hover:bg-slate-800' }}">
            Dashboard
        </a>

        <!-- Websites -->
        <div>
            <button @click="websiteMenu=!websiteMenu"
                    class="w-full flex items-center justify-between rounded-xl px-5 py-3 hover:bg-slate-800">
                <span>Websites</span>
                <span>⌄</span>
            </button>

            <div x-show="websiteMenu" x-cloak class="ml-4 mt-1 space-y-1">
                <a href="{{ route('admin.website-types.index') }}"
                   class="block rounded-xl px-5 py-2 text-sm hover:bg-slate-800
                   {{ request()->routeIs('admin.website-types.*') ? 'bg-blue-600 text-white' : '' }}">
                    Website Types
                </a>

                <a href="#"
                   class="block rounded-xl px-5 py-2 text-sm hover:bg-slate-800">
                    User Websites
                </a>
            </div>
        </div>

        <!-- Plans Manager -->
        <div>
            <button @click="plansMenu=!plansMenu"
                    class="w-full flex items-center justify-between rounded-xl px-5 py-3 hover:bg-slate-800">
                <span>Plans Manager</span>
                <span>⌄</span>
            </button>

            <div x-show="plansMenu" x-cloak class="ml-4 mt-1 space-y-1">
                <a href="#" class="block rounded-xl px-5 py-2 text-sm hover:bg-slate-800">New Plan</a>
                <a href="#" class="block rounded-xl px-5 py-2 text-sm hover:bg-slate-800">My Plans</a>
            </div>
        </div>

        <!-- Core -->
        <div>
            <a href="{{ route('admin.core-features.index') }}"
               class="flex items-center rounded-xl px-5 py-3 hover:bg-slate-800
               {{ request()->routeIs('admin.core-features.*') ? 'bg-blue-600 text-white' : '' }}">
                Core
            </a>
        </div>

        <!-- Marketplace -->
        <div>
            <button @click="marketplaceMenu=!marketplaceMenu"
                    class="w-full flex items-center justify-between rounded-xl px-5 py-3 hover:bg-slate-800">
                <span>Marketplace</span>
                <span>⌄</span>
            </button>

            <div x-show="marketplaceMenu" x-cloak class="ml-4 mt-1 space-y-1">
                <a href="{{ config('sso.clients.marketplace.url') }}/sso/login"
                   class="block rounded-xl px-5 py-2 text-sm hover:bg-slate-800">
                    Marketplace Dashboard
                </a>

                <a href="{{ route('admin.core-addons.index') }}"
   class="block rounded-xl px-5 py-2 text-sm hover:bg-slate-800
   {{ request()->routeIs('admin.core-addons.*') ? 'bg-blue-600 text-white' : '' }}">
    Add-ons
</a>
                <a href="#" class="block rounded-xl px-5 py-2 text-sm hover:bg-slate-800">Themes</a>
                <a href="#" class="block rounded-xl px-5 py-2 text-sm hover:bg-slate-800">Modules</a>
                <a href="#" class="block rounded-xl px-5 py-2 text-sm hover:bg-slate-800">Others</a>
            
                    <a href="{{ route('admin.credit-packages.index') }}"
                       class="block rounded-xl px-5 py-2 text-sm hover:bg-slate-800
                       {{ request()->routeIs('admin.credit-packages.*') ? 'bg-blue-600 text-white' : '' }}">
                        Credit Packages
                    </a>
</div>
        </div>

        <a href="#" class="flex items-center rounded-xl px-5 py-3 hover:bg-slate-800">
            APIs
        </a>

        <!-- Users -->
        <div>
            <button @click="teamMenu=!teamMenu"
                    class="w-full flex items-center justify-between rounded-xl px-5 py-3 hover:bg-slate-800">
                <span>Users</span>
                <span>⌄</span>
            </button>

            <div x-show="teamMenu" x-cloak class="ml-4 mt-1 space-y-1">
                <a href="#" class="block rounded-xl px-5 py-2 text-sm hover:bg-slate-800">Website Owners</a>
                <a href="#" class="block rounded-xl px-5 py-2 text-sm hover:bg-slate-800">Developers</a>
            </div>
        </div>

        <a href="#" class="flex items-center rounded-xl px-5 py-3 hover:bg-slate-800">
            Platform Management

        <a href="{{ route('admin.financial-reports') }}"
           class="block rounded-xl px-5 py-3 hover:bg-slate-800">
            Financial Reports
        </a>
        </a>

        <a href="#" class="flex items-center rounded-xl px-5 py-3 hover:bg-slate-800">
            Site Settings
        </a>

    @endif

</nav>

    </aside>



    
    <!-- Main -->
    <div
         class="relative min-h-screen w-full flex flex-col
           transition-all duration-300
           lg:pl-64">


        <!-- Header -->

        <header class="sticky top-0 z-30 bg-white border-b border-slate-200">

            <div class="h-16 px-6 flex items-center justify-between">

                <div class="flex items-center gap-4">

                    <button
                        @click="sidebar=true"
                        class="lg:hidden">

                        <svg class="w-8 h-8"
                             fill="none"
                             stroke="currentColor"
                             viewBox="0 0 24 24">

                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M4 6h16M4 12h16M4 18h16"/>

                        </svg>

                    </button>

                    <div>

                        <h2 class="text-2xl font-bold">

                            @yield('title')

                        </h2>

                    </div>

                </div>



                <div class="flex items-center gap-4">

                    <!-- Notification -->

                    <div class="relative">

                        <button
                            @click="notifications=!notifications"
                            class="relative">

                            🔔

                        </button>

                        <div
                            x-show="notifications"
                            x-cloak
                            @click.outside="notifications=false"
                            x-transition

                            class="absolute right-0 mt-4 w-80 bg-white rounded-2xl shadow-xl border">

                            <div class="p-5 border-b font-semibold">

                                Notifications

                            </div>

                            <div class="p-5 text-sm text-slate-500">

                                No notifications yet.

                            </div>

                        </div>

                    </div>



                    <!-- Profile -->

                    <div class="relative">

                        <button
                            @click="profile=!profile"
                            class="w-11 h-11 rounded-full bg-slate-200 font-semibold">

                            {{ strtoupper(substr(auth()->user()->name, 0, 2)) }}

                        </button>

                        <div
                            x-show="profile"
                            x-cloak
                            @click.outside="profile=false"
                            x-transition

                            class="absolute right-0 mt-4 w-72 bg-white rounded-2xl shadow-xl border">

                            <div class="p-5 border-b">

                                <div class="font-bold">

                                {{ auth()->user()->name }}

                                </div>

                                <div class="text-sm text-slate-500">

                                {{ auth()->user()->email }}

                                </div>

                            </div>

                            <div class="py-2">

                                @if(auth()->user()->hasRole('platform-admin'))
                                    <form method="POST" action="{{ route('account.mode.switch') }}">
                                        @csrf
                                        <input type="hidden" name="mode" value="admin">

                                        <button type="submit"
                                                class="w-full text-left px-5 py-3 hover:bg-slate-100 {{ session('account_mode') === 'admin' ? 'font-semibold text-blue-600' : '' }}">
                                            🛡 Admin Mode
                                        </button>
                                    </form>
                                @endif

                                @if(auth()->user()->hasRole('developer') || auth()->user()->hasRole('platform-admin'))
                                    <form method="POST" action="{{ route('account.mode.switch') }}">
                                        @csrf
                                        <input type="hidden" name="mode" value="developer">

                                        <button type="submit"
                                                class="w-full text-left px-5 py-3 hover:bg-slate-100 {{ session('account_mode') === 'developer' ? 'font-semibold text-blue-600' : '' }}">
                                            👨‍💻 Developer Mode
                                        </button>
                                    </form>
                                @endif

                                @if(auth()->user()->hasRole('user') || auth()->user()->hasRole('developer') || auth()->user()->hasRole('platform-admin'))
                                    <form method="POST" action="{{ route('account.mode.switch') }}">
                                        @csrf
                                        <input type="hidden" name="mode" value="user">

                                        <button type="submit"
                                                class="w-full text-left px-5 py-3 hover:bg-slate-100 {{ session('account_mode') === 'user' ? 'font-semibold text-blue-600' : '' }}">
                                            👤 User Mode
                                        </button>
                                    </form>
                                @endif

                                @if(
                                    session('account_mode') === 'user'
                                    && auth()->user()->hasRole('user')
                                    && !auth()->user()->hasRole('developer')
                                    && !auth()->user()->hasRole('platform-admin')
                                )
                                    <a href="{{ route('developer-account.create') }}"
                                       class="block px-5 py-3 hover:bg-slate-100">
                                        🧑‍💻 Switch to Developer Account
                                    </a>
                                @endif

                                <hr>

                                <a href="#" class="block px-5 py-3 hover:bg-slate-100">

                                    My Profile

                                </a>

                                <a href="#" class="block px-5 py-3 hover:bg-slate-100">

                                    Settings

                                </a>

                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf

                                    <button type="submit"
                                            class="w-full text-left px-5 py-3 hover:bg-red-50 text-red-600">
                                        Logout
                                    </button>
                                </form>

                            </div>

                        </div>

                    </div>

                </div>

            </div>

        </header>



        <main class="flex-1 p-6 overflow-x-hidden">

            @yield('content')

        </main>

    </div>

</div>

<style>
[x-cloak]{
    display:none!important;
}
</style>

</body>
</html>
