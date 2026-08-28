{{-- ESUBIZ_INTERNAL_MEDIA_ON_DEMAND_V1 --}}
{{--
|--------------------------------------------------------------------------
| Business Home Content Editor
|--------------------------------------------------------------------------
|
| Homepage CONTENT belongs to the Business Home CMS page.
|
| Header, navigation, favicon, global theme styling, footer and
| floating website tools remain in Theme Configuration.
|
--}}

        {{-- ==================================================
             HERO
        =================================================== --}}

        <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm sm:p-8">

            <div
                data-inline-toggle="show_hero"
                class="mb-6 flex flex-wrap items-center justify-between gap-4 border-b border-slate-200 pb-5"
            >

                <div>
                    <div class="text-sm font-black text-slate-900">
                        Hero
                    </div>

                    <div class="mt-1 text-xs font-bold text-slate-500">
                        Homepage visibility
                    </div>
                </div>

                <label class="theme-visibility-toggle">

                    <input
                        type="hidden"
                        name="show_hero"
                        value="0"
                    >

                    <input
                        type="checkbox"
                        name="show_hero"
                        value="1"
                        @checked(
                            old(
                                'show_hero',
                                $theme['show_hero'] ?? '1'
                            ) === '1'
                        )
                    >

                    <span class="toggle-half toggle-hide">
                        Hide
                    </span>

                    <span class="toggle-half toggle-show">
                        Show
                    </span>

                </label>

            </div>






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
                        <x-media.image
    src="{{ $assetUrl($theme['hero_image_path']) }}"
    alt="Current hero image"
    class="mt-4 aspect-[3/2] w-full rounded-2xl object-cover"
