<div
    x-data="{
        sidebar:false,
        notifications:false,
        profile:false
    }"
    class="min-h-screen bg-slate-100">

    @include('layouts.navigation')

    <div class="lg:ml-72 min-h-screen flex flex-col">

        @isset($header)

        <header class="sticky top-0 z-30 bg-white border-b border-slate-200">

            <div class="flex h-16 items-center justify-between px-6">

                <div class="flex items-center gap-4">

                    <button
                        @click="sidebar=true"
                        class="rounded-lg p-2 hover:bg-slate-100 lg:hidden">

                        <svg
                            class="w-7 h-7"
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

                    {{ $header }}

                </div>

                <div class="flex items-center gap-4">
                    <div class="relative">

                        <button
                            @click="notifications=!notifications"
                            class="rounded-xl p-2 hover:bg-slate-100">

                            🔔

                        </button>

                        <div
                            x-show="notifications"
                            x-cloak
                            x-transition
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

                    <div class="relative">

                        <button
                            @click="profile=!profile"
                            class="flex h-11 w-11 items-center justify-center rounded-full bg-slate-200 font-semibold text-slate-700">

                            {{ strtoupper(substr(auth()->user()->name ?? 'U',0,1)) }}

                        </button>

                        <div
                            x-show="profile"
                            x-cloak
                            x-transition
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

        @endisset

        <main class="flex-1">

            <div class="mx-auto w-full max-w-7xl p-6 lg:p-8">

                {{ $slot }}

            </div>

        </main>

    </div>

</div>
