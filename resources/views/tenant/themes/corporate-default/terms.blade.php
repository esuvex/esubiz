@extends('tenant.themes.corporate-default.layout')

@section('title', 'Terms | ' . ($website->name ?? 'Website'))

@section('content')

<section class="page-hero">
    <div class="container reveal">

        <span class="eyebrow">
            Legal
        </span>

        <h1 class="section-title">
            Terms & Conditions
        </h1>

        <p class="section-copy">
            The general terms governing use of this website.
        </p>

    </div>
</section>


<section class="section">

    <div
        class="container card reveal"
        style="max-width:860px;"
    >

        <h2>Website Use</h2>

        <p class="section-copy">
            By using this website, you agree to use it
            lawfully and in a manner that does not interfere
            with the rights or experience of others.
        </p>

        <h2 style="margin-top:34px;">
            Information
        </h2>

        <p class="section-copy">
            We aim to keep information accurate and current,
            but content may be updated from time to time.
        </p>

        <h2 style="margin-top:34px;">
            Services
        </h2>

        <p class="section-copy">
            Specific services, quotations, agreements or
            transactions may be subject to additional terms.
        </p>

        <h2 style="margin-top:34px;">
            Contact
        </h2>

        <p class="section-copy">
            If you have questions about these terms,
            please contact us through our contact page.
        </p>

    </div>

</section>

@endsection
