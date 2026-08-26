@extends('tenant.admin.layouts.app')

@section('title', 'Esubiz AI')

@section('content')

<div class="mx-auto max-w-7xl space-y-8">

    {{-- ======================================================
         PAGE HEADER
    ======================================================= --}}
    <div
        class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between"
    >
        <div>

            <div
                class="text-xs font-black uppercase tracking-[.16em] text-blue-600"
            >
                Esubiz AI
            </div>

            <h1
                class="mt-2 text-3xl font-black text-slate-900"
            >
                AI Settings
            </h1>

            <p
                class="mt-2 max-w-3xl text-sm leading-6 text-slate-500"
            >
                Configure the AI services used across this website.
                Site AI, Live Chat, WhatsApp and future AI-enabled
                functions all connect to the central Esubiz AI engine.
            </p>

        </div>


        <a
            href="#ai-persona"
            class="inline-flex items-center justify-center rounded-xl border border-slate-200 bg-white px-5 py-3 text-sm font-black text-slate-700 shadow-sm"
        >
            Change AI Avatar
        </a>

    </div>


    
    <div
        data-ai-autosave-status
        hidden
        class="fixed bottom-6 right-6 z-[100] rounded-xl border border-slate-200 bg-white px-5 py-3 text-sm font-black shadow-xl"
    >
        Saved
    </div>


