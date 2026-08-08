<x-guest-layout>

    <div class="mb-7">
        <h2 class="text-2xl font-extrabold tracking-tight text-slate-950">
            Create your Developer account
        </h2>

        <p class="mt-2 text-sm leading-6 text-slate-500">
            Join Esubiz as a developer and access the tools you need to build, compile and manage websites and developer projects.
        </p>
    </div>

    <form method="POST" action="{{ route('developer.register') }}">
        @csrf

        <!-- Name -->
        <div>
            <x-input-label for="name" :value="__('Full name')" />

            <x-text-input
                id="name"
                class="block mt-1 w-full"
                type="text"
                name="name"
                :value="old('name')"
                required
                autofocus
                autocomplete="name"
            />

            <x-input-error :messages="$errors->get('name')" class="mt-2" />
        </div>

        <!-- Email -->
        <div class="mt-4">
            <x-input-label for="email" :value="__('Email address')" />

            <x-text-input
                id="email"
                class="block mt-1 w-full"
                type="email"
                name="email"
                :value="old('email')"
                required
                autocomplete="username"
            />

            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- Password -->
        <div class="mt-4">
            <x-input-label for="password" :value="__('Password')" />

            <x-text-input
                id="password"
                class="block mt-1 w-full"
                type="password"
                name="password"
                required
                autocomplete="new-password"
            />

            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Confirm Password -->
        <div class="mt-4">
            <x-input-label for="password_confirmation" :value="__('Confirm password')" />

            <x-text-input
                id="password_confirmation"
                class="block mt-1 w-full"
                type="password"
                name="password_confirmation"
                required
                autocomplete="new-password"
            />

            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>

        <div class="mt-6 rounded-2xl border border-blue-100 bg-blue-50 p-4">
            <p class="text-sm font-semibold text-slate-800">
                Developer access
            </p>

            <p class="mt-1 text-xs leading-5 text-slate-600">
                Your account will be created with Developer access. Developer accounts can use both developer tools and the User-side website functions.
            </p>
        </div>

        <div class="mt-6 flex items-center justify-between gap-4">
            <a
                class="text-sm font-medium text-slate-500 underline hover:text-slate-900"
                href="{{ route('developer.login') }}"
            >
                Already a developer?
            </a>

            <x-primary-button>
                Create Developer Account
            </x-primary-button>
        </div>
    </form>

</x-guest-layout>