/>
                    @endif

                    <input
                        type="file"
                        name="hero_image"
                        accept="image/*"
                        class="mt-4 block w-full text-sm"
                    >

                        <input
                            type="hidden"
                            name="remove_hero_image"
                            value="0"
                            data-theme-remove-input="remove_hero_image"
                        >

                        @if(!empty($theme['hero_image_path']))
                            <button
                                type="button"
                                class="mt-3 rounded-xl border border-red-200 bg-red-50 px-4 py-2 text-xs font-black text-red-600"
                                data-theme-remove-photo="remove_hero_image"
                            >
                                Delete Photo
                            </button>
                        @endif


                </div>

            </div>

        </section>


        {{-- ==================================================
             FEATURES
        =================================================== --}}

        <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm sm:p-8">

            <div
                data-inline-toggle="show_features"
                class="mb-6 flex flex-wrap items-center justify-between gap-4 border-b border-slate-200 pb-5"
            >

                <div>
                    <div class="text-sm font-black text-slate-900">
                        Features
                    </div>

                    <div class="mt-1 text-xs font-bold text-slate-500">
                        Homepage visibility
                    </div>
                </div>

                <label class="theme-visibility-toggle">

                    <input
                        type="hidden"
                        name="show_features"
                        value="0"
                    >

                    <input
                        type="checkbox"
                        name="show_features"
                        value="1"
                        @checked(
                            old(
                                'show_features',
                                $theme['show_features'] ?? '1'
                            ) === '1'
                        )
                    >

                    <span class="toggle-half toggle-hide">
                        Hide
                    </span>

                    <span class="toggle-half toggle-show">
                        Show
                    </span>

                </label>

            </div>






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

                                {{-- ESUBIZ_BUSINESS_STRUCTURED_EDITOR_V1 --}}

                                <label class="flex items-center gap-3 text-sm font-bold text-slate-700">
                                    <input
                                        type="hidden"
                                        name="features[{{ $index }}][enabled]"
                                        value="0"
                                    >
                                    <input
                                        type="checkbox"
                                        name="features[{{ $index }}][enabled]"
                                        value="1"
                                        @checked(($feature['enabled'] ?? true) == true)
                                    >
                                    Show this card
                                </label>


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


                                <div class="grid gap-3 sm:grid-cols-2">

                                    <input
                                        type="text"
                                        {{-- ESUBIZ_BUSINESS_EXISTING_ICON_LIST_V4 --}}

                                    name="features[{{ $index }}][icon]"
                                    list="businessBasicIconList"
                                        value="{{ $feature['icon'] ?? '' }}"
                                        placeholder="Icon"
                                        class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm"
                                    >

                                    <label class="flex items-center gap-3 rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm font-bold text-slate-700">
                                        <input
                                            type="hidden"
                                            name="features[{{ $index }}][show_icon]"
                                            value="0"
                                        >
                                        <input
                                            type="checkbox"
                                            name="features[{{ $index }}][show_icon]"
                                            value="1"
                                            @checked(($feature['show_icon'] ?? false) == true)
                                        >
                                        Show icon
                                    </label>

                                </div>


                                <label class="flex items-center gap-3 rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm font-bold text-slate-700">

                                    <input
                                        type="hidden"
                                        name="features[{{ $index }}][show_image]"
                                        value="0"
                                    >

                                    <input
                                        type="checkbox"
                                        name="features[{{ $index }}][show_image]"
                                        value="1"
                                        @checked(
                                            array_key_exists(
                                                'show_image',
                                                $feature
                                            )
                                                ? (bool) $feature['show_image']
                                                : !empty($feature['image_path'])
                                        )
                                    >

                                    Show image

                                </label>


                                <label class="flex items-center gap-3 text-sm font-bold text-slate-700">

                                    <input
                                        type="hidden"
                                        name="features[{{ $index }}][show_button]"
                                        value="0"
                                    >

                                    <input
                                        type="checkbox"
                                        name="features[{{ $index }}][show_button]"
                                        value="1"
                                        @checked(($feature['show_button'] ?? false) == true)
                                    >

                                    Show button

                                </label>


                                <div class="grid gap-3 sm:grid-cols-2">

                                    <input
                                        type="text"
                                        name="features[{{ $index }}][button_label]"
                                        value="{{ $feature['button_label'] ?? '' }}"
                                        placeholder="Button label"
                                        class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm"
                                    >

                                    <input
                                        type="text"
                                        name="features[{{ $index }}][button_url]"
                                        value="{{ $feature['button_url'] ?? '' }}"
                                        placeholder="Button URL"
                                        class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm"
                                    >

                                </div>


                                <label class="flex items-center gap-3 text-sm font-bold text-slate-700">

                                    <input
                                        type="hidden"
                                        name="features[{{ $index }}][button_url_active]"
                                        value="0"
                                    >

                                    <input
                                        type="checkbox"
                                        name="features[{{ $index }}][button_url_active]"
                                        value="1"
                                        @checked(($feature['button_url_active'] ?? false) == true)
                                    >

                                    Activate button link

                                </label>


                            </div>


                            <div>

                                <div class="text-xs font-bold text-slate-500">
                                    Feature Image
                                </div>

                                <div class="mt-1 text-[11px] leading-5 text-slate-400">
                                    Recommended: 800 × 520 px.
                                </div>

                                @if(!empty($feature['image_path']))
                                    <x-media.image
    src="{{ $assetUrl($feature['image_path']) }}"
    alt=""
    class="mt-3 aspect-[800/520] w-full rounded-xl object-cover"
/>
                                @endif

                                <input
                                    type="hidden"
                                    name="features[{{ $index }}][existing_image]"
                                    value="{{ $feature['image_path'] ?? '' }}"
                                >

                                <input
                                    type="hidden"
                                    name="features[{{ $index }}][remove_image]"
                                    value="0"
                                    data-feature-remove-input
                                >

                                @if(!empty($feature['image_path']))
                                    <button
                                        type="button"
                                        class="mt-3 rounded-xl border border-red-200 bg-red-50 px-3 py-2 text-xs font-black text-red-600"
                                        data-repeatable-image-remove
                                    >
                                        Delete Photo
                                    </button>
                                @endif


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

            <div
                data-inline-toggle="show_stats"
                class="mb-6 flex flex-wrap items-center justify-between gap-4 border-b border-slate-200 pb-5"
            >

                <div>
                    <div class="text-sm font-black text-slate-900">
                        Statistics
                    </div>

                    <div class="mt-1 text-xs font-bold text-slate-500">
                        Homepage visibility
                    </div>
                </div>

                <label class="theme-visibility-toggle">

                    <input
                        type="hidden"
                        name="show_stats"
                        value="0"
                    >

                    <input
                        type="checkbox"
                        name="show_stats"
                        value="1"
                        @checked(
                            old(
                                'show_stats',
                                $theme['show_stats'] ?? '1'
                            ) === '1'
                        )
                    >

                    <span class="toggle-half toggle-hide">
                        Hide
                    </span>

                    <span class="toggle-half toggle-show">
                        Show
                    </span>

                </label>

            </div>





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

            <div
                data-inline-toggle="show_about"
                class="mb-6 flex flex-wrap items-center justify-between gap-4 border-b border-slate-200 pb-5"
            >

                <div>
                    <div class="text-sm font-black text-slate-900">
                        About Us
                    </div>

                    <div class="mt-1 text-xs font-bold text-slate-500">
                        Homepage visibility
                    </div>
                </div>

                <label class="theme-visibility-toggle">

                    <input
                        type="hidden"
                        name="show_about"
                        value="0"
                    >

                    <input
                        type="checkbox"
                        name="show_about"
                        value="1"
                        @checked(
                            old(
                                'show_about',
                                $theme['show_about'] ?? '1'
                            ) === '1'
                        )
                    >

                    <span class="toggle-half toggle-hide">
                        Hide
                    </span>

                    <span class="toggle-half toggle-show">
                        Show
                    </span>

                </label>

            </div>




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
                        <x-media.image
    src="{{ $assetUrl($theme['about_image_path']) }}"
    alt=""
    class="mt-4 aspect-[5/4] w-full rounded-2xl object-cover"
