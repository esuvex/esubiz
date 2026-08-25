@extends('tenant.themes.corporate-default.layout')

@section('title', 'FAQs | ' . ($website->name ?? 'Website'))

@section('content')

<section class="page-hero">
    <div class="container reveal">

        <span class="eyebrow">
            FAQs
        </span>

        <h1>
            Frequently asked questions.
        </h1>

        <p>
            Helpful answers to common questions about
            {{ $website->name ?? 'our business' }}.
        </p>

    </div>
</section>


<section class="section">

    <div class="container">

        <div class="faq-list">

            @foreach([
                [
                    'What services do you provide?',
                    'We provide solutions tailored to the needs of our customers. Contact us to discuss your specific requirements.'
                ],
                [
                    'How can I get started?',
                    'Use our contact page to send us a message. We will review your request and guide you through the next steps.'
                ],
                [
                    'How long does it take to receive a response?',
                    'Response times may vary, but we aim to respond to enquiries as quickly as possible during our normal business hours.'
                ],
                [
                    'Can I request a custom solution?',
                    'Yes. We understand that every customer can have different requirements, so custom requests can be discussed with our team.'
                ],
                [
                    'How can I learn more about your business?',
                    'Visit our About page or contact us directly for more information.'
                ]
            ] as $index => $item)

                <article class="faq-item reveal">

                    <button
                        type="button"
                        class="faq-question"
                        aria-expanded="false"
                    >
                        <span>
                            {{ $item[0] }}
                        </span>

                        <span class="faq-icon">
                            +
                        </span>
                    </button>

                    <div class="faq-answer">
                        <p>
                            {{ $item[1] }}
                        </p>
                    </div>

                </article>

            @endforeach

        </div>

    </div>

</section>

@endsection