@if(session('success'))

        <div
            class="rounded-2xl border border-emerald-200 bg-emerald-50 px-5 py-4 text-sm font-bold text-emerald-700"
        >
            {{ session('success') }}
        </div>

    @endif


    {{-- ======================================================
         AI SUMMARY
    ======================================================= --}}
    <div
        class="grid gap-5 md:grid-cols-2 xl:grid-cols-4"
    >

        <div
            class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm"
        >
            <div
                class="text-xs font-black uppercase tracking-wide text-slate-400"
            >
                AI Credit Balance
            </div>

            <div
                class="mt-3 text-3xl font-black text-slate-900"
            >
                {{ $aiCreditBalance ?? '—' }}
            </div>

            <p
                class="mt-2 text-xs leading-5 text-slate-500"
            >
                Credits are managed centrally by Esubiz.
            </p>
        </div>


        <div
            class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm"
        >
            <div
                class="text-xs font-black uppercase tracking-wide text-slate-400"
            >
                Site AI
            </div>

            <div
                class="mt-3 text-xl font-black text-slate-900"
            >
                Enabled
            </div>

            <p
                class="mt-2 text-xs leading-5 text-slate-500"
            >
                Used by Theme Config, Page Builder and other website tools.
            </p>
        </div>


        <div
            class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm"
        >
            <div
                class="text-xs font-black uppercase tracking-wide text-slate-400"
            >
                Live Chat AI
            </div>

            <div
                class="mt-3 text-xl font-black text-slate-900"
            >
                {{ $liveChatAiEnabled ?? false ? 'Enabled' : 'Not configured' }}
            </div>

            <p
                class="mt-2 text-xs leading-5 text-slate-500"
            >
                Customer-facing chat persona can be configured separately.
            </p>
        </div>


        <div
            class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm"
        >
            <div
                class="text-xs font-black uppercase tracking-wide text-slate-400"
            >
                WhatsApp AI
            </div>

            <div
                class="mt-3 text-xl font-black text-slate-900"
            >
                {{ $whatsappAiEnabled ?? false ? 'Enabled' : 'Not configured' }}
            </div>

            <p
                class="mt-2 text-xs leading-5 text-slate-500"
            >
                Connects WhatsApp automation to the central AI engine.
            </p>
        </div>

    </div>


    {{-- ======================================================
         AI SERVICES
    ======================================================= --}}

    <section data-esubiz-ai-services>

        <div>

            <div
                class="text-xs font-black uppercase tracking-[.16em] text-blue-600"
            >
                AI Services
            </div>

            <h2
                class="mt-2 text-2xl font-black text-slate-900"
            >
                Website AI Controls
            </h2>

            <p
                class="mt-2 max-w-4xl text-sm leading-6 text-slate-500"
            >
                Enable the Esubiz AI services this website can use.
                Every service connects to the same central Esubiz AI
                engine and consumes AI credits when AI work is performed.
            </p>

        </div>


        <div
            class="mt-6 grid grid-cols-1 gap-5 xl:grid-cols-4"
            data-ai-service-grid
        >

            {{-- ==============================================
                 SITE AI
            =============================================== --}}
            <article
                class="flex min-h-[260px] flex-col rounded-3xl border border-slate-200 bg-white p-6 shadow-sm"
            >

                <div
                    class="flex items-start justify-between gap-4"
                >

                    <div
                        class="flex h-11 w-11 items-center justify-center rounded-2xl bg-blue-50 text-xl"
                    >
                        ✦
                    </div>

                    <label
                        class="inline-flex cursor-pointer items-center gap-2"
                    >
                        <span
                            class="text-xs font-black text-slate-500"
                        >
                            Enabled
                        </span>

                        <input
                            type="checkbox"
                            name="site_ai_enabled"
                            data-ai-service-toggle="site"
                            value="1"
                            form="esubizAiSettingsForm"
                            class="h-5 w-5"
                            {{
                                ($siteAiEnabled ?? true)
                                    ? 'checked'
                                    : ''
                            }}
                        >
                    </label>

                </div>


                <h3
                    class="mt-5 text-lg font-black text-slate-900"
                >
                    Site AI
                </h3>

                <p
                    class="mt-2 text-sm leading-6 text-slate-500"
                >
                    AI Assist for Theme Config, Page Builder,
                    content, products and other website functions
                    that register AI capabilities.
                </p>


                <div
                    class="mt-auto pt-5 text-xs font-bold text-blue-600"
                >
                    Central capability engine
                </div>

            </article>


            {{-- ==============================================
                 LIVE CHAT AI
            =============================================== --}}
            <article
                class="flex min-h-[260px] flex-col rounded-3xl border border-slate-200 bg-white p-6 shadow-sm"
            >

                <div
                    class="flex items-start justify-between gap-4"
                >

                    <div
                        class="flex h-11 w-11 items-center justify-center rounded-2xl bg-violet-50 text-xl"
                    >
                        ◉
                    </div>

                    <label
                        class="inline-flex cursor-pointer items-center gap-2"
                    >
                        <span
                            class="text-xs font-black text-slate-500"
                        >
                            Enabled
                        </span>

                        <input
                            type="checkbox"
                            name="live_chat_ai_enabled"
                            data-ai-service-toggle="live_chat"
                            value="1"
                            form="esubizAiSettingsForm"
                            class="h-5 w-5"
                            {{
                                ($liveChatAiEnabled ?? true)
                                    ? 'checked'
                                    : ''
                            }}
                        >
                    </label>

                </div>


                <h3
                    class="mt-5 text-lg font-black text-slate-900"
                >
                    Live Chat AI
                </h3>

                <p
                    class="mt-2 text-sm leading-6 text-slate-500"
                >
                    Customer conversations, support, lead
                    qualification and website enquiries.
                </p>


                <div
                    class="mt-auto pt-5"
                >
                    <span
                        class="text-xs font-bold text-violet-600"
                    >
                        Customer-facing avatar
                    </span>
                </div>

            </article>


            {{-- ==============================================
                 WHATSAPP AI
            =============================================== --}}
            <article
                class="flex min-h-[260px] flex-col rounded-3xl border border-slate-200 bg-white p-6 shadow-sm"
            >

                <div
                    class="flex items-start justify-between gap-4"
                >

                    <div
                        class="flex h-11 w-11 items-center justify-center rounded-2xl bg-emerald-50 text-xl"
                    >
                        ☏
                    </div>

                    <label
                        class="inline-flex cursor-pointer items-center gap-2"
                    >
                        <span
                            class="text-xs font-black text-slate-500"
                        >
                            Enabled
                        </span>

                        <input
                            type="checkbox"
                            name="whatsapp_ai_enabled"
                            data-ai-service-toggle="whatsapp"
                            value="1"
                            form="esubizAiSettingsForm"
                            class="h-5 w-5"
                            {{
                                ($whatsappAiEnabled ?? true)
                                    ? 'checked'
                                    : ''
                            }}
                        >
                    </label>

                </div>


                <h3
                    class="mt-5 text-lg font-black text-slate-900"
                >
                    WhatsApp AI
                </h3>

                <p
                    class="mt-2 text-sm leading-6 text-slate-500"
                >
                    AI-powered WhatsApp conversations, customer
                    support, enquiries and automated responses.
                </p>


                <div
                    class="mt-auto pt-5 text-xs font-bold text-emerald-600"
                >
                    Customer-facing avatar
                </div>

            </article>


            {{-- ==============================================
                 EMAIL AI
            =============================================== --}}
            <article
                class="flex min-h-[260px] flex-col rounded-3xl border border-slate-200 bg-white p-6 shadow-sm"
            >

                <div
                    class="flex items-start justify-between gap-4"
                >

                    <div
                        class="flex h-11 w-11 items-center justify-center rounded-2xl bg-sky-50 text-xl"
                    >
                        ✉
                    </div>

                    <label
                        class="inline-flex cursor-pointer items-center gap-2"
                    >
                        <span
                            class="text-xs font-black text-slate-500"
                        >
                            Enabled
                        </span>

                        <input
                            type="checkbox"
                            name="email_ai_enabled"
                            data-ai-service-toggle="email"
                            value="1"
                            form="esubizAiSettingsForm"
                            class="h-5 w-5"
                            {{
                                ($emailAiEnabled ?? true)
                                    ? 'checked'
                                    : ''
                            }}
                        >
                    </label>

                </div>


                <h3
                    class="mt-5 text-lg font-black text-slate-900"
                >
                    Email AI
                </h3>

                <p
                    class="mt-2 text-sm leading-6 text-slate-500"
                >
                    Draft emails, replies, campaigns, follow-ups,
                    subject lines and other email communication.
                </p>


                <div
                    class="mt-auto pt-5 text-xs font-bold text-sky-600"
                >
                    AI + Email service
                </div>

            </article>


            {{-- ==============================================
                 SMS AI
            =============================================== --}}
            <article
                class="flex min-h-[260px] flex-col rounded-3xl border border-slate-200 bg-white p-6 shadow-sm"
            >

                <div
                    class="flex items-start justify-between gap-4"
                >

                    <div
                        class="flex h-11 w-11 items-center justify-center rounded-2xl bg-amber-50 text-xl"
                    >
                        ▤
                    </div>

                    <label
                        class="inline-flex cursor-pointer items-center gap-2"
                    >
                        <span
                            class="text-xs font-black text-slate-500"
                        >
                            Enabled
                        </span>

                        <input
                            type="checkbox"
                            name="sms_ai_enabled"
                            data-ai-service-toggle="sms"
                            value="1"
                            form="esubizAiSettingsForm"
                            class="h-5 w-5"
                            {{
                                ($smsAiEnabled ?? true)
                                    ? 'checked'
                                    : ''
                            }}
                        >
                    </label>

                </div>


                <h3
                    class="mt-5 text-lg font-black text-slate-900"
                >
                    SMS AI
                </h3>

                <p
                    class="mt-2 text-sm leading-6 text-slate-500"
                >
                    Generate concise SMS campaigns, reminders,
                    notifications and personalized messages.
                </p>


                <div
                    class="mt-auto pt-5 text-xs font-bold text-amber-600"
                >
                    AI + SMS service
                </div>

            </article>


            {{-- ==============================================
                 SOCIAL MEDIA AI
            =============================================== --}}
            <article
                class="flex min-h-[260px] flex-col rounded-3xl border border-slate-200 bg-white p-6 shadow-sm"
            >

                <div
                    class="flex items-start justify-between gap-4"
                >

                    <div
                        class="flex h-11 w-11 items-center justify-center rounded-2xl bg-pink-50 text-xl"
                    >
                        ◎
                    </div>

                    <label
                        class="inline-flex cursor-pointer items-center gap-2"
                    >
                        <span
                            class="text-xs font-black text-slate-500"
                        >
                            Enabled
                        </span>

                        <input
                            type="checkbox"
                            name="social_media_ai_enabled"
                            data-ai-service-toggle="social_media"
                            value="1"
                            form="esubizAiSettingsForm"
                            class="h-5 w-5"
                            {{
                                ($socialMediaAiEnabled ?? true)
                                    ? 'checked'
                                    : ''
                            }}
                        >
                    </label>

                </div>


                <h3
                    class="mt-5 text-lg font-black text-slate-900"
                >
                    Social Media AI
                </h3>

                <p
                    class="mt-2 text-sm leading-6 text-slate-500"
                >
                    Generate posts, captions, images and content
                    plans for connected social profiles and
                    scheduled publishing.
                </p>


                <div
                    class="mt-auto pt-5 text-xs font-bold text-pink-600"
                >
                    Create • Schedule • Publish
                </div>

            </article>


            {{-- ==============================================
                 ADS AI
            =============================================== --}}
            <article
                class="flex min-h-[260px] flex-col rounded-3xl border border-slate-200 bg-white p-6 shadow-sm"
            >

                <div
                    class="flex items-start justify-between gap-4"
                >

                    <div
                        class="flex h-11 w-11 items-center justify-center rounded-2xl bg-orange-50 text-xl"
                    >
                        ◈
                    </div>

                    <label
                        class="inline-flex cursor-pointer items-center gap-2"
                    >
                        <span
                            class="text-xs font-black text-slate-500"
                        >
                            Enabled
                        </span>

                        <input
                            type="checkbox"
                            name="ads_ai_enabled"
                            data-ai-service-toggle="ads"
                            value="1"
                            form="esubizAiSettingsForm"
                            class="h-5 w-5"
                            {{
                                ($adsAiEnabled ?? true)
                                    ? 'checked'
                                    : ''
                            }}
                        >
                    </label>

                </div>


                <h3
                    class="mt-5 text-lg font-black text-slate-900"
                >
                    Ads AI
                </h3>

                <p
                    class="mt-2 text-sm leading-6 text-slate-500"
                >
                    Generate campaign concepts, ad copy,
                    headlines, creatives, CTAs and variations
                    for connected advertising platforms.
                </p>


                <div
                    class="mt-auto pt-5 text-xs font-bold text-orange-600"
                >
                    AI-assisted advertising
                </div>

            </article>

        </div>

    </section>


    {{-- ======================================================
         PERSONA
    ======================================================= --}}
    <div
        id="ai-persona"
        class="scroll-mt-6"
    >

        <div
            class="text-xs font-black uppercase tracking-[.16em] text-blue-600"
        >
            AI Avatar
        </div>

        <h2
            class="mt-2 text-2xl font-black text-slate-900"
        >
            Choose your Esubiz AI avatar
        </h2>

        <p
            class="mt-2 max-w-3xl text-sm leading-6 text-slate-500"
        >
            Choose the Esubiz AI avatar you want to interact with.
            All official Esubiz personas use the same central AI engine.
        </p>

    </div>


    <form
        id="esubizAiSettingsForm"
        method="POST"
        action="{{
            route(
                'tenant.cms.site-ai.settings.update',
                [
                    'subdomain' =>
                        $website->subdomain,
                ]
            )
        }}"
    >
        @csrf

        <div
            class="grid gap-5 md:grid-cols-2 xl:grid-cols-3"
        >

            @forelse(
                $personas
                as $persona
            )

                <label
                    class="cursor-pointer rounded-3xl border border-slate-200 bg-white p-5 shadow-sm"
                >

                    <div
                        class="flex items-start gap-4"
                    >

                        <div
                            class="h-16 w-16 shrink-0 overflow-hidden rounded-full bg-blue-50"
                        >

                            @if($persona->avatarUrl())

                                <img
                                    src="{{ $persona->avatarUrl() }}"
                                    alt="{{ $persona->name }}"
                                    class="h-full w-full object-cover"
                                >

                            @else

                                <div
                                    class="flex h-full w-full items-center justify-center text-sm font-black text-blue-600"
                                >
                                    AI
                                </div>

                            @endif

                        </div>


                        <div
                            class="min-w-0 flex-1"
                        >

                            <div
                                class="flex items-center justify-between gap-3"
                            >

                                <h3
                                    class="text-lg font-black text-slate-900"
                                >
                                    {{ $persona->name }}
                                </h3>

                                <input
                                    type="radio"
                                    name="persona_id"
                                    value="{{ $persona->id }}"
                                    {{
                                        (int) old(
                                            'persona_id',
                                            $selectedPersonaId
                                            ?? (
                                                $persona->is_default
                                                    ? $persona->id
                                                    : 0
                                            )
                                        )
                                        === (int) $persona->id
                                            ? 'checked'
                                            : ''
                                    }}
                                    class="h-5 w-5"
                                >

                            </div>


                            @if($persona->description)

                                <p
                                    class="mt-2 text-sm leading-6 text-slate-500"
                                >
                                    {{ $persona->description }}
                                </p>

                            @endif


                            @if($persona->is_default)

                                <span
                                    class="mt-3 inline-flex rounded-full bg-blue-50 px-3 py-1 text-[10px] font-black uppercase text-blue-600"
                                >
                                    Default
                                </span>

                            @endif

                        </div>

                    </div>

                </label>

            @empty

                <div
                    class="col-span-full rounded-3xl border border-dashed border-slate-300 bg-white p-8 text-center"
                >

                    <strong
                        class="text-slate-900"
                    >
                        No official AI avatars are available yet.
                    </strong>

                    <p
                        class="mt-2 text-sm text-slate-500"
                    >
                        Esubiz Admin can publish AI avatars such as
                        Yemi, Sonia and future assistant identities.
                    </p>

                </div>

            @endforelse

        </div>


        <div
            class="mt-6 flex justify-end"
        >
            
        </div>

    </form>

