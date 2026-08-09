@extends('admin.layouts.app')

@section('title', 'Website Builder')

@section('content')

<div class="max-w-7xl mx-auto space-y-8">

    <div>
        <h1 class="text-3xl font-bold text-slate-800">
            Developer Website Builder
        </h1>

        <p class="mt-2 text-slate-500">
            Build, compile and download developer websites.
        </p>
    </div>

    <div class="rounded-2xl bg-white border border-slate-200 shadow-sm p-8">

        <h2 class="text-xl font-semibold text-slate-800">
            Create Build
        </h2>

        <p class="mt-2 text-slate-500">
            Configure a website project for compilation and download.
        </p>

        @if(session('build'))
            <div class="mt-6 rounded-xl bg-emerald-50 border border-emerald-200 p-5">
                <p class="font-semibold text-emerald-800">
                    Build project created
                </p>

                <p class="mt-1 text-sm text-emerald-700">
                    {{ session('build')['project_name'] }}
                </p>

                <p class="mt-1 text-xs text-emerald-600">
                    Build ID: {{ session('build')['build_id'] }}
                </p>
            </div>
        @endif

        <form method="POST"
              action="{{ route('developer.builder.create') }}"
              class="mt-6">

            @csrf

            <label for="project_name"
                   class="block text-sm font-medium text-slate-700">
                Project Name
            </label>

            <input
                id="project_name"
                name="project_name"
                type="text"
                required
                maxlength="100"
                value="{{ old('project_name') }}"
                placeholder="My Developer Website"
                class="mt-2 w-full rounded-xl border border-slate-300 px-4 py-3 focus:border-blue-500 focus:ring-blue-500">

            @error('project_name')
                <p class="mt-2 text-sm text-red-600">
                    {{ $message }}
                </p>
            @enderror

            <label for="website_type"
                   class="block mt-6 text-sm font-medium text-slate-700">
                Website Type
            </label>

            <select
                id="website_type"
                name="website_type"
                required
                class="mt-2 w-full rounded-xl border border-slate-300 px-4 py-3 focus:border-blue-500 focus:ring-blue-500">

                <option value="">Select website type</option>
                <option value="business">Business Website</option>
                <option value="ecommerce">E-commerce Website</option>
                <option value="portfolio">Portfolio Website</option>
                <option value="blog">Blog / Magazine</option>
                <option value="landing">Landing Page</option>

            </select>

            @error('website_type')
                <p class="mt-2 text-sm text-red-600">
                    {{ $message }}
                </p>
            @enderror

            <label for="capacity_bundle"
                   class="block mt-6 text-sm font-medium text-slate-700">
                Capacity Bundle
            </label>

            <select
                id="capacity_bundle"
                name="capacity_bundle"
                required
                class="mt-2 w-full rounded-xl border border-slate-300 px-4 py-3 focus:border-blue-500 focus:ring-blue-500">

                <option value="">Select capacity bundle</option>

                @foreach($capacityBundles as $bundle)
                    <option
                        value="{{ $bundle->id }}"
                        {{ old('capacity_bundle') == $bundle->id ? 'selected' : '' }}>
                        {{ $bundle->name }} — ₦{{ number_format($bundle->price, 2) }}
                    </option>
                @endforeach

            </select>

            @error('capacity_bundle')
                <p class="mt-2 text-sm text-red-600">
                    {{ $message }}
                </p>
            @enderror

            <label for="theme"
                   class="block mt-6 text-sm font-medium text-slate-700">
                Theme
            </label>

            <select
                id="theme"
                name="theme"
                required
                class="mt-2 w-full rounded-xl border border-slate-300 px-4 py-3 focus:border-blue-500 focus:ring-blue-500">

                <option value="">Select theme</option>
                <option value="default">Esubiz Default</option>
                <option value="minimal">Minimal</option>
                <option value="modern">Modern</option>
                <option value="corporate">Corporate</option>

            </select>

            @error('theme')
                <p class="mt-2 text-sm text-red-600">
                    {{ $message }}
                </p>
            @enderror

            <div class="mt-6">

                <h3 class="text-sm font-medium text-slate-700">
                    Modules
                </h3>

                <p class="mt-1 text-xs text-slate-500">
                    Select all modules required for this website.
                </p>

                <div class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-3">

                    @foreach([
                        'ecommerce' => 'E-commerce',
                        'hotel' => 'Hotel',
                        'restaurant' => 'Restaurant',
                        'school' => 'School',
                        'church' => 'Church',
                        'corporate' => 'Corporate',
                        'crypto' => 'Crypto',
                        'investment' => 'Investment',
                        'courier' => 'Courier',
                        'fleet-manager' => 'Fleet Manager',
                        'logistics' => 'Logistics',
                        'app-generator' => 'App Generator',
                    ] as $value => $label)

                        <label class="flex items-center gap-3 rounded-xl border border-slate-200 p-4 hover:bg-slate-50 cursor-pointer">

                            <input
                                type="checkbox"
                                name="modules[]"
                                value="{{ $value }}"
                                class="rounded border-slate-300 text-blue-600 focus:ring-blue-500"
                                {{ in_array($value, old('modules', [])) ? 'checked' : '' }}>

                            <span class="text-sm font-medium text-slate-700">
                                {{ $label }}
                            </span>

                        </label>

                    @endforeach

                </div>

            </div>

            @error('modules')
                <p class="mt-2 text-sm text-red-600">
                    {{ $message }}
                </p>
            @enderror

            <div class="mt-8 rounded-2xl border border-violet-200 bg-violet-50 p-6">

                <div class="flex items-start gap-4">

                    <input
                        id="ai_theme"
                        type="checkbox"
                        name="ai_theme"
                        value="1"
                        class="mt-1 rounded border-violet-300 text-violet-600 focus:ring-violet-500"
                        {{ old('ai_theme') ? 'checked' : '' }}>

                    <div>

                        <label for="ai_theme"
                               class="font-semibold text-violet-900 cursor-pointer">
                            Generate Theme with AI
                        </label>

                        <p class="mt-1 text-sm text-violet-700">
                            Use AI to generate a custom theme for this developer website.
                            The generated theme can later be added to the Esubiz core
                            through the developer development and vetting system.
                        </p>

                    </div>

                </div>

            </div>

            @error('ai_theme')
                <p class="mt-2 text-sm text-red-600">
                    {{ $message }}
                </p>
            @enderror

            <button
                type="submit"
                class="mt-5 rounded-xl bg-blue-600 px-6 py-3 font-semibold text-white hover:bg-blue-700">
                Create Build Project
            </button>

        </form>

    </div>

</div>

@endsection
