<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>
        @yield('title', $website->name ?? 'Website')
    </title>

    <meta
        name="description"
        content="@yield(
            'description',
            'Welcome to ' . ($website->name ?? 'our website')
        )"
    >


    @if(!empty($theme['favicon_path']))
        <link
            rel="icon"
            href="{{ url(
                '/theme-assets/'
                . ltrim(
                    $theme['favicon_path'],
                    '/'
                )
            ) }}"
        >
    @endif


    <style>
        :root {
            --brand:
                {{ $theme['primary_color'] ?? '#2563eb' }};

            --ink:
                {{ $theme['secondary_color'] ?? '#0f172a' }};

            --brand-soft:
                color-mix(
                    in srgb,
                    var(--brand) 9%,
                    white
                );

            --muted:#64748b;
            --line:#e2e8f0;
            --soft:#f8fafc;
            --radius:24px;

            --shadow:
                0 24px 70px
                rgba(15,23,42,.10);
        }

        * {
            box-sizing:border-box;
        }

        html {
            scroll-behavior:smooth;
        }

        body {
            margin:0;
            color:var(--ink);
            background:#fff;
            font-family:
                Inter,
                ui-sans-serif,
                system-ui,
                -apple-system,
                BlinkMacSystemFont,
                "Segoe UI",
                sans-serif;
            line-height:1.6;
        }

        body.menu-open {
            overflow:hidden;
        }

        a {
            color:inherit;
            text-decoration:none;
        }

        img {
            display:block;
            max-width:100%;
        }

        button,
        input,
        textarea {
            font:inherit;
        }

        .container {
            width:min(
                1160px,
                calc(100% - 40px)
            );
            margin-inline:auto;
        }

        .site-header {
            position:sticky;
            top:0;
            z-index:1000;
            border-bottom:
                1px solid
                rgba(226,232,240,.8);
            background:
                rgba(255,255,255,.94);
            backdrop-filter:
                blur(18px);
        }

        .nav {
            min-height:78px;
            display:flex;
            align-items:center;
            justify-content:space-between;
            gap:28px;
        }

        .brand {
            display:flex;
            align-items:center;
            gap:12px;
            font-weight:850;
            letter-spacing:-.03em;
            font-size:1.12rem;
        }

        .brand img {
            max-height:48px;
            max-width:180px;
            object-fit:contain;
        }

        .brand-mark {
            width:42px;
            height:42px;
            display:grid;
            place-items:center;
            border-radius:14px;
            color:#fff;
            background:
                linear-gradient(
                    135deg,
                    var(--brand),
                    #60a5fa
                );
            font-weight:900;
        }

        .desktop-nav {
            display:flex;
            align-items:center;
            gap:6px;
        }

        .desktop-nav a {
            padding:10px 13px;
            border-radius:12px;
            color:#334155;
            font-size:.91rem;
            font-weight:700;
        }

        .desktop-nav a:hover {
            color:var(--brand);
            background:var(--brand-soft);
        }

        .nav-cta {
            color:#fff !important;
            background:var(--brand) !important;
        }

        .mobile-toggle {
            display:none;
            width:44px;
            height:44px;
            border:1px solid var(--line);
            background:#fff;
            border-radius:13px;
            cursor:pointer;
        }

        .mobile-toggle span {
            width:18px;
            height:2px;
            display:block;
            margin:4px auto;
            border-radius:99px;
            background:var(--ink);
        }

        .mobile-menu {
            display:none;
            padding:8px 20px 24px;
            border-top:1px solid var(--line);
            background:#fff;
        }

        .mobile-menu.open {
            display:block;
        }

        .mobile-menu a {
            display:block;
            padding:14px;
            border-radius:12px;
            font-weight:750;
        }

        .section {
            padding:96px 0;
        }

        .section-soft {
            background:var(--soft);
        }

        .eyebrow {
            display:inline-flex;
            padding:7px 12px;
            border-radius:999px;
            color:var(--brand);
            background:var(--brand-soft);
            font-size:.76rem;
            font-weight:900;
            letter-spacing:.08em;
            text-transform:uppercase;
        }

        .section-title {
            margin:16px 0 14px;
            font-size:
                clamp(
                    2rem,
                    5vw,
                    3.4rem
                );
            line-height:1.06;
            letter-spacing:-.05em;
        }

        .section-copy {
            max-width:680px;
            margin:0;
            color:var(--muted);
            font-size:1.04rem;
        }

        .btn {
            min-height:50px;
            display:inline-flex;
            align-items:center;
            justify-content:center;
            padding:0 20px;
            border:0;
            border-radius:14px;
            cursor:pointer;
            font-weight:850;
            transition:.2s ease;
        }

        .btn:hover {
            transform:
                translateY(-2px);
        }

        .btn-primary {
            color:#fff;
            background:var(--brand);
            box-shadow:
                0 14px 30px
                rgba(37,99,235,.18);
        }

        .btn-secondary {
            border:
                1px solid
                var(--line);
            color:var(--ink);
            background:#fff;
        }

        .hero {
            overflow:hidden;
            padding:108px 0 90px;
            background:
                radial-gradient(
                    circle at 90% 10%,
                    rgba(96,165,250,.22),
                    transparent 32%
                ),
                linear-gradient(
                    180deg,
                    #fff,
                    #f8fbff
                );
        }

        .hero-grid {
            display:grid;
            grid-template-columns:
                1.05fr .95fr;
            gap:64px;
            align-items:center;
        }

        .hero h1 {
            margin:18px 0 20px;
            font-size:
                clamp(
                    2.7rem,
                    7vw,
                    5.3rem
                );
            line-height:.98;
            letter-spacing:-.065em;
        }

        .hero p {
            max-width:650px;
            margin:0;
            color:var(--muted);
            font-size:1.08rem;
        }

        .hero-actions {
            display:flex;
            flex-wrap:wrap;
            gap:12px;
            margin-top:30px;
        }

        .hero-image {
            min-height:470px;
            overflow:hidden;
            border-radius:34px;
            background:
                linear-gradient(
                    145deg,
                    var(--ink),
                    var(--brand)
                );
            box-shadow:var(--shadow);
            animation:
                floatCard
                6s
                ease-in-out
                infinite;
        }

        .hero-image img,
        .hero-svg {
            width:100%;
            height:470px;
            object-fit:cover;
        }

        .feature-grid {
            display:grid;
            grid-template-columns:
                repeat(
                    3,
                    minmax(0,1fr)
                );
            gap:24px;
        }

        .card {
            border:
                1px solid
                var(--line);
            border-radius:var(--radius);
            padding:28px;
            background:#fff;
            box-shadow:
                0 10px 36px
                rgba(15,23,42,.05);
        }

        .media {
            min-height:230px;
            overflow:hidden;
            margin-bottom:22px;
            border-radius:20px;
            background:
                linear-gradient(
                    135deg,
                    var(--brand-soft),
                    #e2e8f0
                );
        }

        .media img {
            width:100%;
            height:100%;
            min-height:230px;
            object-fit:cover;
        }

        .feature-number {
            min-height:230px;
            display:grid;
            place-items:center;
            color:var(--brand);
            font-size:4rem;
            font-weight:950;
        }

        .stats-grid {
            display:grid;
            grid-template-columns:
                repeat(3,1fr);
            gap:18px;
            margin-top:35px;
        }

        .stat-card {
            padding:30px;
            border:
                1px solid
                var(--line);
            border-radius:20px;
            text-align:center;
            background:#fff;
        }

        .stat-card strong {
            display:block;
            color:var(--brand);
            font-size:2.1rem;
        }

        .stat-card span {
            display:block;
            margin-top:6px;
            color:var(--muted);
            font-weight:750;
        }

        .split {
            display:grid;
            grid-template-columns:
                repeat(
                    2,
                    minmax(0,1fr)
                );
            gap:54px;
            align-items:center;
        }

        .about-art {
            min-height:420px;
            display:grid;
            place-items:center;
            color:#fff;
            background:
                linear-gradient(
                    145deg,
                    var(--ink),
                    var(--brand)
                );
        }

        .about-art span {
            font-size:8rem;
            font-weight:950;
            opacity:.86;
        }

        .testimonial-slider {
            overflow:hidden;
            margin-top:42px;
        }

        .testimonial-track {
            display:flex;
            gap:20px;
            transition:
                transform
                .5s
                cubic-bezier(
                    .22,
                    .61,
                    .36,
                    1
                );
        }

        .testimonial-slide {
            flex:
                0 0
                calc(
                    (100% - 40px)
                    / 3
                );
        }

        .testimonial-card {
            height:100%;
            position:relative;
            padding:30px;
            border:
                1px solid
                var(--line);
            border-radius:24px;
            background:#fff;
        }

        .testimonial-avatar {
            width:56px;
            height:56px;
            border-radius:50%;
            object-fit:cover;
        }

        .testimonial-avatar-placeholder {
            display:grid;
            place-items:center;
            color:#fff;
            background:var(--brand);
            font-weight:900;
        }

        .testimonial-quote {
            position:absolute;
            right:25px;
            top:12px;
            color:var(--brand-soft);
            font:900 6rem/1 Georgia,serif;
        }

        .testimonial-card p {
            position:relative;
            z-index:1;
            margin:22px 0;
            color:#475569;
        }

        .testimonial-name {
            font-weight:900;
        }

        .testimonial-role {
            color:var(--muted);
            font-size:.82rem;
        }

        .testimonial-controls {
            display:flex;
            align-items:center;
            justify-content:center;
            gap:16px;
            margin-top:28px;
        }

        .testimonial-controls > button {
            width:42px;
            height:42px;
            border:
                1px solid
                var(--line);
            border-radius:13px;
            cursor:pointer;
            color:var(--ink);
            background:#fff;
        }

        .testimonial-dots {
            display:flex;
            gap:7px;
        }

        .testimonial-dot {
            width:8px;
            height:8px;
            padding:0;
            border:0;
            border-radius:50%;
            cursor:pointer;
            background:#cbd5e1;
        }

        .testimonial-dot.active {
            width:24px;
            border-radius:99px;
            background:var(--brand);
        }

        .final-cta {
            display:flex;
            align-items:center;
            justify-content:space-between;
            gap:36px;
            padding:48px;
            border:
                1px solid
                var(--line);
            border-radius:30px;
            background:#fff;
            box-shadow:var(--shadow);
        }

        .final-cta h2 {
            margin:14px 0 8px;
            font-size:
                clamp(
                    2rem,
                    4vw,
                    3rem
                );
            letter-spacing:-.04em;
        }

        .final-cta p {
            margin:0;
            color:var(--muted);
        }

        .page-hero {
            padding:86px 0 64px;
            background:
                linear-gradient(
                    180deg,
                    #f8fbff,
                    #fff
                );
        }

        .page-hero h1 {
            margin:16px 0 14px;
            font-size:
                clamp(
                    2.5rem,
                    6vw,
                    4.5rem
                );
            line-height:1;
            letter-spacing:-.055em;
        }

        .faq-list {
            display:grid;
            gap:16px;
            max-width:900px;
            margin:0 auto;
        }

        .faq-item {
            overflow:hidden;
            border:
                1px solid
                var(--line);
            border-radius:20px;
            background:#fff;
        }

        .faq-question {
            width:100%;
            display:flex;
            justify-content:
                space-between;
            gap:20px;
            padding:22px 24px;
            border:0;
            cursor:pointer;
            text-align:left;
            color:var(--ink);
            background:transparent;
            font-weight:850;
        }

        .faq-answer {
            display:none;
            padding:0 24px 24px;
            color:var(--muted);
        }

        .faq-item.open
        .faq-answer {
            display:block;
        }

        .contact-grid {
            display:grid;
            grid-template-columns:
                .8fr 1.2fr;
            gap:48px;
        }

        .contact-form {
            padding:34px;
            border:
                1px solid
                var(--line);
            border-radius:26px;
            background:#fff;
            box-shadow:var(--shadow);
        }

        .form-grid {
            display:grid;
            grid-template-columns:
                repeat(2,1fr);
            gap:18px;
        }

        .form-group {
            display:grid;
            gap:8px;
        }

        .form-group.full {
            grid-column:1 / -1;
        }

        .form-control {
            width:100%;
            padding:13px 14px;
            border:
                1px solid
                #cbd5e1;
            border-radius:13px;
            outline:none;
        }

        textarea.form-control {
            min-height:150px;
        }

        .site-footer {
            position:relative;
            overflow:hidden;
            padding:70px 0 28px;
            color:#cbd5e1;
            background:
                var(--ink);
        }

        .footer-bg {
            position:absolute;
            inset:0;
            width:100%;
            height:100%;
            object-fit:cover;
            opacity:.12;
        }

        .footer-overlay {
            position:absolute;
            inset:0;
            background:
                linear-gradient(
                    90deg,
                    rgba(15,23,42,.97),
                    rgba(15,23,42,.86)
                );
        }

        .footer-inner {
            position:relative;
            z-index:2;
        }

        .footer-grid {
            display:grid;
            grid-template-columns:
                1.3fr .7fr;
            gap:50px;
        }

        .footer-logo {
            max-height:58px;
            max-width:190px;
            margin-bottom:18px;
            object-fit:contain;
        }

        .footer-title {
            color:#fff;
            font-size:1.35rem;
            font-weight:900;
        }

        .footer-links {
            display:grid;
            grid-template-columns:
                repeat(
                    2,
                    minmax(0,1fr)
                );
            gap:10px 20px;
        }

        .footer-links a:hover {
            color:#fff;
        }

        .footer-bottom {
            margin-top:50px;
            padding-top:22px;
            border-top:
                1px solid
                rgba(148,163,184,.18);
            display:flex;
            justify-content:space-between;
            gap:20px;
            font-size:.84rem;
        }

        .powered-link {
            color:#fff;
            font-weight:800;
        }

        .back-to-top {
            position:fixed;
            right:22px;
            bottom:22px;
            z-index:1200;
            width:48px;
            height:48px;
            display:grid;
            place-items:center;
            border:0;
            border-radius:16px;
            cursor:pointer;
            opacity:0;
            visibility:hidden;
            transform:
                translateY(14px);
            color:#fff;
            background:var(--brand);
            box-shadow:
                0 14px 34px
                rgba(15,23,42,.22);
            transition:.25s ease;
        }

        .back-to-top.visible {
            opacity:1;
            visibility:visible;
            transform:none;
        }

        .reveal {
            opacity:0;
            transform:
                translateY(24px);
            transition:
                opacity .65s ease,
                transform .65s ease;
        }

        .reveal.visible {
            opacity:1;
            transform:none;
        }

        @keyframes floatCard {
            0%,100% {
                transform:
                    translateY(0);
            }

            50% {
                transform:
                    translateY(-8px);
            }
        }

        @media(max-width:1050px) {

            .testimonial-slide {
                flex:
                    0 0
                    calc(
                        (100% - 20px)
                        / 2
                    );
            }

        }

        @media(max-width:900px) {

            .desktop-nav {
                display:none;
            }

            .mobile-toggle {
                display:block;
            }

            .hero-grid,
            .split,
            .contact-grid {
                grid-template-columns:1fr;
            }

            .feature-grid {
                grid-template-columns:
                    repeat(
                        2,
                        minmax(0,1fr)
                    );
            }

            .footer-grid {
                grid-template-columns:1fr;
            }

        }

        @media(max-width:720px) {

            .testimonial-slide {
                flex:0 0 100%;
            }

            .feature-grid {
                grid-template-columns:1fr;
            }

        }

        @media(max-width:640px) {

            .container {
                width:min(
                    100% - 28px,
                    1160px
                );
            }

            .section {
                padding:72px 0;
            }

            .hero {
                padding-top:72px;
            }

            .stats-grid {
                grid-template-columns:1fr;
            }

            .form-grid {
                grid-template-columns:1fr;
            }

            .form-group.full {
                grid-column:auto;
            }

            .final-cta {
                padding:30px;
                align-items:flex-start;
                flex-direction:column;
            }

            .footer-links {
                grid-template-columns:1fr;
            }

            .footer-bottom {
                flex-direction:column;
            }

            .back-to-top {
                right:14px;
                bottom:14px;
                width:44px;
                height:44px;
            }

        }

        @media(
            prefers-reduced-motion:
            reduce
        ) {

            *,
            *::before,
            *::after {
                animation:none !important;
                transition:none !important;
                scroll-behavior:auto !important;
            }

            .reveal {
                opacity:1;
                transform:none;
            }

        }
    </style>

    @stack('styles')

