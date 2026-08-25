@extends('tenant.themes.corporate-default.layout')

@section('title', ($website->name ?? 'Website') . ' | Home')

@section('content')

@php
    $features =
        json_decode(
            $theme['features_json'] ?? '[]',
            true
        ) ?: [];

    $testimonials =
        json_decode(
            $theme['testimonials_json'] ?? '[]',
            true
        ) ?: [];

    $assetUrl = static function (?string $path): ?string {
        if (empty($path)) {
            return null;
        }

        return request()->getSchemeAndHttpHost()
            . '/media/'
            . implode(
                '/',
                array_map(
                    'rawurlencode',
                    explode(
                        '/',
                        ltrim($path, '/')
                    )
                )
            );
    };
@endphp


@if(($theme['show_hero'] ?? '1') === '1')
{{-- HERO --}}
<section class="hero">

    <div class="container hero-grid">

        <div class="reveal">

            <span class="eyebrow">
                {{ $theme['hero_badge'] ?? 'Welcome' }}
            </span>

            <h1>
                {{ $theme['hero_title']
                    ?? 'Helping you move forward.' }}
            </h1>

            <p>
                {{ $theme['hero_subtitle'] ?? '' }}
            </p>

            <div class="hero-actions">

                <a
                    href="{{ $theme['cta_url'] ?? '/contact' }}"
                    class="btn btn-primary"
                >
                    {{ $theme['cta_label'] ?? 'Get Started' }}
                </a>

                <a
                    href="{{ $theme['secondary_cta_url'] ?? '/about' }}"
                    class="btn btn-secondary"
                >
                    {{ $theme['secondary_cta_label'] ?? 'Learn More' }}
                </a>

            </div>

        </div>


        <div class="hero-image reveal">

            @if(!empty($theme['hero_image_path']))

                <img
                    src="{{ $assetUrl($theme['hero_image_path']) }}"
                    alt="{{ $website->name ?? 'Business' }}"
                    loading="eager"
                >

            @else

                <svg
                    class="hero-svg"
                    viewBox="0 0 600 500"
                    xmlns="http://www.w3.org/2000/svg"
                >
                    <defs>
                        <linearGradient id="businessHero" x1="0" x2="1">
                            <stop
                                offset="0%"
                                stop-color="{{ $theme['secondary_color'] ?? '#0f172a' }}"
                            />
                            <stop
                                offset="100%"
                                stop-color="{{ $theme['primary_color'] ?? '#2563eb' }}"
                            />
                        </linearGradient>
                    </defs>

                    <rect width="600" height="500" fill="url(#businessHero)"/>

                    <circle
                        cx="480"
                        cy="90"
                        r="120"
                        fill="#fff"
                        opacity=".08"
                    />

                    <rect
                        x="70"
                        y="95"
                        width="460"
                        height="310"
                        rx="28"
                        fill="#fff"
                        opacity=".10"
                    />

                    <rect
                        x="105"
                        y="145"
                        width="250"
                        height="28"
                        rx="14"
                        fill="#fff"
                        opacity=".88"
                    />

                    <rect
                        x="105"
                        y="195"
                        width="345"
                        height="16"
                        rx="8"
                        fill="#fff"
                        opacity=".42"
                    />

                    <rect
                        x="105"
                        y="225"
                        width="285"
                        height="16"
                        rx="8"
                        fill="#fff"
                        opacity=".28"
                    />
                </svg>

            @endif

        </div>

    </div>

</section>