</div>



<script data-ai-service-autosave-installed>
document.addEventListener(
    'DOMContentLoaded',
    function () {

        const toggles =
            document.querySelectorAll(
                '[data-ai-service-toggle]'
            );

        const status =
            document.querySelector(
                '[data-ai-autosave-status]'
            );

        const endpoint =
            @json(
                route(
                    'tenant.cms.site-ai.service.update',
                    [
                        'subdomain' =>
                            $website->subdomain,
                    ]
                )
            );


        const csrf =
            document.querySelector(
                'meta[name="csrf-token"]'
            )?.getAttribute(
                'content'
            )
            || document.querySelector(
                'input[name="_token"]'
            )?.value
            || '';


        let statusTimer = null;


        function showStatus(
            text,
            type = 'saving'
        ) {

            if (!status) {
                return;
            }


            if (statusTimer) {
                clearTimeout(
                    statusTimer
                );
            }


            status.textContent =
                text;


            status.classList.remove(
                'text-slate-700',
                'text-blue-600',
                'text-emerald-600',
                'text-red-600',
                'border-blue-200',
                'border-emerald-200',
                'border-red-200'
            );


            if (type === 'saving') {

                status.classList.add(
                    'text-blue-600',
                    'border-blue-200'
                );

            } else if (
                type === 'saved'
            ) {

                status.classList.add(
                    'text-emerald-600',
                    'border-emerald-200'
                );

            } else {

                status.classList.add(
                    'text-red-600',
                    'border-red-200'
                );
            }


            status.hidden =
                false;
        }


        function hideStatus() {

            statusTimer =
                setTimeout(
                    function () {

                        if (status) {
                            status.hidden =
                                true;
                        }

                    },
                    1800
                );
        }


        toggles.forEach(
            function (toggle) {

                toggle.addEventListener(
                    'change',
                    async function () {

                        /*
                         * Because this runs AFTER the browser
                         * changed the checkbox, previous state
                         * is the opposite of current state.
                         */
                        const previousState =
                            !toggle.checked;

                        const service =
                            toggle.getAttribute(
                                'data-ai-service-toggle'
                            );


                        /*
                         * Prevent repeated clicks while saving.
                         */
                        toggle.disabled =
                            true;


                        showStatus(
                            'Saving...',
                            'saving'
                        );


                        try {

                            const response =
                                await fetch(
                                    endpoint,
                                    {
                                        method:
                                            'POST',

                                        credentials:
                                            'same-origin',

                                        headers: {
                                            'Content-Type':
                                                'application/json',

                                            'Accept':
                                                'application/json',

                                            'X-CSRF-TOKEN':
                                                csrf,

                                            'X-Requested-With':
                                                'XMLHttpRequest',
                                        },

                                        body:
                                            JSON.stringify({
                                                service:
                                                    service,

                                                enabled:
                                                    toggle.checked,
                                            }),
                                    }
                                );


                            let data = {};

                            try {
                                data =
                                    await response.json();
                            } catch (_) {
                                data = {};
                            }


                            if (
                                !response.ok
                                || !data.success
                            ) {

                                throw new Error(
                                    data.message
                                    || 'Could not save AI setting.'
                                );
                            }


                            /*
                             * Respect authoritative value
                             * returned by the server.
                             */
                            toggle.checked =
                                !!data.enabled;


                            showStatus(
                                'Saved',
                                'saved'
                            );


                            hideStatus();


                        } catch (error) {

                            /*
                             * Restore old state if AJAX failed.
                             */
                            toggle.checked =
                                previousState;


                            showStatus(
                                'Could not save',
                                'error'
                            );


                            hideStatus();


                        } finally {

                            toggle.disabled =
                                false;
                        }

                    }
                );
            }
        );

    }
);
</script>


@endsection