</head>


<body>

@php
    $assetUrl = function ($path) {
        return $path
            ? url(
                '/theme-assets/'
                . ltrim($path, '/')
            )
            : null;
    };

    $footerLinks =
        json_decode(
            $theme['footer_links_json']
                ?? '[]',
            true
        ) ?: [];
@endphp


<header class="site-header">

    <div class="container nav">

        <a
            href="/"
            class="brand"
        >

            @if(!empty($theme['logo_path']))

                <img
                    src="{{ $assetUrl($theme['logo_path']) }}"
                    alt="{{ $website->name ?? 'Website' }}"
                >

            @else

                <span class="brand-mark">
                    {{ strtoupper(
                        substr(
                            $website->name ?? 'B',
                            0,
                            1
                        )
                    ) }}
                </span>

                <span>
                    {{ $website->name ?? 'Business' }}
                </span>

            @endif

        </a>


        <nav class="desktop-nav">

            <a href="/">
                {{ $theme['nav_home_label'] ?? 'Home' }}
            </a>

            <a href="/about">
                {{ $theme['nav_about_label'] ?? 'About' }}
            </a>

            <a href="/faqs">
                {{ $theme['nav_faq_label'] ?? 'FAQs' }}
            </a>

            <a href="/contact">
                {{ $theme['nav_contact_label'] ?? 'Contact' }}
            </a>

            <a href="/login">
                {{ $theme['nav_login_label'] ?? 'Login' }}
            </a>

            <a
                href="/register"
                class="nav-cta"
            >
                {{ $theme['nav_register_label'] ?? 'Register' }}
            </a>

        </nav>


        <button
            type="button"
            class="mobile-toggle"
            id="corporateMobileToggle"
            aria-label="Open navigation"
        >
            <span></span>
            <span></span>
            <span></span>
        </button>

    </div>


    <div
        class="mobile-menu"
        id="corporateMobileMenu"
    >

        <a href="/">
            {{ $theme['nav_home_label'] ?? 'Home' }}
        </a>

        <a href="/about">
            {{ $theme['nav_about_label'] ?? 'About' }}
        </a>

        <a href="/faqs">
            {{ $theme['nav_faq_label'] ?? 'FAQs' }}
        </a>

        <a href="/contact">
            {{ $theme['nav_contact_label'] ?? 'Contact' }}
        </a>

        <a href="/login">
            {{ $theme['nav_login_label'] ?? 'Login' }}
        </a>

        <a href="/register">
            {{ $theme['nav_register_label'] ?? 'Register' }}
        </a>

    </div>

