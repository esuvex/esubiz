<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>

    <meta charset="utf-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1">

    <meta name="csrf-token"
          content="{{ csrf_token() }}">

    <title>@yield('title','Esubiz') | Esubiz</title>

    @vite([
        'resources/css/app.css',
        'resources/js/app.js',
    ])


    {{-- ESUBIZ_CENTRAL_CANONICAL_FAVICON_V6 --}}
    <link
        rel="icon"
        href="{{ url('/media/branding/favicon.png') }}"
    >
    <link
        rel="shortcut icon"
        href="{{ url('/media/branding/favicon.png') }}"
    >

    {{-- ESUBIZ_CENTRAL_GLOBAL_SEO_LAYOUT_V13 --}}
    @include('partials.central-seo')

</head>

<body
    class="bg-slate-100 antialiased"
    x-data="{
        sidebar:false,
        notifications:false,
        profile:false
    }">

<div class="min-h-screen">

    <!-- Mobile Overlay -->

    <div
        x-show="sidebar"
        x-transition.opacity
        x-cloak
        @click="sidebar=false"
        class="fixed inset-0 z-40 bg-black/60 lg:hidden">
    </div>

    <!-- Main Content -->

    <div class="lg:ml-72 min-h-screen flex flex-col">

        <!-- Header -->

        <header class="sticky top-0 z-30 border-b border-slate-200 bg-white">

            <div class="flex h-16 items-center justify-between px-6">

                <div class="flex items-center gap-4">

                    <button
                        @click="sidebar=true"
                        class="rounded-lg p-2 hover:bg-slate-100 lg:hidden">

                        <svg
                            class="h-7 w-7"
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

                        <h2 class="text-2xl font-bold text-slate-900">

                            @yield('title')

                        </h2>

                    </div>

                </div>

                <div class="flex items-center gap-4">

                    <!-- Notifications -->

                    <div class="relative">

                        <button
                            @click="notifications=!notifications"
                            class="rounded-xl p-2 hover:bg-slate-100">

                            🔔

                        </button>

                        <div
                            x-show="notifications"
                            x-transition
                            x-cloak
                            @click.outside="notifications=false"
                            class="absolute right-0 mt-3 w-80 rounded-2xl border bg-white shadow-xl">

                            <div class="border-b p-5 font-semibold">

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
                            class="flex h-11 w-11 items-center justify-center rounded-full bg-slate-200 font-semibold text-slate-700">

                            {{ strtoupper(substr(auth()->user()->name ?? 'U',0,1)) }}

                        </button>

                        <div
                            x-show="profile"
                            x-transition
                            x-cloak
                            @click.outside="profile=false"
                            class="absolute right-0 mt-3 w-72 rounded-2xl border bg-white shadow-xl">

                            <div class="border-b p-5">

                                <div class="font-bold">

                                    {{ auth()->user()->name }}

                                </div>

                                <div class="text-sm text-slate-500">

                                    {{ auth()->user()->email }}

                                </div>

                            </div>

                            <div class="py-2">

                                <a
                                    href="{{ route('profile.edit') }}"
                                    class="block px-5 py-3 hover:bg-slate-100">

                                    My Profile

                                </a>

                                <a
                                    href="#"
                                    class="block px-5 py-3 hover:bg-slate-100">

                                    Settings

                                </a>

                                <form
                                    method="POST"
                                    action="{{ route('logout') }}">

                                    @csrf

                                    <button
                                        type="submit"
                                        class="block w-full px-5 py-3 text-left text-red-600 hover:bg-red-50">

                                        Logout

                                    </button>

                                </form>

                            </div>

                        </div>

                    </div>

                </div>

            </div>

        </header>

        <!-- Page Content -->

        <main class="flex-1">

            <div class="mx-auto w-full max-w-7xl p-6 lg:p-8">

                @yield('content')

            </div>

        </main>

    </div>

</div>

</body>

</html>
