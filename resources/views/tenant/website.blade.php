<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    @php
        $seo = json_decode($page->seo ?? '{}', true) ?: [];

        $siteName =
            $settings['website_name']
            ?? $website->name;

        $pageTitle =
            $seo['title']
            ?? (
                $page->is_homepage
                    ? $siteName
                    : $page->title . ' | ' . $siteName
            );

        $description =
            $seo['description']
            ?? 'Welcome to ' . $siteName;

        $sections =
            $builderContent['sections']
            ?? [];
    @endphp

    <title>{{ $pageTitle }}</title>

    <meta
        name="description"
        content="{{ $description }}"
    >

    <style>
        * {
            box-sizing: border-box;
        }

        html {
            scroll-behavior: smooth;
        }

        body {
            margin: 0;
            font-family:
                Inter,
                ui-sans-serif,
                system-ui,
                -apple-system,
                BlinkMacSystemFont,
                "Segoe UI",
                sans-serif;
            background: #ffffff;
            color: #0f172a;
        }

        a {
            color: inherit;
        }

        .container {
            width: min(1180px, calc(100% - 40px));
            margin: 0 auto;
        }

        .site-header {
            position: sticky;
            top: 0;
            z-index: 50;
            border-bottom: 1px solid #e2e8f0;
            background: rgba(255,255,255,.94);
            backdrop-filter: blur(14px);
        }

        .nav {
            min-height: 76px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 24px;
        }

        .brand {
            display: flex;
            align-items: center;
            gap: 12px;
            text-decoration: none;
            font-size: 20px;
            font-weight: 900;
            letter-spacing: -.03em;
        }

        .brand-mark {
            width: 40px;
            height: 40px;
            display: grid;
            place-items: center;
            border-radius: 12px;
            background: #0f172a;
            color: #fff;
            font-weight: 900;
        }

        .menu {
            display: flex;
            align-items: center;
            gap: 28px;
            list-style: none;
            margin: 0;
            padding: 0;
        }

        .menu a {
            text-decoration: none;
            color: #475569;
            font-size: 14px;
            font-weight: 700;
        }

        .menu a:hover {
            color: #0f172a;
        }

        .hero {
            min-height: 72vh;
            display: flex;
            align-items: center;
            padding: 88px 0;
            background:
                radial-gradient(
                    circle at top right,
                    rgba(37,99,235,.10),
                    transparent 34%
                ),
                linear-gradient(
                    180deg,
                    #ffffff 0%,
                    #f8fafc 100%
                );
        }

        .hero-grid {
            display: grid;
            grid-template-columns:
                minmax(0, 1.1fr)
                minmax(320px, .9fr);
            gap: 56px;
            align-items: center;
        }

        .eyebrow {
            margin-bottom: 18px;
            color: #2563eb;
            font-size: 13px;
            font-weight: 900;
            letter-spacing: .16em;
            text-transform: uppercase;
        }

        .hero h1 {
            margin: 0;
            max-width: 760px;
            font-size: clamp(44px, 7vw, 78px);
            line-height: .98;
            letter-spacing: -.055em;
        }

        .hero-copy {
            margin-top: 26px;
            max-width: 680px;
            color: #64748b;
            font-size: 19px;
            line-height: 1.75;
        }

        .hero-actions {
            margin-top: 34px;
            display: flex;
            flex-wrap: wrap;
            gap: 14px;
        }

        .button {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 50px;
            padding: 0 22px;
            border-radius: 14px;
            text-decoration: none;
            font-size: 14px;
            font-weight: 900;
        }

        .button-primary {
            background: #2563eb;
            color: #ffffff;
        }

        .button-secondary {
            border: 1px solid #cbd5e1;
            background: #ffffff;
            color: #0f172a;
        }

        .hero-card {
            padding: 34px;
            border: 1px solid #e2e8f0;
            border-radius: 28px;
            background: #ffffff;
            box-shadow: 0 26px 80px rgba(15,23,42,.08);
        }

        .hero-card-label {
            color: #94a3b8;
            font-size: 12px;
            font-weight: 800;
            letter-spacing: .1em;
            text-transform: uppercase;
        }

        .hero-card-value {
            margin-top: 10px;
            font-size: 22px;
            font-weight: 900;
            word-break: break-word;
        }

        .hero-card-divider {
            height: 1px;
            margin: 26px 0;
            background: #e2e8f0;
        }

        .page-content {
            padding: 80px 0;
        }

        .page-content-inner {
            max-width: 860px;
            margin: 0 auto;
            font-size: 18px;
            line-height: 1.8;
            color: #334155;
        }

        .cms-section {
            padding: 80px 0;
        }

        .cms-section:nth-child(even) {
            background: #f8fafc;
        }

        .cms-section h2 {
            margin: 0 0 18px;
            font-size: clamp(32px, 5vw, 52px);
            letter-spacing: -.04em;
        }

        .cms-section p {
            margin: 0;
            max-width: 760px;
            color: #64748b;
            font-size: 18px;
            line-height: 1.75;
        }

        .site-footer {
            padding: 34px 0;
            border-top: 1px solid #e2e8f0;
            background: #0f172a;
            color: #cbd5e1;
        }

        .footer-inner {
            display: flex;
            justify-content: space-between;
            gap: 24px;
            align-items: center;
            flex-wrap: wrap;
        }

        .powered {
            color: #94a3b8;
            font-size: 13px;
        }

        @media (max-width: 820px) {
            .hero-grid {
                grid-template-columns: 1fr;
            }

            .menu {
                gap: 16px;
                flex-wrap: wrap;
                justify-content: flex-end;
            }

            .hero {
                padding: 64px 0;
            }
        }

        @media (max-width: 620px) {
            .nav {
                align-items: flex-start;
                flex-direction: column;
                padding: 18px 0;
            }

            .menu {
                justify-content: flex-start;
            }

            .hero h1 {
                font-size: 46px;
            }
        }
    </style>
