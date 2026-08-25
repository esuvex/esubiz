@extends('tenant.themes.corporate-default.layout')

@section(
    'title',
    ($page->title ?? 'Page')
    . ' | '
    . ($website->name ?? 'Website')
)

@section('content')

@php
    $sections =
        is_array($builderContent ?? null)
            ? $builderContent
            : [];

    $themeAsset = function ($path) {
        return $path
            ? url(
                '/theme-assets/'
                . ltrim(
                    $path,
                    '/'
                )
            )
            : null;
    };
@endphp


<style>
    .builder-page {
        min-height: 50vh;
    }

    .builder-section {
        width: 100%;
    }

    .builder-section-inner {
        width: min(
            1160px,
            calc(100% - 40px)
        );

        margin-inline: auto;
    }

    .builder-columns {
        display: grid;
        align-items: start;
    }

    .builder-column {
        min-width: 0;
    }

    .builder-widget + .builder-widget {
        margin-top: 20px;
    }

    .builder-widget img {
        max-width: 100%;
        height: auto;
        border-radius: 18px;
    }

    .builder-heading {
        margin: 0;
        line-height: 1.08;
        letter-spacing: -.035em;
    }

    .builder-text {
        color: var(--muted);
        font-size: 1rem;
        line-height: 1.8;
        white-space: pre-line;
    }

    .builder-button {
        display: inline-flex;
        min-height: 48px;
        align-items: center;
        justify-content: center;
        padding: 0 20px;
        border-radius: 14px;
        background: var(--brand);
        color: #fff;
        font-weight: 850;
    }

    .builder-hero {
        padding: 38px;
        border-radius: 26px;
        background:
            linear-gradient(
                145deg,
                var(--ink),
                var(--brand)
            );
        color: #fff;
    }

    .builder-hero h1 {
        margin: 12px 0;
        font-size: clamp(
            2.2rem,
            6vw,
            4.6rem
        );
        line-height: .98;
        letter-spacing: -.055em;
    }

    .builder-hero p {
        max-width: 700px;
        color: #dbeafe;
    }

    .builder-card {
        padding: 26px;
        border: 1px solid var(--line);
        border-radius: 22px;
        background: #fff;
    }

    .builder-items {
        display: grid;
        grid-template-columns:
            repeat(
                auto-fit,
                minmax(220px,1fr)
            );
        gap: 20px;
    }

    .builder-divider {
        border: 0;
        border-top: 1px solid var(--line);
    }

    .builder-placeholder {
        padding: 32px;
        border: 1px dashed var(--line);
        border-radius: 18px;
        color: var(--muted);
        text-align: center;
        background: var(--soft);
    }

    @media(max-width:800px) {
        .builder-columns {
            grid-template-columns:
                1fr !important;
        }
    }
</style>


<div class="builder-page">

