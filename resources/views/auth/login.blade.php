<x-guest-layout>
    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    @php
    $loginPortal = request()->routeIs('admin.login')
        ? 'admin'
        : (request()->routeIs('developer.login') ? 'developer' : null);

    $loginAction = $loginPortal === 'admin'
        ? route('admin.login')
        : ($loginPortal === 'developer' ? route('developer.login') : route('login'));
@endphp

<form method="POST" action="{{ $loginAction }}">
        @if($loginPortal)
        <input type="hidden" name="portal" value="{{ $loginPortal }}">
    @endif

    @csrf

    <div class="mb-7">
        <h2 class="text-2xl font-extrabold tracking-tight text-slate-950">
            {{ $loginPortal === 'admin'
                ? 'Administrator Sign In'
                : ($loginPortal === 'developer'
                    ? 'Developer Sign In'
                    : 'Sign in to Esubiz') }}
        </h2>

        <p class="mt-2 text-sm leading-6 text-slate-500">
            {{ $loginPortal === 'admin'
                ? 'Access the Esubiz platform administration console.'
                : ($loginPortal === 'developer'
                    ? 'Access your Esubiz developer workspace and tools.'
                    : 'Access your websites, workspace and business tools.') }}
        </p>
    </div>

        <!-- Email Address -->
        <div>
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- Password -->
        <div class="mt-4">
            <x-input-label for="password" :value="__('Password')" />

            <x-text-input id="password" class="block mt-1 w-full"
                            type="password"
                            name="password"
                            required autocomplete="current-password" />

            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Remember Me -->
        <div class="block mt-4">
            <label for="remember_me" class="inline-flex items-center">
                <input id="remember_me" type="checkbox" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500" name="remember">
                <span class="ms-2 text-sm text-gray-600">{{ __('Remember me') }}</span>
            </label>
        </div>

        <div class="flex items-center justify-end mt-4">
            @if (Route::has('password.request'))
                <a class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500" href="{{ route('password.request') }}">
                    {{ __('Forgot your password?') }}
                </a>
            @endif

            <x-primary-button class="ms-3">
                {{ $loginPortal === 'admin'
                    ? 'Sign in to Admin'
                    : ($loginPortal === 'developer'
                        ? 'Sign in to Developer Console'
                        : 'Sign in') }}
            </x-primary-button>
        </div>
    </form>
</x-guest-layout>
