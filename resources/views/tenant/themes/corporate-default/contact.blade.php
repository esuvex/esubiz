@extends('tenant.themes.corporate-default.layout')

@section('title', 'Contact | ' . ($website->name ?? 'Website'))

@section('content')

<section class="page-hero">

    <div class="container reveal">

        <span class="eyebrow">
            Contact
        </span>

        <h1>
            Let's start a conversation.
        </h1>

        <p>
            Have a question, enquiry or project in mind?
            Send us a message and we will get back to you.
        </p>

    </div>

</section>


<section class="section">

    <div class="container contact-grid">

        <div class="reveal">

            <span class="eyebrow">
                Get in touch
            </span>

            <h2 class="section-title">
                We would love to hear from you.
            </h2>

            <p class="section-copy">
                Reach out using the contact form or any of
                the available contact details below.
            </p>


            <div class="contact-details">

                <div class="contact-detail">
                    <strong>Email</strong>

                    <span>
                        {{ $theme['contact_email']
                            ?? 'Contact us using the form' }}
                    </span>
                </div>

                <div class="contact-detail">
                    <strong>Business</strong>

                    <span>
                        {{ $website->name ?? 'Our Business' }}
                    </span>
                </div>

                <div class="contact-detail">
                    <strong>Website</strong>

                    <span>
                        {{ request()->getHost() }}
                    </span>
                </div>

            </div>

        </div>


        <div class="contact-form reveal">

            <h2 style="margin-top:0;">
                Send a message
            </h2>

            <p class="section-copy" style="margin-bottom:26px;">
                Complete the form below and we will receive
                your enquiry.
            </p>


            <form
                method="POST"
                action="#"
                onsubmit="event.preventDefault();"
            >
                @csrf

                <div class="form-grid">

                    <div class="form-group">
                        <label for="contact-name">
                            Name
                        </label>

                        <input
                            id="contact-name"
                            type="text"
                            name="name"
                            class="form-control"
                            placeholder="Your name"
                            required
                        >
                    </div>


                    <div class="form-group">
                        <label for="contact-email">
                            Email
                        </label>

                        <input
                            id="contact-email"
                            type="email"
                            name="email"
                            class="form-control"
                            placeholder="you@example.com"
                            required
                        >
                    </div>


                    <div class="form-group full">
                        <label for="contact-subject">
                            Subject
                        </label>

                        <input
                            id="contact-subject"
                            type="text"
                            name="subject"
                            class="form-control"
                            placeholder="How can we help?"
                            required
                        >
                    </div>


                    <div class="form-group full">
                        <label for="contact-message">
                            Message
                        </label>

                        <textarea
                            id="contact-message"
                            name="message"
                            class="form-control"
                            placeholder="Tell us about your enquiry..."
                            required
                        ></textarea>
                    </div>


                    <div class="form-group full">
                        <button
                            type="submit"
                            class="btn btn-primary"
                        >
                            Send Message
                        </button>
                    </div>

                </div>

            </form>

        </div>

    </div>

</section>

@endsection