@endif
@if(($theme['show_features'] ?? '1') === '1')
{{-- FEATURES --}}
<section class="section">

    <div class="container">

        <div class="reveal">

            <span class="eyebrow">
                {{ $theme['features_badge'] ?? 'What we offer' }}
            </span>

            <h2 class="section-title">
                {{ $theme['features_title'] ?? '' }}
            </h2>

            <p class="section-copy">
                {{ $theme['features_subtitle'] ?? '' }}
            </p>

        </div>


        <div
            class="feature-grid"
            style="margin-top:42px;"
        >

            @foreach($features as $index => $feature)

                <article class="card reveal">

                    <div class="media">

                        @if(!empty($feature['image_path']))

                            <img
                                src="{{ $assetUrl($feature['image_path']) }}"
                                alt="{{ $feature['title'] ?? 'Feature' }}"
                                loading="lazy"
                            >

                        @else

                            <div class="feature-number">
                                {{ str_pad($index + 1, 2, '0', STR_PAD_LEFT) }}
                            </div>

                        @endif

                    </div>

                    <h3>
                        {{ $feature['title'] ?? '' }}
                    </h3>

                    <p class="section-copy">
                        {{ $feature['text'] ?? '' }}
                    </p>

                </article>

            @endforeach

        </div>

    </div>

</section>


@endif
{{-- STATISTICS --}}
@if(($theme['show_stats'] ?? '1') === '1')
<section class="section section-soft">

    <div class="container">

        <div class="reveal" style="text-align:center;">

            <span class="eyebrow">
                {{ $theme['stats_badge'] ?? 'Our approach' }}
            </span>

        </div>

        <div class="stats-grid reveal">

            @for($i = 1; $i <= 3; $i++)

                <div class="stat-card">

                    <strong>
                        {{ $theme['stat_' . $i . '_value'] ?? '' }}
                    </strong>

                    <span>
                        {{ $theme['stat_' . $i . '_label'] ?? '' }}
                    </span>

                </div>

            @endfor

        </div>

    </div>

</section>
@endif


@if(($theme['show_about'] ?? '1') === '1')
{{-- ABOUT --}}
<section class="section">

    <div class="container split">

        <div class="media reveal">

            @if(!empty($theme['about_image_path']))

                <img
                    src="{{ $assetUrl($theme['about_image_path']) }}"
                    alt="About {{ $website->name ?? 'us' }}"
                    loading="lazy"
                >

            @else

                <div class="about-art">
                    <span>
                        {{ strtoupper(
                            substr(
                                $website->name ?? 'B',
                                0,
                                1
                            )
                        ) }}
                    </span>
                </div>

            @endif

        </div>


        <div class="reveal">

            <span class="eyebrow">
                {{ $theme['about_badge'] ?? 'About us' }}
            </span>

            <h2 class="section-title">
                {{ $theme['about_title'] ?? '' }}
            </h2>

            <p class="section-copy">
                {{ $theme['about_text'] ?? '' }}
            </p>

            <div style="margin-top:28px;">

                <a
                    href="{{ $theme['about_cta_url'] ?? '/about' }}"
                    class="btn btn-primary"
                >
                    {{ $theme['about_cta_label'] ?? 'Our Story' }}
                </a>

            </div>

        </div>

    </div>

</section>


@endif
@if(($theme['show_testimonials'] ?? '1') === '1')
{{-- TESTIMONIAL SLIDER --}}
<section class="section section-soft">

    <div class="container">

        <div class="reveal">

            <span class="eyebrow">
                {{ $theme['testimonials_badge'] ?? 'Testimonials' }}
            </span>

            <h2 class="section-title">
                {{ $theme['testimonials_title'] ?? '' }}
            </h2>

            <p class="section-copy">
                {{ $theme['testimonials_subtitle'] ?? '' }}
            </p>

        </div>


        @if(count($testimonials))

            <div
                class="testimonial-slider reveal"
                id="businessTestimonialSlider"
            >

                <div
                    class="testimonial-track"
                    id="businessTestimonialTrack"
                >

                    @foreach($testimonials as $testimonial)

                        <article class="testimonial-slide">

                            <div class="testimonial-card">

                                @if(!empty($testimonial['photo_path']))

                                    <img
                                        src="{{ $assetUrl($testimonial['photo_path']) }}"
                                        class="testimonial-avatar"
                                        alt=""
                                        loading="lazy"
                                    >

                                @else

                                    <div class="testimonial-avatar testimonial-avatar-placeholder">
                                        {{ strtoupper(
                                            substr(
                                                $testimonial['name']
                                                ?? 'C',
                                                0,
                                                1
                                            )
                                        ) }}
                                    </div>

                                @endif


                                <div class="testimonial-quote">
                                    “
                                </div>

                                <p>
                                    {{ $testimonial['text'] ?? '' }}
                                </p>

                                <div class="testimonial-name">
                                    {{ $testimonial['name'] ?? '' }}
                                </div>

                                <div class="testimonial-role">
                                    {{ $testimonial['role'] ?? '' }}
                                </div>

                            </div>

                        </article>

                    @endforeach

                </div>


                <div class="testimonial-controls">

                    <button
                        type="button"
                        id="testimonialPrev"
                        aria-label="Previous testimonial"
                    >
                        ←
                    </button>

                    <div
                        class="testimonial-dots"
                        id="testimonialDots"
                    ></div>

                    <button
                        type="button"
                        id="testimonialNext"
                        aria-label="Next testimonial"
                    >
                        →
                    </button>

                </div>

            </div>

        @endif

    </div>