/>
                    @endif

                    <input
                        type="file"
                        name="about_image"
                        accept="image/*"
                        class="mt-4 block w-full text-sm"
                    >

                        <input
                            type="hidden"
                            name="remove_about_image"
                            value="0"
                            data-theme-remove-input="remove_about_image"
                        >

                        @if(!empty($theme['about_image_path']))
                            <button
                                type="button"
                                class="mt-3 rounded-xl border border-red-200 bg-red-50 px-4 py-2 text-xs font-black text-red-600"
                                data-theme-remove-photo="remove_about_image"
                            >
                                Delete Photo
                            </button>
                        @endif


                </div>

            </div>

        </section>


        {{-- ==================================================
             TESTIMONIAL SLIDER
        =================================================== --}}

        <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm sm:p-8">

            <div
                data-inline-toggle="show_testimonials"
                class="mb-6 flex flex-wrap items-center justify-between gap-4 border-b border-slate-200 pb-5"
            >

                <div>
                    <div class="text-sm font-black text-slate-900">
                        Testimonials
                    </div>

                    <div class="mt-1 text-xs font-bold text-slate-500">
                        Homepage visibility
                    </div>
                </div>

                <label class="theme-visibility-toggle">

                    <input
                        type="hidden"
                        name="show_testimonials"
                        value="0"
                    >

                    <input
                        type="checkbox"
                        name="show_testimonials"
                        value="1"
                        @checked(
                            old(
                                'show_testimonials',
                                $theme['show_testimonials'] ?? '1'
                            ) === '1'
                        )
                    >

                    <span class="toggle-half toggle-hide">
                        Hide
                    </span>

                    <span class="toggle-half toggle-show">
                        Show
                    </span>

                </label>

            </div>







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

                        <div class="mb-4 grid gap-3 sm:grid-cols-2">

                            <label class="flex items-center gap-3 text-sm font-bold text-slate-700">

                                <input
                                    type="hidden"
                                    name="testimonials[{{ $index }}][enabled]"
                                    value="0"
                                >

                                <input
                                    type="checkbox"
                                    name="testimonials[{{ $index }}][enabled]"
                                    value="1"
                                    @checked(($testimonial['enabled'] ?? true) == true)
                                >

                                Show testimonial

                            </label>


                            <label class="flex items-center gap-3 text-sm font-bold text-slate-700">

                                <input
                                    type="hidden"
                                    name="testimonials[{{ $index }}][show_image]"
                                    value="0"
                                >

                                <input
                                    type="checkbox"
                                    name="testimonials[{{ $index }}][show_image]"
                                    value="1"
                                    @checked(
                                        array_key_exists(
                                            'show_image',
                                            $testimonial
                                        )
                                            ? (bool) $testimonial['show_image']
                                            : !empty($testimonial['photo_path'])
                                    )
                                >

                                Show photo

                            </label>

                        </div>


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


                                <div class="grid gap-3 sm:grid-cols-2">

                                    <input
                                        type="text"
                                        name="testimonials[{{ $index }}][icon]"
                                    list="businessBasicIconList"
                                        value="{{ $testimonial['icon'] ?? '' }}"
                                        placeholder="Icon"
                                        class="rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm"
                                    >

                                    <label class="flex items-center gap-3 rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm font-bold text-slate-700">

                                        <input
                                            type="hidden"
                                            name="testimonials[{{ $index }}][show_icon]"
                                            value="0"
                                        >

                                        <input
                                            type="checkbox"
                                            name="testimonials[{{ $index }}][show_icon]"
                                            value="1"
                                            @checked(($testimonial['show_icon'] ?? false) == true)
                                        >

                                        Show icon

                                    </label>

                                </div>


                                <label class="flex items-center gap-3 text-sm font-bold text-slate-700">

                                    <input
                                        type="hidden"
                                        name="testimonials[{{ $index }}][show_button]"
                                        value="0"
                                    >

                                    <input
                                        type="checkbox"
                                        name="testimonials[{{ $index }}][show_button]"
                                        value="1"
                                        @checked(($testimonial['show_button'] ?? false) == true)
                                    >

                                    Show button

                                </label>


                                <div class="grid gap-3 sm:grid-cols-2">

                                    <input
                                        type="text"
                                        name="testimonials[{{ $index }}][button_label]"
                                        value="{{ $testimonial['button_label'] ?? '' }}"
                                        placeholder="Button label"
                                        class="rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm"
                                    >

                                    <input
                                        type="text"
                                        name="testimonials[{{ $index }}][button_url]"
                                        value="{{ $testimonial['button_url'] ?? '' }}"
                                        placeholder="Button URL"
                                        class="rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm"
                                    >

                                </div>


                                <label class="flex items-center gap-3 text-sm font-bold text-slate-700">

                                    <input
                                        type="hidden"
                                        name="testimonials[{{ $index }}][button_url_active]"
                                        value="0"
                                    >

                                    <input
                                        type="checkbox"
                                        name="testimonials[{{ $index }}][button_url_active]"
                                        value="1"
                                        @checked(($testimonial['button_url_active'] ?? false) == true)
                                    >

                                    Activate button link

                                </label>



                            <div>

                                <div class="text-xs font-bold text-slate-500">
                                    Customer Photo
                                </div>

                                <div class="mt-1 text-[11px] text-slate-400">
                                    Recommended: 160 × 160 px square.
                                </div>

                                @if(!empty($testimonial['photo_path']))
                                    <x-media.image
    src="{{ $assetUrl($testimonial['photo_path']) }}"
    alt=""
    class="mt-3 h-16 w-16 rounded-full object-cover"