</header>


<main>
    @yield('content')
</main>


<footer class="site-footer">

    @if(!empty($theme['footer_background_path']))
        <img
            src="{{ $assetUrl(
                $theme['footer_background_path']
            ) }}"
            class="footer-bg"
            alt=""
        >
    @endif

    <div class="footer-overlay"></div>


    <div class="container footer-inner">

        <div class="footer-grid">

            <div>

                @if(!empty($theme['footer_logo_path']))

                    <img
                        src="{{ $assetUrl(
                            $theme['footer_logo_path']
                        ) }}"
                        class="footer-logo"
                        alt="{{ $website->name ?? 'Business' }}"
                    >

                @else

                    <div class="footer-title">
                        {{ $theme['footer_heading']
                            ?: ($website->name ?? 'Business') }}
                    </div>

                @endif


                <p style="max-width:520px;">
                    {{ $theme['footer_text'] ?? '' }}
                </p>

            </div>


            <div>

                <div
                    style="
                        color:#fff;
                        font-size:.8rem;
                        font-weight:900;
                        text-transform:uppercase;
                        letter-spacing:.08em;
                        margin-bottom:16px;
                    "
                >
                    Quick Links
                </div>

                <div class="footer-links">

                    @foreach($footerLinks as $link)

                        <a
                            href="{{ $link['url'] ?? '#' }}"
                        >
                            {{ $link['label'] ?? '' }}
                        </a>

                    @endforeach

                </div>

            </div>

        </div>


        <div class="footer-bottom">

            <span>
                © {{ date('Y') }}
                {{ $website->name ?? 'Business' }}.
                {{ $theme['footer_copyright']
                    ?? 'All rights reserved.' }}
            </span>

            <span>
                Powered by
                <a
                    href="https://esubiz.com"
                    target="_blank"
                    rel="noopener"
                    class="powered-link"
                >
                    Esubiz
                </a>
            </span>

        </div>

    </div>