@if(count($sections))

    @foreach($sections as $section)

        @if(
            is_array($section)
            && ($section['type'] ?? null)
                === 'section'
        )

            @php
                $ratios =
                    $section['ratios']
                    ?? [1];

                $columns =
                    $section['columns']
                    ?? [];

                $sectionSettings =
                    $section['settings']
                    ?? [];

                $gridTemplate =
                    collect($ratios)
                        ->map(
                            fn ($ratio) =>
                                ((float) $ratio)
                                . 'fr'
                        )
                        ->implode(' ');

                $background =
                    $sectionSettings[
                        'background'
                    ]
                    ?? '#ffffff';

                $paddingTop =
                    (int) (
                        $sectionSettings[
                            'paddingTop'
                        ]
                        ?? 48
                    );

                $paddingBottom =
                    (int) (
                        $sectionSettings[
                            'paddingBottom'
                        ]
                        ?? 48
                    );

                $gap =
                    (int) (
                        $sectionSettings[
                            'gap'
                        ]
                        ?? 24
                    );
            @endphp


            <section
                class="builder-section"
                style="
                    background:
                        {{ $background }};
                    padding-top:
                        {{ $paddingTop }}px;
                    padding-bottom:
                        {{ $paddingBottom }}px;
                "
            >

                <div class="builder-section-inner">

                    <div
                        class="builder-columns"
                        style="
                            grid-template-columns:
                                {{ $gridTemplate }};
                            gap:
                                {{ $gap }}px;
                        "
                    >

                        @foreach($columns as $column)

                            <div class="builder-column">

                                @foreach(
                                    ($column['widgets'] ?? [])
                                    as $widget
                                )

                                    @php
                                        $type =
                                            $widget['type']
                                            ?? '';

                                        $data =
                                            $widget['data']
                                            ?? [];

                                        $settings =
                                            $widget['settings']
                                            ?? [];

                                        $align =
                                            $settings[
                                                'align'
                                            ]
                                            ?? 'left';

                                        $textColor =
                                            $settings[
                                                'textColor'
                                            ]
                                            ?? 'inherit';

                                        $widgetBackground =
                                            $settings[
                                                'background'
                                            ]
                                            ?? 'transparent';
                                    @endphp


                                    <div
                                        class="builder-widget"
                                        style="
                                            text-align:
                                                {{ $align }};
                                            color:
                                                {{ $textColor }};
                                            background:
                                                {{ $widgetBackground }};
                                        "
                                    >

                                        @switch($type)


                                            @case('hero')

                                                <div
                                                    class="builder-hero"
                                                >

                                                    @if(
                                                        !empty(
                                                            $data['eyebrow']
                                                        )
                                                    )
                                                        <div class="eyebrow">
                                                            {{ $data['eyebrow'] }}
                                                        </div>
                                                    @endif

                                                    <h1>
                                                        {{ $data['heading'] ?? '' }}
                                                    </h1>

                                                    <p>
                                                        {{ $data['text'] ?? '' }}
                                                    </p>

                                                    @if(
                                                        !empty(
                                                            $data['buttonText']
                                                        )
                                                    )
                                                        <div style="margin-top:24px;">
                                                            <a
                                                                href="{{ $data['buttonUrl'] ?? '#' }}"
                                                                class="btn btn-primary"
                                                            >
                                                                {{ $data['buttonText'] }}
                                                            </a>
                                                        </div>
                                                    @endif

                                                </div>

                                                @break


                                            @case('heading')

                                                @php
                                                    $level =
                                                        in_array(
                                                            $data['level']
                                                            ?? 'h2',
                                                            [
                                                                'h1',
                                                                'h2',
                                                                'h3',
                                                                'h4',
                                                            ],
                                                            true
                                                        )
                                                            ? $data['level']
                                                            : 'h2';
                                                @endphp

                                                <{{ $level }}
                                                    class="builder-heading"
                                                >
                                                    {{ $data['text'] ?? '' }}
                                                </{{ $level }}>

                                                @break


                                            @case('text')

                                                <div class="builder-text">
                                                    {{ $data['text'] ?? '' }}
                                                </div>

                                                @break


                                            @case('image')

                                                @if(!empty($data['src']))

                                                    <figure style="margin:0;">

                                                        <img
                                                            src="{{ $data['src'] }}"
                                                            alt="{{ $data['alt'] ?? '' }}"
                                                        >

                                                        @if(
                                                            !empty(
                                                                $data['caption']
                                                            )
                                                        )
                                                            <figcaption
                                                                style="
                                                                    margin-top:8px;
                                                                    color:var(--muted);
                                                                    font-size:.85rem;
                                                                "
                                                            >
                                                                {{ $data['caption'] }}
                                                            </figcaption>
                                                        @endif

                                                    </figure>

                                                @endif

                                                @break


                                            @case('button')

                                                <a
                                                    href="{{ $data['url'] ?? '#' }}"
                                                    class="builder-button"
                                                >
                                                    {{ $data['text'] ?? 'Learn More' }}
                                                </a>

                                                @break


                                            @case('cta')

                                                <div class="builder-card">

                                                    <h2 class="builder-heading">
                                                        {{ $data['heading'] ?? '' }}
                                                    </h2>

                                                    <p class="builder-text">
                                                        {{ $data['text'] ?? '' }}
                                                    </p>

                                                    @if(
                                                        !empty(
                                                            $data['buttonText']
                                                        )
                                                    )
                                                        <a
                                                            href="{{ $data['buttonUrl'] ?? '#' }}"
                                                            class="builder-button"
                                                            style="margin-top:18px;"
                                                        >
                                                            {{ $data['buttonText'] }}
                                                        </a>
                                                    @endif

                                                </div>

                                                @break


                                            @case('features')
                                            @case('cards')
                                            @case('testimonials')

                                                @if(
                                                    !empty(
                                                        $data['heading']
                                                    )
                                                )
                                                    <h2 class="builder-heading">
                                                        {{ $data['heading'] }}
                                                    </h2>
                                                @endif

                                                <div
                                                    class="builder-items"
                                                    style="margin-top:22px;"
                                                >

                                                    @foreach(
                                                        ($data['items'] ?? [])
                                                        as $item
                                                    )

                                                        <article class="builder-card">

                                                            @if(
                                                                !empty(
                                                                    $item['image']
                                                                )
                                                            )
                                                                <img
                                                                    src="{{ $item['image'] }}"
                                                                    alt=""
                                                                    style="margin-bottom:18px;"
                                                                >
                                                            @endif

                                                            @if(
                                                                !empty(
                                                                    $item['title']
                                                                )
                                                            )
                                                                <h3>
                                                                    {{ $item['title'] }}
                                                                </h3>
                                                            @endif

                                                            @if(
                                                                !empty(
                                                                    $item['name']
                                                                )
                                                            )
                                                                <h3>
                                                                    {{ $item['name'] }}
                                                                </h3>
                                                            @endif

                                                            <p class="builder-text">
                                                                {{ $item['text'] ?? '' }}
                                                            </p>

                                                        </article>

                                                    @endforeach

                                                </div>

                                                @break


                                            @case('faq')

                                                @if(
                                                    !empty(
                                                        $data['heading']
                                                    )
                                                )
                                                    <h2 class="builder-heading">
                                                        {{ $data['heading'] }}
                                                    </h2>
                                                @endif

                                                <div
                                                    class="faq-list"
                                                    style="margin-top:22px;"
                                                >

                                                    @foreach(
                                                        ($data['items'] ?? [])
                                                        as $item
                                                    )

                                                        <article class="faq-item">

                                                            <button
                                                                type="button"
                                                                class="faq-question"
                                                            >
                                                                <span>
                                                                    {{ $item['question'] ?? '' }}
                                                                </span>

                                                                <span>+</span>
                                                            </button>

                                                            <div class="faq-answer">
                                                                <p>
                                                                    {{ $item['answer'] ?? '' }}
                                                                </p>
                                                            </div>

                                                        </article>

                                                    @endforeach

                                                </div>

                                                @break


                                            @case('stats')

                                                <div class="builder-items">

                                                    @foreach(
                                                        ($data['items'] ?? [])
                                                        as $item
                                                    )

                                                        <div class="stat-card">

                                                            <strong>
                                                                {{ $item['value'] ?? '' }}
                                                            </strong>

                                                            <span>
                                                                {{ $item['label'] ?? '' }}
                                                            </span>

                                                        </div>

                                                    @endforeach

                                                </div>

                                                @break


                                            @case('divider')

                                                <hr class="builder-divider">

                                                @break


                                            @case('spacer')

                                                <div
                                                    style="
                                                        height:
                                                            {{ (int)($data['height'] ?? 48) }}px;
                                                    "
                                                ></div>

                                                @break


                                            @case('video')

                                                @if(!empty($data['url']))
                                                    <div class="builder-card">
                                                        <a
                                                            href="{{ $data['url'] }}"
                                                            target="_blank"
                                                            rel="noopener"
                                                        >
                                                            View Video
                                                        </a>
                                                    </div>
                                                @endif

                                                @break


                                            @case('map')

                                                <div class="builder-card">
                                                    {{ $data['embed'] ?? '' }}
                                                </div>

                                                @break


                                            @case('form')

                                                <div class="builder-placeholder">
                                                    Form
                                                    @if(!empty($data['formId']))
                                                        #{{ $data['formId'] }}
                                                    @endif
                                                </div>

                                                @break


                                            @case('panorama')

                                                <div class="builder-placeholder">

                                                    <strong>
                                                        360° Panorama
                                                    </strong>

                                                    @if(
                                                        !empty(
                                                            $data['heading']
                                                        )
                                                    )
                                                        <div style="margin-top:8px;">
                                                            {{ $data['heading'] }}
                                                        </div>
                                                    @endif

                                                </div>

                                                @break


                                            @case('html')

                                                <pre
                                                    style="
                                                        white-space:pre-wrap;
                                                        overflow-wrap:anywhere;
                                                    "
                                                >{{ $data['html'] ?? '' }}</pre>

                                                @break


                                            @default

                                                <div class="builder-placeholder">
                                                    {{ ucfirst($type ?: 'Widget') }}
                                                </div>

                                        @endswitch

                                    </div>

                                @endforeach

                            </div>

                        @endforeach

                    </div>

                </div>

            </section>

        @endif

    @endforeach


@elseif(
    trim(
        (string) (
            $page->content
            ?? ''
        )
    ) !== ''
)

    <section class="section">

        <div class="container">

            <div class="section-copy">
                {!! nl2br(
                    e(
                        $page->content
                    )
                ) !!}
            </div>

        </div>

    </section>


@else

    <section class="page-hero">

        <div class="container">

            <span class="eyebrow">
                Page
            </span>

            <h1 class="section-title">
                {{ $page->title ?? 'Page' }}
            </h1>

        </div>

    </section>

@endif

</div>

@endsection