/>
                                @endif

                                <input
                                    type="hidden"
                                    name="testimonials[{{ $index }}][existing_photo]"
                                    value="{{ $testimonial['photo_path'] ?? '' }}"
                                >

                                <input
                                    type="hidden"
                                    name="testimonials[{{ $index }}][remove_photo]"
                                    value="0"
                                    data-testimonial-remove-input
                                >

                                @if(!empty($testimonial['photo_path']))
                                    <button
                                        type="button"
                                        class="mt-3 rounded-xl border border-red-200 bg-red-50 px-3 py-2 text-xs font-black text-red-600"
                                        data-repeatable-photo-remove
                                    >
                                        Delete Photo
                                    </button>
                                @endif


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

            <div
                data-inline-toggle="show_cta"
                class="mb-6 flex flex-wrap items-center justify-between gap-4 border-b border-slate-200 pb-5"
            >

                <div>
                    <div class="text-sm font-black text-slate-900">
                        Call to Action
                    </div>

                    <div class="mt-1 text-xs font-bold text-slate-500">
                        Homepage visibility
                    </div>
                </div>

                <label class="theme-visibility-toggle">

                    <input
                        type="hidden"
                        name="show_cta"
                        value="0"
                    >

                    <input
                        type="checkbox"
                        name="show_cta"
                        value="1"
                        @checked(
                            old(
                                'show_cta',
                                $theme['show_cta'] ?? '1'
                            ) === '1'
                        )
                    >

                    <span class="toggle-half toggle-hide">
                        Hide
                    </span>

                    <span class="toggle-half toggle-show">
                        Show
                    </span>

                </label>

            </div>


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
