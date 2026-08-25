@extends('tenant.themes.corporate-default.layout')

@section('title', 'About | ' . ($website->name ?? 'Website'))

@section('content')

<section class="page-hero">

    <div class="container reveal">

        <span class="eyebrow">
            About us
        </span>

        <h1 class="section-title">
            People first.
            Purpose always.
        </h1>

        <p class="section-copy">
            Learn more about
            {{ $website->name ?? 'our company' }},
            what drives us and how we approach the work we do.
        </p>

    </div>

</section>


<section class="section">

    <div
        class="container"
        style="
            display:grid;
            grid-template-columns:repeat(
                auto-fit,
                minmax(300px,1fr)
            );
            gap:50px;
            align-items:start;
        "
    >

        <div class="reveal">

            <span class="eyebrow">
                Our story
            </span>

            <h2 class="section-title">
                Doing meaningful work,
                the right way.
            </h2>

        </div>

        <div class="reveal">

            <p class="section-copy">
                {{ $website->name ?? 'Our company' }}
                exists to create practical solutions and
                positive experiences for the people we serve.
            </p>

            <p class="section-copy" style="margin-top:20px;">
                We believe the strongest businesses grow
                through consistency, trust and a genuine
                commitment to delivering value.
            </p>

        </div>

    </div>

</section>


<section class="section section-soft">

    <div class="container">

        <div class="grid-3">

            <div class="card reveal">
                <div class="icon-box">V</div>
                <h3>Our Vision</h3>
                <p class="section-copy">
                    To be trusted for thoughtful solutions,
                    meaningful service and lasting value.
                </p>
            </div>

            <div class="card reveal">
                <div class="icon-box">M</div>
                <h3>Our Mission</h3>
                <p class="section-copy">
                    To serve people well through dependable
                    work, clear communication and continuous
                    improvement.
                </p>
            </div>

            <div class="card reveal">
                <div class="icon-box">C</div>
                <h3>Our Values</h3>
                <p class="section-copy">
                    Integrity, consistency, respect,
                    responsibility and customer focus.
                </p>
            </div>

        </div>

    </div>

</section>

@endsection
