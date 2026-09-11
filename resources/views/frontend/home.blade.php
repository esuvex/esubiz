<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    {{-- ESUBIZ_CENTRAL_GLOBAL_SEO_HOME_V13 --}}
    @php
        $esHomeSettingsV10 = app(
            \App\Services\Platform\CentralSiteSettingsService::class
        );

        $esHomeTitleV10 =
            $esHomeSettingsV10->get(
                'seo.site_title',
                'Esubiz — Build, Manage & Grow Your Business Online'
            );
    @endphp

    <title>{{ $esHomeTitleV10 }}</title>

    @include('partials.central-seo')

    @vite(['resources/css/app.css','resources/js/app.js'])


    {{-- ESUBIZ_CENTRAL_CANONICAL_FAVICON_V6 --}}
    <link
        rel="icon"
        href="{{ url('/media/branding/favicon.png') }}"
    >
    <link
        rel="shortcut icon"
        href="{{ url('/media/branding/favicon.png') }}"
    >
</head>


<body class="bg-white text-slate-900 antialiased">


    {{-- Navigation --}}

    @include('frontend.partials.navbar')



    {{-- Hero Section --}}

    @include('frontend.partials.hero')



   {{-- Trusted By Businesses --}}

   @include('frontend.partials.trusted')



    {{-- Products Section --}}

    @include('frontend.partials.products')



    {{-- Dashboard Showcase --}}

    @include('frontend.partials.dashboard-showcase')



    {{-- Why Esubiz --}}

    @include('frontend.partials.why-esubiz')



    {{-- Everything Connected --}}

    @include('frontend.partials.connected')



    {{-- AI Assistant --}}

    @include('frontend.partials.ai')



    {{-- Testimonials --}}

    @include('frontend.partials.testimonials')



    {{-- Call To Action --}}

    @include('frontend.partials.cta')



    {{-- Footer --}}

    @include('frontend.partials.footer')
    
    {{-- Future Sections --}}

    {{--

    @include('frontend.partials.dashboard-preview')

    @include('frontend.partials.showcase')

    @include('frontend.partials.features')

    @include('frontend.partials.pricing')

    @include('frontend.partials.faq')

    @include('frontend.partials.cta')



    --}}



</body>

</html>