</head>

<body>

<header class="site-header">
    <div class="container nav">

        <a
            href="{{ $websiteUrl }}"
            class="brand"
        >
            <span class="brand-mark">
                {{ strtoupper(substr($siteName, 0, 1)) }}
            </span>

            <span>
                {{ $siteName }}
            </span>
        </a>

        @if($menuItems->isNotEmpty())
            <nav aria-label="Main navigation">
                <ul class="menu">

                    @foreach($menuItems as $item)
                        @php
                            if ($item->type === 'page' && $item->page_id) {
                                $href =
                                    $item->url === '/'
                                        ? $websiteUrl
                                        : $websiteUrl . '/' . ltrim(
                                            $item->url ?: '',
                                            '/'
                                        );
                            } else {
                                $href =
                                    $item->url
                                        ? (
                                            str_starts_with(
                                                $item->url,
                                                'http'
                                            )
                                                ? $item->url
                                                : $websiteUrl . '/' . ltrim(
                                                    $item->url,
                                                    '/'
                                                )
                                        )
                                        : '#';
                            }
                        @endphp

                        <li>
                            <a href="{{ $href }}">
                                {{ $item->label }}
                            </a>
                        </li>
                    @endforeach

                </ul>
            </nav>
        @endif

    </div>
</header>


<main>

    @if(!empty($sections))

        @foreach($sections as $section)

            @php
                $type = $section['type'] ?? 'content';

                $sectionData =
                    $section['data']
                    ?? $section['settings']
                    ?? [];

                $heading =
                    $sectionData['heading']
                    ?? $sectionData['title']
                    ?? null;

                $text =
                    $sectionData['text']
                    ?? $sectionData['content']
                    ?? $sectionData['description']
                    ?? null;
            @endphp

            @if($type === 'hero')

                <section class="hero">
                    <div class="container hero-grid">

                        <div>
                            <div class="eyebrow">
                                {{ $sectionData['eyebrow'] ?? $siteName }}
                            </div>

                            <h1>
                                {{ $heading ?? $page->title }}
                            </h1>

                            @if($text)
                                <div class="hero-copy">
                                    {{ $text }}
                                </div>
                            @endif
                        </div>

                        <div class="hero-card">
                            <div class="hero-card-label">
                                Website
                            </div>

                            <div class="hero-card-value">
                                {{ $siteName }}
                            </div>

                            <div class="hero-card-divider"></div>

                            <div class="hero-card-label">
                                Address
                            </div>

                            <div class="hero-card-value">
                                {{ $websiteUrl }}
                            </div>
                        </div>

                    </div>
                </section>

            @else

                <section class="cms-section">
                    <div class="container">

                        @if($heading)
                            <h2>{{ $heading }}</h2>
                        @endif

                        @if($text)
                            <p>{{ $text }}</p>
                        @endif

                    </div>
                </section>

            @endif

        @endforeach

    @else

        <section class="hero">
            <div class="container hero-grid">

                <div>

                    <div class="eyebrow">
                        Welcome
                    </div>

                    <h1>
                        {{ $page->title ?: $siteName }}
                    </h1>

                    <div class="hero-copy">

                        @if(!empty(trim((string) $page->content)))
                            {!! nl2br(e($page->content)) !!}
                        @else
                            Welcome to {{ $siteName }}.
                            This website is now running on the Esubiz Core CMS.
                        @endif

                    </div>

                    <div class="hero-actions">

                        <a
                            href="#about"
                            class="button button-primary"
                        >
                            Explore
                        </a>

                        <a
                            href="{{ $websiteUrl }}"
                            class="button button-secondary"
                        >
                            Visit Website
                        </a>

                    </div>

                </div>

                <div class="hero-card">

                    <div class="hero-card-label">
                        Website
                    </div>

                    <div class="hero-card-value">
                        {{ $siteName }}
                    </div>

                    <div class="hero-card-divider"></div>

                    <div class="hero-card-label">
                        Website Address
                    </div>

                    <div class="hero-card-value">
                        {{ $websiteUrl }}
                    </div>

                    <div class="hero-card-divider"></div>

                    <div class="hero-card-label">
                        Core CMS
                    </div>

                    <div class="hero-card-value">
                        Active
                    </div>

                </div>

            </div>
        </section>


        <section
            id="about"
            class="cms-section"
        >
            <div class="container">

                <h2>
                    Built with Esubiz Core
                </h2>

                <p>
                    This is the first live Core CMS public renderer.
                    Pages, menus, media, forms, widgets and the page builder
                    will be managed from the website's own CMS.
                </p>

            </div>
        </section>

    @endif


    @if(
        !empty(trim((string) $page->content))
        && !empty($sections)
    )
        <section class="page-content">
            <div class="container">
                <div class="page-content-inner">
                    {!! nl2br(e($page->content)) !!}
                </div>
            </div>
        </section>
    @endif

</main>


<footer class="site-footer">
    <div class="container footer-inner">

        <strong>
            {{ $siteName }}
        </strong>

        <span class="powered">
            Powered by Esubiz
        </span>

    </div>
</footer>

</body>
</html>
