<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>@yield('title','Platform Console') | Esubiz</title>

    @vite(['resources/css/app.css','resources/js/app.js'])
</head>

<body class="bg-slate-100">

<div x-data="{
        sidebar:false,
        profile:false,
        notifications:false
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


        <nav class="px-4 py-6 space-y-2">

          <a href="{{ route(request()->is('user/*') ? 'user.dashboard' : 'admin.dashboard') }}"
   class="flex items-center rounded-xl px-5 py-3 font-medium
   {{ request()->routeIs('user.dashboard') || request()->routeIs('admin.dashboard')
        ? 'bg-blue-600 text-white'
        : 'hover:bg-slate-800' }}">

         Dashboard

</a>

            <a href="{{ route('websites.create') }}"
   class="flex items-center rounded-xl px-5 py-3 font-medium
   {{ request()->routeIs('websites.*')
        ? 'bg-blue-600 text-white'
        : 'hover:bg-slate-800' }}">
 

                Website Builder

            </a>

            <a href="#"
               class="flex items-center rounded-xl px-5 py-3 hover:bg-slate-800">

                Business

            </a>

            <a href="#"
               class="flex items-center rounded-xl px-5 py-3 hover:bg-slate-800">

                Sales

            </a>

            <a href="#"
               class="flex items-center rounded-xl px-5 py-3 hover:bg-slate-800">

                Finance

            </a>

            <a href="#"
               class="flex items-center rounded-xl px-5 py-3 hover:bg-slate-800">

                Marketplace

            </a>

            <a href="#"
               class="flex items-center rounded-xl px-5 py-3 hover:bg-slate-800">

                API

            </a>

            <a href="#"
               class="flex items-center rounded-xl px-5 py-3 hover:bg-slate-800">

                Settings

            </a>

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

                                {{ request()->is('user/*') ? auth()->user()->name : 'Platform Admin' }}

                                </div>

                                <div class="text-sm text-slate-500">

                                {{ request()->is('user/*') ? auth()->user()->email : 'administrator@esubiz.com' }}

                                </div>

                            </div>

                            <div class="py-2">

                                <a href="#" class="block px-5 py-3 hover:bg-slate-100">

                                    🛡 Platform Console

                                </a>

                                <a href="#" class="block px-5 py-3 hover:bg-slate-100">

                                    👨‍💻 Creator Mode

                                </a>

                                <a href="#" class="block px-5 py-3 hover:bg-slate-100">

                                    👤 User Mode

                                </a>

                                <hr>

                                <a href="#" class="block px-5 py-3 hover:bg-slate-100">

                                    My Profile

                                </a>

                                <a href="#" class="block px-5 py-3 hover:bg-slate-100">

                                    Settings

                                </a>

                                <a href="#" class="block px-5 py-3 hover:bg-red-50 text-red-600">

                                    Logout

                                </a>

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
