@extends('admin.layouts.app')

@section('title', 'Website Builder')

@section('content')

<div class="mb-8">
    <h1 class="text-3xl font-bold tracking-tight text-slate-900">
        Developer Website Builder
    </h1>
    <p class="mt-2 text-slate-500">
        Configure your website, compile it, and download the finished package.
    </p>
</div>

@if(session('build'))
    <div class="mb-6 rounded-2xl border border-emerald-200 bg-emerald-50 p-5">
        <p class="font-semibold text-emerald-800">
            Build project created
        </p>
        <p class="mt-1 text-sm text-emerald-700">
            {{ session('build')['project_name'] ?? '' }}
        </p>
        <p class="mt-1 text-xs text-emerald-600">
            Build ID: {{ session('build')['build_id'] ?? '' }}
        </p>
    </div>
@endif

<form method="POST"
      action="{{ route('developer.builder.create') }}"
      class="space-y-6">
    @csrf

    {{-- Project Information --}}
    <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
        <div class="mb-6">
            <h2 class="text-lg font-bold text-slate-900">Project Information</h2>
            <p class="mt-1 text-sm text-slate-500">
                Start with the basic information for your website.
            </p>
        </div>

        <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
            <div>
                <label for="project_name" class="block text-sm font-semibold text-slate-700">
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
                    class="mt-2 w-full rounded-xl border border-slate-300 px-4 py-3 outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                @error('project_name')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="website_type" class="block text-sm font-semibold text-slate-700">
                    Website Type
                </label>
                <select
                    id="website_type"
                    name="website_type"
                    required
                    class="mt-2 w-full rounded-xl border border-slate-300 bg-white px-4 py-3 outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                    <option value="">Select website type</option>
                    <option value="business" @selected(old('website_type') === 'business')>Business Website</option>
                    <option value="ecommerce" @selected(old('website_type') === 'ecommerce')>E-commerce Website</option>
                    <option value="portfolio" @selected(old('website_type') === 'portfolio')>Portfolio Website</option>
                    <option value="blog" @selected(old('website_type') === 'blog')>Blog / Magazine</option>
                    <option value="landing" @selected(old('website_type') === 'landing')>Landing Page</option>
                </select>
                @error('website_type')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
        </div>
    </section>

    {{-- Capacity --}}
    <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
        <div class="mb-6">
            <h2 class="text-lg font-bold text-slate-900">Core Capacity</h2>
            <p class="mt-1 text-sm text-slate-500">
                Choose the capacity bundle that will be included in the compiled website.
            </p>
        </div>

        @if($capacityBundles->isEmpty())
            <div class="rounded-2xl border border-dashed border-slate-300 bg-slate-50 p-6 text-center">
                <p class="font-semibold text-slate-700">No capacity bundles available</p>
                <p class="mt-1 text-sm text-slate-500">
                    Developer capacity products will appear here when made available by admin.
                </p>
            </div>
        @else
            <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-3">
                @foreach($capacityBundles as $bundle)
                    <label class="cursor-pointer">
                        <input
                            type="radio"
                            name="capacity_bundle"
                            value="{{ $bundle->id }}"
                            class="peer sr-only"
                            @checked(old('capacity_bundle') == $bundle->id)
                            required>

                        <div class="h-full rounded-2xl border border-slate-200 p-5 transition peer-checked:border-blue-500 peer-checked:bg-blue-50 peer-checked:ring-2 peer-checked:ring-blue-100 hover:border-blue-300">
                            <div class="flex items-start justify-between gap-4">
                                <div>
                                    <p class="font-bold text-slate-900">{{ $bundle->name }}</p>
                                    @if($bundle->description)
                                        <p class="mt-2 text-sm text-slate-500">
                                            {{ $bundle->description }}
                                        </p>
                                    @endif
                                </div>

                                <span class="shrink-0 rounded-full bg-blue-100 px-3 py-1 text-xs font-bold text-blue-700">
                                    Developer
                                </span>
                            </div>

                            <p class="mt-5 text-xl font-bold text-slate-900">
                                ₦{{ number_format($bundle->price, 2) }}
                            </p>
                        </div>
                    </label>
                @endforeach
            </div>
        @endif

        @error('capacity_bundle')
            <p class="mt-3 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </section>

    {{-- Theme --}}
    <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
        <div class="mb-6">
            <h2 class="text-lg font-bold text-slate-900">Theme</h2>
            <p class="mt-1 text-sm text-slate-500">
                Select a marketplace theme or generate a custom theme with AI.
            </p>
        </div>

        @if($themes->isEmpty())
            <div class="mb-5 rounded-2xl border border-dashed border-slate-300 bg-slate-50 p-6 text-center">
                <p class="font-semibold text-slate-700">No marketplace themes available</p>
                <p class="mt-1 text-sm text-slate-500">
                    Themes added by admin will appear here automatically.
                </p>
            </div>
        @else
            <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-3">
                @foreach($themes as $theme)
                    <label class="cursor-pointer">
                        <input
                            type="radio"
                            name="theme"
                            value="{{ $theme->id }}"
                            class="peer sr-only"
                            @checked(old('theme') == $theme->id)>

                        <div class="h-full rounded-2xl border border-slate-200 p-5 transition peer-checked:border-blue-500 peer-checked:bg-blue-50 peer-checked:ring-2 peer-checked:ring-blue-100 hover:border-blue-300">
                            <p class="font-bold text-slate-900">{{ $theme->name }}</p>
                            @if($theme->description)
                                <p class="mt-2 text-sm text-slate-500">
                                    {{ $theme->description }}
                                </p>
                            @endif
                        </div>
                    </label>
                @endforeach
            </div>
        @endif

        <label class="mt-5 block cursor-pointer">
            <input
                id="ai_theme"
                type="checkbox"
                name="ai_theme"
                value="1"
                class="peer sr-only"
                @checked(old('ai_theme'))>

            <div class="rounded-2xl border border-violet-200 bg-violet-50 p-5 transition peer-checked:border-violet-500 peer-checked:ring-2 peer-checked:ring-violet-100">
                <div class="flex items-start gap-4">
                    <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-violet-600 text-lg text-white">
                        ✨
                    </div>
                    <div>
                        <p class="font-bold text-violet-900">Generate a Custom Theme with AI</p>
                        <p class="mt-1 text-sm text-violet-700">
                            Describe the visual style you want and generate a custom theme using your account's AI credits.
                        </p>
                    </div>
                </div>
            </div>
        </label>

        <div id="ai-theme-panel" class="mt-4 hidden rounded-2xl border border-violet-200 bg-white p-5">
            <label for="ai_theme_prompt" class="block text-sm font-semibold text-slate-700">
                Describe your theme
            </label>
            <textarea
                id="ai_theme_prompt"
                name="ai_theme_prompt"
                rows="5"
                maxlength="5000"
                placeholder="Example: Create a modern dark fintech theme with deep navy backgrounds, electric blue accents, clean typography, glass-effect cards and subtle animations."
                class="mt-2 w-full rounded-xl border border-slate-300 px-4 py-3 outline-none transition focus:border-violet-500 focus:ring-2 focus:ring-violet-100">{{ old('ai_theme_prompt') }}</textarea>

            <div class="mt-4 grid grid-cols-1 gap-4 md:grid-cols-3">
                <select name="ai_theme_preferences[mode]"
                        class="rounded-xl border border-slate-300 px-4 py-3 text-sm">
                    <option value="">Colour mode</option>
                    <option value="light" @selected(old('ai_theme_preferences.mode') === 'light')>Light</option>
                    <option value="dark" @selected(old('ai_theme_preferences.mode') === 'dark')>Dark</option>
                    <option value="mixed" @selected(old('ai_theme_preferences.mode') === 'mixed')>Mixed</option>
                </select>

                <select name="ai_theme_preferences[style]"
                        class="rounded-xl border border-slate-300 px-4 py-3 text-sm">
                    <option value="">Style</option>
                    <option value="minimal" @selected(old('ai_theme_preferences.style') === 'minimal')>Minimal</option>
                    <option value="modern" @selected(old('ai_theme_preferences.style') === 'modern')>Modern</option>
                    <option value="corporate" @selected(old('ai_theme_preferences.style') === 'corporate')>Corporate</option>
                    <option value="bold" @selected(old('ai_theme_preferences.style') === 'bold')>Bold</option>
                    <option value="editorial" @selected(old('ai_theme_preferences.style') === 'editorial')>Editorial</option>
                </select>

                <select name="ai_theme_preferences[animation]"
                        class="rounded-xl border border-slate-300 px-4 py-3 text-sm">
                    <option value="">Animation</option>
                    <option value="none" @selected(old('ai_theme_preferences.animation') === 'none')>None</option>
                    <option value="subtle" @selected(old('ai_theme_preferences.animation') === 'subtle')>Subtle</option>
                    <option value="dynamic" @selected(old('ai_theme_preferences.animation') === 'dynamic')>Dynamic</option>
                </select>
            </div>

            @error('ai_theme_prompt')
                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        @error('theme')
            <p class="mt-3 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </section>

    {{-- Modules --}}
    <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
        <div class="mb-6">
            <h2 class="text-lg font-bold text-slate-900">Modules</h2>
            <p class="mt-1 text-sm text-slate-500">
                Add the business components required by this website.
            </p>
        </div>

        @if($modules->isEmpty())
            <div class="rounded-2xl border border-dashed border-slate-300 bg-slate-50 p-6 text-center">
                <p class="font-semibold text-slate-700">No developer modules available</p>
                <p class="mt-1 text-sm text-slate-500">
                    Modules connected from the marketplace will appear here automatically.
                </p>
            </div>
        @else
            <div class="grid grid-cols-1 gap-3 md:grid-cols-2 xl:grid-cols-3">
                @foreach($modules as $module)
                    <label class="cursor-pointer">
                        <input
                            type="checkbox"
                            name="modules[]"
                            value="{{ $module->id }}"
                            class="peer sr-only"
                            @checked(in_array($module->id, old('modules', [])))>

                        <div class="flex h-full items-start justify-between gap-4 rounded-2xl border border-slate-200 p-5 transition peer-checked:border-blue-500 peer-checked:bg-blue-50 peer-checked:ring-2 peer-checked:ring-blue-100 hover:border-blue-300">
                            <div>
                                <p class="font-bold text-slate-900">{{ $module->name }}</p>
                                @if($module->description)
                                    <p class="mt-1 text-sm text-slate-500">
                                        {{ $module->description }}
                                    </p>
                                @endif
                            </div>

                            <span class="shrink-0 text-sm font-bold text-slate-900">
                                Module
                            </span>
                        </div>
                    </label>
                @endforeach
            </div>
        @endif

        @error('modules')
            <p class="mt-3 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </section>

    {{-- Submit --}}
    <section class="rounded-3xl bg-slate-900 p-6 text-white shadow-xl">
        <div class="flex flex-col gap-5 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <p class="text-sm font-semibold uppercase tracking-wider text-blue-300">
                    Ready to build
                </p>
                <h2 class="mt-1 text-2xl font-bold">
                    Compile your website
                </h2>
                <p class="mt-1 text-sm text-slate-300">
                    Your completed package will be compiled first. Payment will release the download.
                </p>
            </div>

            <button
                type="submit"
                class="inline-flex items-center justify-center rounded-2xl bg-blue-600 px-7 py-3 font-bold text-white shadow-lg transition hover:bg-blue-500">
                Compile Website →
            </button>
        </div>
    </section>
</form>

<script>
    (() => {
        const checkbox = document.getElementById('ai_theme');
        const panel = document.getElementById('ai-theme-panel');

        if (!checkbox || !panel) {
            return;
        }

        const sync = () => {
            panel.classList.toggle('hidden', !checkbox.checked);
        };

        checkbox.addEventListener('change', sync);
        sync();
    })();
</script>

@endsection

