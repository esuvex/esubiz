<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Esubiz - The Business Operating System</title>

    @vite(['resources/css/app.css','resources/js/app.js'])

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

    @include('frontend.partials.footer')

    --}}



</body>

</html>
