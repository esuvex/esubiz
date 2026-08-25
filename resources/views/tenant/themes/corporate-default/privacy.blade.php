@extends('tenant.themes.corporate-default.layout')

@section('title', 'Privacy | ' . ($website->name ?? 'Website'))

@section('content')

<section class="page-hero">

    <div class="container reveal">

        <span class="eyebrow">
            Privacy
        </span>

        <h1 class="section-title">
            Privacy Policy
        </h1>

        <p class="section-copy">
            How information submitted through this website
            may be handled.
        </p>

    </div>

</section>


<section class="section">

    <div
        class="container card reveal"
        style="max-width:860px;"
    >

        <h2>Information We Receive</h2>

        <p class="section-copy">
            Information may be provided when you contact us,
            submit forms or interact with services available
            through this website.
        </p>

        <h2 style="margin-top:34px;">
            How Information Is Used
        </h2>

        <p class="section-copy">
            Information may be used to respond to enquiries,
            provide requested services and improve customer
            experience.
        </p>

        <h2 style="margin-top:34px;">
            Protection
        </h2>

        <p class="section-copy">
            Reasonable measures should be taken to protect
            personal information from unauthorized access,
            misuse or disclosure.
        </p>

        <h2 style="margin-top:34px;">
            Contact
        </h2>

        <p class="section-copy">
            For privacy-related questions, please use the
            contact information provided on this website.
        </p>

    </div>

</section>

@endsection