</section>


@endif
{{-- FINAL CTA --}}
@if(($theme['show_cta'] ?? '1') === '1')
<section class="section">

    <div class="container">

        <div class="final-cta reveal">

            <div>

                <span class="eyebrow">
                    {{ $theme['final_cta_badge'] ?? "Let's talk" }}
                </span>

                <h2>
                    {{ $theme['final_cta_title'] ?? '' }}
                </h2>

                <p>
                    {{ $theme['final_cta_text'] ?? '' }}
                </p>

            </div>

            <a
                href="{{ $theme['final_cta_url'] ?? '/contact' }}"
                class="btn btn-primary"
            >
                {{ $theme['final_cta_label'] ?? 'Contact Us' }}
            </a>

        </div>

    </div>

</section>
@endif


<script>
document.addEventListener(
    'DOMContentLoaded',
    function () {

        const track =
            document.getElementById(
                'businessTestimonialTrack'
            );

        if (!track) {
            return;
        }

        const slides =
            Array.from(
                track.querySelectorAll(
                    '.testimonial-slide'
                )
            );

        const dots =
            document.getElementById(
                'testimonialDots'
            );

        let index = 0;
        let timer = null;


        function visibleCount() {

            if (window.innerWidth < 720) {
                return 1;
            }

            if (window.innerWidth < 1050) {
                return 2;
            }

            return 3;
        }


        function maxIndex() {
            return Math.max(
                0,
                slides.length
                - visibleCount()
            );
        }


        function renderDots() {

            dots.innerHTML = '';

            for (
                let i = 0;
                i <= maxIndex();
                i++
            ) {
                const button =
                    document.createElement(
                        'button'
                    );

                button.type = 'button';

                button.className =
                    'testimonial-dot'
                    + (
                        i === index
                            ? ' active'
                            : ''
                    );

                button.onclick =
                    function () {
                        index = i;
                        update();
                        restart();
                    };

                dots.appendChild(button);
            }
        }


        function update() {

            if (index > maxIndex()) {
                index = maxIndex();
            }

            const width =
                slides[0]?.getBoundingClientRect()
                    .width || 0;

            const gap = 20;

            track.style.transform =
                'translateX(-'
                + (
                    index
                    * (
                        width
                        + gap
                    )
                )
                + 'px)';

            renderDots();
        }


        function next() {

            index =
                index >= maxIndex()
                    ? 0
                    : index + 1;

            update();
        }


        function previous() {

            index =
                index <= 0
                    ? maxIndex()
                    : index - 1;

            update();
        }


        function restart() {

            clearInterval(timer);

            timer =
                setInterval(
                    next,
                    5500
                );
        }


        document
            .getElementById(
                'testimonialNext'
            )
            ?.addEventListener(
                'click',
                function () {
                    next();
                    restart();
                }
            );


        document
            .getElementById(
                'testimonialPrev'
            )
            ?.addEventListener(
                'click',
                function () {
                    previous();
                    restart();
                }
            );


        window.addEventListener(
            'resize',
            update
        );

        update();
        restart();

    }
);
</script>

@endsection
