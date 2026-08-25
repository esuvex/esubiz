@extends('tenant.admin.layouts.app')

@section('title', 'Configure Business Theme')

@section('content')

@php
    $assetUrl = function ($path) {
        return $path
            ? url('/theme-assets/' . ltrim($path, '/'))
            : null;
    };
@endphp

<div class="mx-auto max-w-7xl space-y-8">

    <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">

        <div>
            <div class="text-xs font-black uppercase tracking-[.16em] text-blue-600">
                Business · v1.0
            </div>

            <h1 class="mt-2 text-3xl font-black tracking-tight text-slate-900">
                Theme Configuration
            </h1>

            <p class="mt-2 max-w-3xl text-sm leading-6 text-slate-500">
                Every visible homepage section can be customized here.
            </p>
        </div>

        <div class="flex gap-3">

            <a
                href="{{ route(
                    'tenant.cms.themes.index',
                    ['subdomain' => $website->subdomain]
                ) }}"
                class="rounded-xl border border-slate-200 bg-white px-5 py-3 text-sm font-black text-slate-700"
            >
                ← Themes
            </a>

            <a
                href="https://{{ $website->subdomain }}.esubiz.com"
                target="_blank"
                class="rounded-xl bg-slate-900 px-5 py-3 text-sm font-black text-white"
            >
                View Website ↗
            </a>

        </div>

    </div>


    @if(session('success'))
        <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-5 py-4 text-sm font-bold text-emerald-700">
            {{ session('success') }}
        </div>
    @endif


    @if($errors->any())
        <div class="rounded-2xl border border-red-200 bg-red-50 px-5 py-4 text-sm text-red-700">
            <div class="font-black">
                Please correct the following:
            </div>

            <ul class="mt-2 list-disc space-y-1 pl-5">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif


    <form
        method="POST"
        enctype="multipart/form-data"
        action="{{ route(
            'tenant.cms.themes.business.update',
            ['subdomain' => $website->subdomain]
        ) }}"
        class="space-y-8"
        id="businessThemeForm"
    >
        @csrf


        {{-- ==================================================
             BRAND + HEADER
        =================================================== --}}

        <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm sm:p-8">

            <div>
                <div class="text-xs font-black uppercase tracking-[.14em] text-blue-600">
                    Header
                </div>

                <h2 class="mt-2 text-xl font-black text-slate-900">
                    Brand & Navigation
                </h2>
            </div>


            <div class="mt-6 grid gap-6 lg:grid-cols-2">

                @foreach([
                    ['primary_color', 'Primary Color'],
                    ['secondary_color', 'Secondary Color'],
                ] as [$key, $label])

                    <div>
                        <label class="text-xs font-black uppercase tracking-wide text-slate-500">
                            {{ $label }}
                        </label>

                        <div class="mt-2 flex gap-3">

                            <input
                                type="color"
                                value="{{ old($key, $theme[$key]) }}"
                                class="h-12 w-16 cursor-pointer rounded-xl border border-slate-200 bg-white p-1"
                                oninput="this.nextElementSibling.value=this.value"
                            >

                            <input
                                type="text"
                                name="{{ $key }}"
                                value="{{ old($key, $theme[$key]) }}"
                                class="w-full rounded-xl border border-slate-200 px-4 py-3 text-sm font-bold"
                                oninput="
                                    if(/^#[0-9A-Fa-f]{6}$/.test(this.value)){
                                        this.previousElementSibling.value=this.value;
                                    }
                                "
                            >

                        </div>
                    </div>

                @endforeach

            </div>


            <div class="mt-7 grid gap-6 lg:grid-cols-2">

                <div class="rounded-2xl border border-slate-200 bg-slate-50 p-5">

                    <div class="font-black text-slate-900">
                        Header Logo
                    </div>

                    <div class="mt-1 text-xs text-slate-500">
                        Recommended: 180 × 60 px · PNG, JPG or WebP · Max 2 MB.
                    </div>

                    @if(!empty($theme['logo_path']))
                        <div class="mt-4 rounded-xl bg-white p-4">
                            <img
                                src="{{ $assetUrl($theme['logo_path']) }}"
                                alt="Current logo"
                                class="max-h-16 max-w-[220px] object-contain"
                            >
                        </div>
                    @endif

                    <input
                        type="file"
                        name="logo"
                        accept="image/*"
                        class="mt-4 block w-full text-sm"
                    >

                </div>


                <div class="rounded-2xl border border-slate-200 bg-slate-50 p-5">

                    <div class="font-black text-slate-900">
                        Favicon
                    </div>

                    <div class="mt-1 text-xs text-slate-500">
                        Recommended: 64 × 64 px or 128 × 128 px square · Max 1 MB.
                    </div>

                    @if(!empty($theme['favicon_path']))
                        <div class="mt-4">
                            <img
                                src="{{ $assetUrl($theme['favicon_path']) }}"
                                alt="Current favicon"
                                class="h-14 w-14 rounded-xl object-cover"
                            >
                        </div>
                    @endif

                    <input
                        type="file"
                        name="favicon"
                        accept="image/*"
                        class="mt-4 block w-full text-sm"
                    >

                </div>

            </div>


            <div class="mt-7">

                <div class="text-sm font-black text-slate-900">
                    Header Menu Labels
                </div>

                <div class="mt-4 grid gap-4 md:grid-cols-2 lg:grid-cols-3">

                    @foreach([
                        ['nav_home_label', 'Home'],
                        ['nav_about_label', 'About'],
                        ['nav_faq_label', 'FAQs'],
                        ['nav_contact_label', 'Contact'],
                        ['nav_login_label', 'Login'],
                        ['nav_register_label', 'Register'],
                    ] as [$key, $label])

                        <div>
                            <label class="text-xs font-bold text-slate-500">
                                {{ $label }}
                            </label>

                            <input
                                type="text"
                                name="{{ $key }}"
                                value="{{ old($key, $theme[$key] ?? '') }}"
                                class="mt-1 w-full rounded-xl border border-slate-200 px-4 py-3 text-sm"
                            >
                        </div>

                    @endforeach

                </div>

            </div>

        </section>


        {{-- ==================================================
             HERO
        =================================================== --}}

        <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm sm:p-8">

            <div class="text-xs font-black uppercase tracking-[.14em] text-blue-600">
                Homepage
            </div>

            <h2 class="mt-2 text-xl font-black text-slate-900">
                Hero Section
            </h2>


            <div class="mt-6 grid gap-6 xl:grid-cols-[1.15fr_.85fr]">

                <div class="space-y-5">

                    @foreach([
                        ['hero_badge', 'Badge'],
                        ['hero_title', 'Main Heading'],
                        ['hero_subtitle', 'Description'],
                        ['cta_label', 'Primary Button Text'],
                        ['cta_url', 'Primary Button Link'],
                        ['secondary_cta_label', 'Secondary Button Text'],
                        ['secondary_cta_url', 'Secondary Button Link'],
                    ] as [$key, $label])

                        <div>
                            <label class="text-xs font-black uppercase tracking-wide text-slate-500">
                                {{ $label }}
                            </label>

                            @if($key === 'hero_subtitle')
                                <textarea
                                    name="{{ $key }}"
                                    rows="4"
                                    class="mt-2 w-full rounded-xl border border-slate-200 px-4 py-3 text-sm"
                                >{{ old($key, $theme[$key] ?? '') }}</textarea>
                            @else
                                <input
                                    type="text"
                                    name="{{ $key }}"
                                    value="{{ old($key, $theme[$key] ?? '') }}"
                                    class="mt-2 w-full rounded-xl border border-slate-200 px-4 py-3 text-sm"
                                >
                            @endif
                        </div>

                    @endforeach

                </div>


                <div class="rounded-2xl border border-blue-100 bg-blue-50/60 p-5">

                    <div class="font-black text-slate-900">
                        Hero Image
                    </div>

                    <div class="mt-1 text-xs leading-5 text-slate-500">
                        Recommended: 1200 × 800 px.<br>
                        Allowed: 600–3000 px wide and 400–2200 px high.<br>
                        Max upload: 4 MB.
                    </div>

                    @if(!empty($theme['hero_image_path']))
                        <img
                            src="{{ $assetUrl($theme['hero_image_path']) }}"
                            alt="Current hero image"
                            class="mt-4 aspect-[3/2] w-full rounded-2xl object-cover"
                        >
                    @endif

                    <input
                        type="file"
                        name="hero_image"
                        accept="image/*"
                        class="mt-4 block w-full text-sm"
                    >

                </div>

            </div>

        </section>


        {{-- ==================================================
             FEATURES
        =================================================== --}}

        <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm sm:p-8">

            <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">

                <div>
                    <div class="text-xs font-black uppercase tracking-[.14em] text-blue-600">
                        Homepage
                    </div>

                    <h2 class="mt-2 text-xl font-black text-slate-900">
                        Features Section
                    </h2>

                    <p class="mt-1 text-sm text-slate-500">
                        Features can be added or removed without a fixed maximum.
                    </p>
                </div>

                <button
                    type="button"
                    id="addFeature"
                    class="rounded-xl bg-blue-50 px-4 py-2.5 text-sm font-black text-blue-600"
                >
                    + Add Feature
                </button>

            </div>


            <div class="mt-6 grid gap-5">

                @foreach([
                    ['features_badge', 'Badge'],
                    ['features_title', 'Section Heading'],
                    ['features_subtitle', 'Section Description'],
                ] as [$key, $label])

                    <div>
                        <label class="text-xs font-black uppercase tracking-wide text-slate-500">
                            {{ $label }}
                        </label>

                        @if($key === 'features_subtitle')
                            <textarea
                                name="{{ $key }}"
                                rows="3"
                                class="mt-2 w-full rounded-xl border border-slate-200 px-4 py-3 text-sm"
                            >{{ old($key, $theme[$key] ?? '') }}</textarea>
                        @else
                            <input
                                type="text"
                                name="{{ $key }}"
                                value="{{ old($key, $theme[$key] ?? '') }}"
                                class="mt-2 w-full rounded-xl border border-slate-200 px-4 py-3 text-sm"
                            >
                        @endif
                    </div>

                @endforeach

            </div>


            <div
                id="featureList"
                class="mt-7 grid gap-5"
            >
                @foreach(old('features', $features) as $index => $feature)

                    <div class="repeatable-feature rounded-2xl border border-slate-200 bg-slate-50 p-5">

                        <div class="flex items-center justify-between">

                            <div class="font-black text-slate-900">
                                Feature
                            </div>

                            <button
                                type="button"
                                class="remove-repeatable text-xs font-black text-red-600"
                            >
                                Delete
                            </button>

                        </div>


                        <div class="mt-4 grid gap-5 lg:grid-cols-[1fr_260px]">

                            <div class="space-y-4">

                                <input
                                    type="text"
                                    name="features[{{ $index }}][title]"
                                    value="{{ $feature['title'] ?? '' }}"
                                    placeholder="Feature title"
                                    class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm"
                                >

                                <textarea
                                    name="features[{{ $index }}][text]"
                                    rows="4"
                                    placeholder="Feature description"
                                    class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm"
                                >{{ $feature['text'] ?? '' }}</textarea>

                            </div>


                            <div>

                                <div class="text-xs font-bold text-slate-500">
                                    Feature Image
                                </div>

                                <div class="mt-1 text-[11px] leading-5 text-slate-400">
                                    Recommended: 800 × 520 px.
                                </div>

                                @if(!empty($feature['image_path']))
                                    <img
                                        src="{{ $assetUrl($feature['image_path']) }}"
                                        class="mt-3 aspect-[800/520] w-full rounded-xl object-cover"
                                        alt=""
                                    >
                                @endif

                                <input
                                    type="hidden"
                                    name="features[{{ $index }}][existing_image]"
                                    value="{{ $feature['image_path'] ?? '' }}"
                                >

                                <input
                                    type="file"
                                    name="features[{{ $index }}][image]"
                                    accept="image/*"
                                    class="mt-3 block w-full text-xs"
                                >

                            </div>

                        </div>

                    </div>

                @endforeach
            </div>

        </section>


        {{-- ==================================================
             STATISTICS
        =================================================== --}}

        <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm sm:p-8">

            <div class="text-xs font-black uppercase tracking-[.14em] text-blue-600">
                Homepage
            </div>

            <h2 class="mt-2 text-xl font-black text-slate-900">
                Statistics
            </h2>

            <div class="mt-6">

                <label class="text-xs font-black uppercase tracking-wide text-slate-500">
                    Badge
                </label>

                <input
                    type="text"
                    name="stats_badge"
                    value="{{ old('stats_badge', $theme['stats_badge'] ?? '') }}"
                    class="mt-2 w-full rounded-xl border border-slate-200 px-4 py-3 text-sm"
                >

            </div>


            <div class="mt-6 grid gap-5 md:grid-cols-3">

                @for($i = 1; $i <= 3; $i++)

                    <div class="rounded-2xl bg-slate-50 p-5">

                        <div class="text-sm font-black text-slate-900">
                            Statistic {{ $i }}
                        </div>

                        <input
                            type="text"
                            name="stat_{{ $i }}_value"
                            value="{{ old(
                                'stat_' . $i . '_value',
                                $theme['stat_' . $i . '_value'] ?? ''
                            ) }}"
                            placeholder="Value"
                            class="mt-4 w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm"
                        >

                        <input
                            type="text"
                            name="stat_{{ $i }}_label"
                            value="{{ old(
                                'stat_' . $i . '_label',
                                $theme['stat_' . $i . '_label'] ?? ''
                            ) }}"
                            placeholder="Label"
                            class="mt-3 w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm"
                        >

                    </div>

                @endfor

            </div>

        </section>


        {{-- ==================================================
             ABOUT
        =================================================== --}}

        <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm sm:p-8">

            <div class="text-xs font-black uppercase tracking-[.14em] text-blue-600">
                Homepage
            </div>

            <h2 class="mt-2 text-xl font-black text-slate-900">
                About Section
            </h2>


            <div class="mt-6 grid gap-6 xl:grid-cols-[1.15fr_.85fr]">

                <div class="space-y-5">

                    @foreach([
                        ['about_badge', 'Badge'],
                        ['about_title', 'Heading'],
                        ['about_text', 'Description'],
                        ['about_cta_label', 'Button Text'],
                        ['about_cta_url', 'Button Link'],
                    ] as [$key, $label])

                        <div>
                            <label class="text-xs font-black uppercase tracking-wide text-slate-500">
                                {{ $label }}
                            </label>

                            @if($key === 'about_text')
                                <textarea
                                    name="{{ $key }}"
                                    rows="5"
                                    class="mt-2 w-full rounded-xl border border-slate-200 px-4 py-3 text-sm"
                                >{{ old($key, $theme[$key] ?? '') }}</textarea>
                            @else
                                <input
                                    type="text"
                                    name="{{ $key }}"
                                    value="{{ old($key, $theme[$key] ?? '') }}"
                                    class="mt-2 w-full rounded-xl border border-slate-200 px-4 py-3 text-sm"
                                >
                            @endif
                        </div>

                    @endforeach

                </div>


                <div class="rounded-2xl border border-blue-100 bg-blue-50/60 p-5">

                    <div class="font-black text-slate-900">
                        About Image
                    </div>

                    <div class="mt-1 text-xs leading-5 text-slate-500">
                        Recommended: 1000 × 800 px.<br>
                        Max upload: 4 MB.
                    </div>

                    @if(!empty($theme['about_image_path']))
                        <img
                            src="{{ $assetUrl($theme['about_image_path']) }}"
                            class="mt-4 aspect-[5/4] w-full rounded-2xl object-cover"
                            alt=""
                        >
                    @endif

                    <input
                        type="file"
                        name="about_image"
                        accept="image/*"
                        class="mt-4 block w-full text-sm"
                    >

                </div>

            </div>

        </section>


        {{-- ==================================================
             TESTIMONIAL SLIDER
        =================================================== --}}

        <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm sm:p-8">

            <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">

                <div>
                    <div class="text-xs font-black uppercase tracking-[.14em] text-blue-600">
                        Homepage
                    </div>

                    <h2 class="mt-2 text-xl font-black text-slate-900">
                        Testimonials Slider
                    </h2>

                    <p class="mt-1 text-sm text-slate-500">
                        Business comes with 6 testimonials by default. Add or delete as many as required.
                    </p>
                </div>

                <button
                    type="button"
                    id="addTestimonial"
                    class="rounded-xl bg-blue-50 px-4 py-2.5 text-sm font-black text-blue-600"
                >
                    + Add Testimonial
                </button>

            </div>


            <div class="mt-6 grid gap-5">

                <input
                    type="text"
                    name="testimonials_badge"
                    value="{{ old(
                        'testimonials_badge',
                        $theme['testimonials_badge'] ?? ''
                    ) }}"
                    placeholder="Section badge"
                    class="rounded-xl border border-slate-200 px-4 py-3 text-sm"
                >

                <input
                    type="text"
                    name="testimonials_title"
                    value="{{ old(
                        'testimonials_title',
                        $theme['testimonials_title'] ?? ''
                    ) }}"
                    placeholder="Section heading"
                    class="rounded-xl border border-slate-200 px-4 py-3 text-sm"
                >

                <textarea
                    name="testimonials_subtitle"
                    rows="3"
                    placeholder="Section description"
                    class="rounded-xl border border-slate-200 px-4 py-3 text-sm"
                >{{ old(
                    'testimonials_subtitle',
                    $theme['testimonials_subtitle'] ?? ''
                ) }}</textarea>

            </div>


            <div
                id="testimonialList"
                class="mt-7 grid gap-5 lg:grid-cols-2"
            >

                @foreach(old('testimonials', $testimonials) as $index => $testimonial)

                    <div class="repeatable-testimonial rounded-2xl border border-slate-200 bg-slate-50 p-5">

                        <div class="flex justify-end">

                            <button
                                type="button"
                                class="remove-repeatable text-xs font-black text-red-600"
                            >
                                Delete
                            </button>

                        </div>


                        <div class="mt-3 grid gap-4">

                            <div class="grid gap-4 md:grid-cols-2">

                                <input
                                    type="text"
                                    name="testimonials[{{ $index }}][name]"
                                    value="{{ $testimonial['name'] ?? '' }}"
                                    placeholder="Customer name"
                                    class="rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm"
                                >

                                <input
                                    type="text"
                                    name="testimonials[{{ $index }}][role]"
                                    value="{{ $testimonial['role'] ?? '' }}"
                                    placeholder="Role / company"
                                    class="rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm"
                                >

                            </div>


                            <textarea
                                name="testimonials[{{ $index }}][text]"
                                rows="4"
                                placeholder="Testimonial"
                                class="rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm"
                            >{{ $testimonial['text'] ?? '' }}</textarea>


                            <div>

                                <div class="text-xs font-bold text-slate-500">
                                    Customer Photo
                                </div>

                                <div class="mt-1 text-[11px] text-slate-400">
                                    Recommended: 160 × 160 px square.
                                </div>

                                @if(!empty($testimonial['photo_path']))
                                    <img
                                        src="{{ $assetUrl($testimonial['photo_path']) }}"
                                        class="mt-3 h-16 w-16 rounded-full object-cover"
                                        alt=""
                                    >
                                @endif

                                <input
                                    type="hidden"
                                    name="testimonials[{{ $index }}][existing_photo]"
                                    value="{{ $testimonial['photo_path'] ?? '' }}"
                                >

                                <input
                                    type="file"
                                    name="testimonials[{{ $index }}][photo]"
                                    accept="image/*"
                                    class="mt-3 block w-full text-xs"
                                >

                            </div>

                        </div>

                    </div>

                @endforeach

            </div>

        </section>


        {{-- ==================================================
             FINAL CTA
        =================================================== --}}

        <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm sm:p-8">

            <div class="text-xs font-black uppercase tracking-[.14em] text-blue-600">
                Homepage
            </div>

            <h2 class="mt-2 text-xl font-black text-slate-900">
                Final Call to Action
            </h2>

            <div class="mt-6 grid gap-5">

                @foreach([
                    ['final_cta_badge', 'Badge'],
                    ['final_cta_title', 'Heading'],
                    ['final_cta_text', 'Description'],
                    ['final_cta_label', 'Button Text'],
                    ['final_cta_url', 'Button Link'],
                ] as [$key, $label])

                    <div>
                        <label class="text-xs font-black uppercase tracking-wide text-slate-500">
                            {{ $label }}
                        </label>

                        @if($key === 'final_cta_text')
                            <textarea
                                name="{{ $key }}"
                                rows="3"
                                class="mt-2 w-full rounded-xl border border-slate-200 px-4 py-3 text-sm"
                            >{{ old($key, $theme[$key] ?? '') }}</textarea>
                        @else
                            <input
                                type="text"
                                name="{{ $key }}"
                                value="{{ old($key, $theme[$key] ?? '') }}"
                                class="mt-2 w-full rounded-xl border border-slate-200 px-4 py-3 text-sm"
                            >
                        @endif
                    </div>

                @endforeach

            </div>

        </section>


        {{-- ==================================================
             FOOTER
        =================================================== --}}

        <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm sm:p-8">

            <div class="text-xs font-black uppercase tracking-[.14em] text-blue-600">
                Website Footer
            </div>

            <h2 class="mt-2 text-xl font-black text-slate-900">
                Footer Content, Images & Links
            </h2>


            <div class="mt-6 grid gap-6 xl:grid-cols-2">

                <div class="space-y-5">

                    <div>
                        <label class="text-xs font-black uppercase tracking-wide text-slate-500">
                            Footer Heading
                        </label>

                        <input
                            type="text"
                            name="footer_heading"
                            value="{{ old(
                                'footer_heading',
                                $theme['footer_heading'] ?? ''
                            ) }}"
                            placeholder="{{ $website->name }}"
                            class="mt-2 w-full rounded-xl border border-slate-200 px-4 py-3 text-sm"
                        >
                    </div>

                    <div>
                        <label class="text-xs font-black uppercase tracking-wide text-slate-500">
                            Footer Description
                        </label>

                        <textarea
                            name="footer_text"
                            rows="4"
                            class="mt-2 w-full rounded-xl border border-slate-200 px-4 py-3 text-sm"
                        >{{ old(
                            'footer_text',
                            $theme['footer_text'] ?? ''
                        ) }}</textarea>
                    </div>

                    <div>
                        <label class="text-xs font-black uppercase tracking-wide text-slate-500">
                            Copyright Text
                        </label>

                        <input
                            type="text"
                            name="footer_copyright"
                            value="{{ old(
                                'footer_copyright',
                                $theme['footer_copyright'] ?? ''
                            ) }}"
                            class="mt-2 w-full rounded-xl border border-slate-200 px-4 py-3 text-sm"
                        >
                    </div>

                </div>


                <div class="grid gap-5">

                    <div class="rounded-2xl bg-slate-50 p-5">

                        <div class="font-black text-slate-900">
                            Footer Logo
                        </div>

                        <div class="mt-1 text-xs text-slate-500">
                            Recommended: 180 × 60 px.
                        </div>

                        @if(!empty($theme['footer_logo_path']))
                            <img
                                src="{{ $assetUrl($theme['footer_logo_path']) }}"
                                class="mt-3 max-h-16 max-w-[220px] object-contain"
                                alt=""
                            >
                        @endif

                        <input
                            type="file"
                            name="footer_logo"
                            accept="image/*"
                            class="mt-3 block w-full text-sm"
                        >

                    </div>


                    <div class="rounded-2xl bg-slate-50 p-5">

                        <div class="font-black text-slate-900">
                            Footer Background Image
                        </div>

                        <div class="mt-1 text-xs text-slate-500">
                            Recommended: 1600 × 700 px.
                        </div>

                        @if(!empty($theme['footer_background_path']))
                            <img
                                src="{{ $assetUrl($theme['footer_background_path']) }}"
                                class="mt-3 aspect-[16/7] w-full rounded-xl object-cover"
                                alt=""
                            >
                        @endif

                        <input
                            type="file"
                            name="footer_background"
                            accept="image/*"
                            class="mt-3 block w-full text-sm"
                        >

                    </div>

                </div>

            </div>


            <div class="mt-8">

                <div class="flex items-center justify-between gap-4">

                    <div>
                        <div class="font-black text-slate-900">
                            Footer Links
                        </div>

                        <div class="text-xs text-slate-500">
                            Add, remove or reorder links by editing the rows.
                        </div>
                    </div>

                    <button
                        type="button"
                        id="addFooterLink"
                        class="rounded-xl bg-blue-50 px-4 py-2 text-sm font-black text-blue-600"
                    >
                        + Add Link
                    </button>

                </div>


                <div
                    id="footerLinkList"
                    class="mt-5 grid gap-3"
                >
                    @foreach(old('footer_links', $footerLinks) as $index => $link)

                        <div class="repeatable-footer-link grid gap-3 rounded-2xl bg-slate-50 p-4 md:grid-cols-[1fr_1.4fr_auto]">

                            <input
                                type="text"
                                name="footer_links[{{ $index }}][label]"
                                value="{{ $link['label'] ?? '' }}"
                                placeholder="Link label"
                                class="rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm"
                            >

                            <input
                                type="text"
                                name="footer_links[{{ $index }}][url]"
                                value="{{ $link['url'] ?? '' }}"
                                placeholder="/page"
                                class="rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm"
                            >

                            <button
                                type="button"
                                class="remove-repeatable rounded-xl px-4 py-2 text-xs font-black text-red-600"
                            >
                                Delete
                            </button>

                        </div>

                    @endforeach
                </div>

            </div>

        </section>


        <div class="sticky bottom-4 z-30 flex justify-end">

            <button
                type="submit"
                class="rounded-xl bg-blue-600 px-7 py-3.5 text-sm font-black text-white shadow-xl hover:bg-blue-700"
            >
                Save Theme Configuration
            </button>

        </div>

    </form>


    {{-- Templates for repeatable sections --}}

    <template id="featureTemplate">
        <div class="repeatable-feature rounded-2xl border border-slate-200 bg-slate-50 p-5">

            <div class="flex items-center justify-between">
                <div class="font-black text-slate-900">
                    Feature
                </div>

                <button
                    type="button"
                    class="remove-repeatable text-xs font-black text-red-600"
                >
                    Delete
                </button>
            </div>

            <div class="mt-4 grid gap-5 lg:grid-cols-[1fr_260px]">

                <div class="space-y-4">

                    <input
                        type="text"
                        data-field="title"
                        placeholder="Feature title"
                        class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm"
                    >

                    <textarea
                        data-field="text"
                        rows="4"
                        placeholder="Feature description"
                        class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm"
                    ></textarea>

                </div>

                <div>
                    <div class="text-xs font-bold text-slate-500">
                        Feature Image
                    </div>

                    <div class="mt-1 text-[11px] text-slate-400">
                        Recommended: 800 × 520 px.
                    </div>

                    <input
                        type="file"
                        data-field="image"
                        accept="image/*"
                        class="mt-3 block w-full text-xs"
                    >
                </div>

            </div>

        </div>
    </template>


    <template id="testimonialTemplate">
        <div class="repeatable-testimonial rounded-2xl border border-slate-200 bg-slate-50 p-5">

            <div class="flex justify-end">
                <button
                    type="button"
                    class="remove-repeatable text-xs font-black text-red-600"
                >
                    Delete
                </button>
            </div>

            <div class="mt-3 grid gap-4">

                <div class="grid gap-4 md:grid-cols-2">

                    <input
                        type="text"
                        data-field="name"
                        placeholder="Customer name"
                        class="rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm"
                    >

                    <input
                        type="text"
                        data-field="role"
                        placeholder="Role / company"
                        class="rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm"
                    >

                </div>

                <textarea
                    data-field="text"
                    rows="4"
                    placeholder="Testimonial"
                    class="rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm"
                ></textarea>

                <div>
                    <div class="text-xs font-bold text-slate-500">
                        Customer Photo
                    </div>

                    <div class="mt-1 text-[11px] text-slate-400">
                        Recommended: 160 × 160 px square.
                    </div>

                    <input
                        type="file"
                        data-field="photo"
                        accept="image/*"
                        class="mt-3 block w-full text-xs"
                    >
                </div>

            </div>

        </div>
    </template>


    <template id="footerLinkTemplate">
        <div class="repeatable-footer-link grid gap-3 rounded-2xl bg-slate-50 p-4 md:grid-cols-[1fr_1.4fr_auto]">

            <input
                type="text"
                data-field="label"
                placeholder="Link label"
                class="rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm"
            >

            <input
                type="text"
                data-field="url"
                placeholder="/page"
                class="rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm"
            >

            <button
                type="button"
                class="remove-repeatable rounded-xl px-4 py-2 text-xs font-black text-red-600"
            >
                Delete
            </button>

        </div>
    </template>


    <script>
        document.addEventListener(
            'DOMContentLoaded',
            function () {

                function attachDeleteButtons(root) {
                    root
                        .querySelectorAll(
                            '.remove-repeatable'
                        )
                        .forEach(function (button) {
                            button.onclick =
                                function () {
                                    button
                                        .closest(
                                            '.repeatable-feature, .repeatable-testimonial, .repeatable-footer-link'
                                        )
                                        ?.remove();
                                };
                        });
                }


                function addRepeatable(
                    listId,
                    templateId,
                    prefix
                ) {
                    const list =
                        document.getElementById(
                            listId
                        );

                    const template =
                        document.getElementById(
                            templateId
                        );

                    const index =
                        Date.now().toString()
                        + Math.floor(
                            Math.random() * 1000
                        );

                    const fragment =
                        template.content.cloneNode(
                            true
                        );

                    fragment
                        .querySelectorAll(
                            '[data-field]'
                        )
                        .forEach(function (field) {

                            const key =
                                field.dataset.field;

                            field.name =
                                prefix
                                + '['
                                + index
                                + ']['
                                + key
                                + ']';
                        });

                    list.appendChild(fragment);

                    attachDeleteButtons(list);
                }


                document
                    .getElementById('addFeature')
                    ?.addEventListener(
                        'click',
                        function () {
                            addRepeatable(
                                'featureList',
                                'featureTemplate',
                                'features'
                            );
                        }
                    );


                document
                    .getElementById(
                        'addTestimonial'
                    )
                    ?.addEventListener(
                        'click',
                        function () {
                            addRepeatable(
                                'testimonialList',
                                'testimonialTemplate',
                                'testimonials'
                            );
                        }
                    );


                document
                    .getElementById(
                        'addFooterLink'
                    )
                    ?.addEventListener(
                        'click',
                        function () {
                            addRepeatable(
                                'footerLinkList',
                                'footerLinkTemplate',
                                'footer_links'
                            );
                        }
                    );


                attachDeleteButtons(
                    document
                );

            }
        );
    </script>

</div>

@endsection