</footer>


<button
    type="button"
    class="back-to-top"
    id="corporateBackToTop"
    aria-label="Back to top"
>
    ↑
</button>


<script>
(function () {

    const toggle =
        document.getElementById(
            'corporateMobileToggle'
        );

    const menu =
        document.getElementById(
            'corporateMobileMenu'
        );

    if (toggle && menu) {

        toggle.addEventListener(
            'click',
            function () {

                menu.classList.toggle(
                    'open'
                );

                document.body
                    .classList
                    .toggle(
                        'menu-open',
                        menu.classList
                            .contains('open')
                    );
            }
        );
    }


    const reveal =
        document.querySelectorAll(
            '.reveal'
        );

    if (
        'IntersectionObserver'
        in window
    ) {

        const observer =
            new IntersectionObserver(
                function (entries) {

                    entries.forEach(
                        function (entry) {

                            if (
                                entry.isIntersecting
                            ) {
                                entry.target
                                    .classList
                                    .add(
                                        'visible'
                                    );

                                observer
                                    .unobserve(
                                        entry.target
                                    );
                            }
                        }
                    );
                },
                {
                    threshold:.12
                }
            );

        reveal.forEach(
            function (item) {
                observer.observe(item);
            }
        );

    } else {

        reveal.forEach(
            function (item) {
                item.classList.add(
                    'visible'
                );
            }
        );
    }


    document
        .querySelectorAll(
            '.faq-question'
        )
        .forEach(
            function (button) {

                button.addEventListener(
                    'click',
                    function () {

                        button.closest(
                            '.faq-item'
                        )
                        ?.classList
                        .toggle(
                            'open'
                        );
                    }
                );
            }
        );


    const topButton =
        document.getElementById(
            'corporateBackToTop'
        );

    function updateTopButton() {

        if (!topButton) {
            return;
        }

        topButton.classList.toggle(
            'visible',
            window.scrollY > 320
        );
    }

    window.addEventListener(
        'scroll',
        updateTopButton,
        {
            passive:true
        }
    );

    topButton?.addEventListener(
        'click',
        function () {

            window.scrollTo({
                top:0,
                behavior:'smooth'
            });
        }
    );

    updateTopButton();

})();
</script>

@stack('scripts')

</body>
</html>
