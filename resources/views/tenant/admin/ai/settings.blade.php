{{-- ESUBIZ_INTERNAL_MEDIA_ON_DEMAND_V1 --}}
@extends('tenant.admin.layouts.app')

@section('title', 'Esubiz AI')

@section('content')

{{-- =========================================================
     ESUBIZ_AI_CREDIT_PURCHASE_PANEL

     SaaS AI Credits are centrally owned by Esubiz.

     Purchase:
     Tenant -> Central Marketplace checkout -> payment
     -> generic credit fulfilment -> website AI balance.

     Usage:
     Tenant AI -> CentralAiEngine -> exact debit.
     ========================================================= --}}

@php
    $aiCreditWebsite =
        \App\Models\Website::query()
            ->where(
                'subdomain',
                request()->route(
                    'subdomain'
                )
            )
            ->first();

    $aiCreditBalance =
        $aiCreditWebsite
            ? app(
                \App\Services\Ai\AiCreditService::class
            )->balance(
                $aiCreditWebsite
            )
            : 0;

    $aiCreditPackages =
        \Illuminate\Support\Facades\DB::table(
            'credit_packages'
        )
            ->where(
                'credit_type',
                'ai_credits'
            )
            ->where(
                'is_active',
                true
            )
            ->orderBy(
                'sort_order'
            )
            ->orderBy(
                'credit_quantity'
            )
            ->get();

    $aiReturnUrl =
        $aiCreditWebsite
            ? request()->fullUrl()
            : null;
@endphp


@if($aiCreditWebsite)

    <section
        style="
            margin-bottom:24px;
            padding:22px;
            background:#ffffff;
            border:1px solid #e5e7eb;
            border-radius:18px;
            box-shadow:0 8px 28px rgba(15,23,42,.05);
        "
    >

        <div
            style="
                display:flex;
                align-items:flex-start;
                justify-content:space-between;
                gap:20px;
                flex-wrap:wrap;
                margin-bottom:20px;
            "
        >

            <div>

                <div
                    style="
                        font-size:12px;
                        font-weight:800;
                        letter-spacing:.08em;
                        text-transform:uppercase;
                        color:#2563eb;
                        margin-bottom:6px;
                    "
                >
                    AI Credits
                </div>

                <h3
                    style="
                        margin:0;
                        color:#0f172a;
                        font-size:22px;
                        font-weight:800;
                    "
                >
                    {{ number_format(
                        (float) $aiCreditBalance,
                        0
                    ) }}
                    Credits
                </h3>

                <p
                    style="
                        margin:6px 0 0;
                        color:#64748b;
                        font-size:14px;
                    "
                >
                    Your website's Central Esubiz AI balance.
                    Credits are deducted automatically when AI is used.
                </p>

            </div>


            <div
                style="
                    padding:10px 14px;
                    background:#eff6ff;
                    border-radius:12px;
                    color:#1d4ed8;
                    font-size:13px;
                    font-weight:700;
                "
            >
                ₦10 = 1 AI Credit
            </div>

        </div>


        @if($aiCreditPackages->isEmpty())

            <div
                style="
                    padding:15px;
                    border-radius:12px;
                    background:#fff7ed;
                    color:#9a3412;
                    font-size:14px;
                "
            >
                No AI Credit packages are currently available.
            </div>

        @else

            <div
                style="
                    display:grid;
                    grid-template-columns:
                        repeat(
                            auto-fit,
                            minmax(190px,1fr)
                        );
                    gap:14px;
                "
            >

                @foreach($aiCreditPackages as $aiPackage)

                    <div
                        style="
                            padding:17px;
                            border:1px solid #e2e8f0;
                            border-radius:15px;
                            background:#f8fafc;
                        "
                    >

                        <strong
                            style="
                                display:block;
                                color:#0f172a;
                                font-size:15px;
                                margin-bottom:5px;
                            "
                        >
                            {{ $aiPackage->name }}
                        </strong>


                        <div
                            style="
                                color:#2563eb;
                                font-size:22px;
                                font-weight:800;
                                margin-bottom:3px;
                            "
                        >
                            {{ number_format(
                                (float)
                                $aiPackage->credit_quantity,
                                0
                            ) }}
                            Credits
                        </div>


                        <div
                            style="
                                color:#475569;
                                font-size:14px;
                                margin-bottom:14px;
                            "
                        >
                            ₦{{ number_format(
                                (float)
                                $aiPackage->price,
                                2
                            ) }}
                        </div>


                        @php
                            $aiCheckoutUrl =
                                app(
                                    \App\Services\Marketplace\SaasCheckoutLinkService::class
                                )->create(
                                    $aiCreditWebsite,
                                    'credit_package',
                                    (int) $aiPackage->id,
                                    $aiReturnUrl,
                                    'ai'
                                );
                        @endphp


                        <a
                            href="{{ $aiCheckoutUrl }}"
                            style="
                                display:block;
                                width:100%;
                                box-sizing:border-box;
                                border-radius:10px;
                                background:#2563eb;
                                color:#ffffff;
                                padding:11px 14px;
                                font:inherit;
                                font-size:13px;
                                font-weight:800;
                                text-align:center;
                                text-decoration:none;
                            "
                        >
                            Buy AI Credits
                        </a>

                    </div>

                @endforeach

            </div>

        @endif

    </section>

@endif



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
                            data-autosave-url="{{ route(
                                'tenant.cms.site-ai.autosave',
                                ['subdomain' => $website->subdomain]
                            ) }}"
                            data-autosave-field="site_ai_enabled"
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
                            data-autosave-url="{{ route(
                                'tenant.cms.site-ai.autosave',
                                ['subdomain' => $website->subdomain]
                            ) }}"
                            data-autosave-field="live_chat_ai_enabled"
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
                            data-autosave-url="{{ route(
                                'tenant.cms.site-ai.autosave',
                                ['subdomain' => $website->subdomain]
                            ) }}"
                            data-autosave-field="whatsapp_ai_enabled"
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
                            data-autosave-url="{{ route(
                                'tenant.cms.site-ai.autosave',
                                ['subdomain' => $website->subdomain]
                            ) }}"
                            data-autosave-field="email_ai_enabled"
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
                            data-autosave-url="{{ route(
                                'tenant.cms.site-ai.autosave',
                                ['subdomain' => $website->subdomain]
                            ) }}"
                            data-autosave-field="sms_ai_enabled"
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
                            data-autosave-url="{{ route(
                                'tenant.cms.site-ai.autosave',
                                ['subdomain' => $website->subdomain]
                            ) }}"
                            data-autosave-field="social_media_ai_enabled"
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
                            data-autosave-url="{{ route(
                                'tenant.cms.site-ai.autosave',
                                ['subdomain' => $website->subdomain]
                            ) }}"
                            data-autosave-field="ads_ai_enabled"
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
    decoding="async"
    loading="lazy"
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
                                    data-autosave-url="{{ route(
                                'tenant.cms.site-ai.autosave',
                                ['subdomain' => $website->subdomain]
                            ) }}"
                                    data-autosave-field="persona_id"
                                    value="{{ $persona->id }}"
                                    data-ai-avatar-choice
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












<!-- SITE_AI_CHAT_BRANDING_SETTINGS -->
    @php
        /*
         * ESUBIZ_TENANT_CHAT_APPEARANCE_PERMISSION_V2
         *
         * Central Admin is authoritative for whether
         * website admins may customize AI chat colors.
         */
        $allowSiteAiChatColorCustomization =
            (bool) (
                \Illuminate\Support\Facades\DB::table(
                    'central_ai_chat_settings'
                )
                    ->orderBy('id')
                    ->value(
                        'allow_user_chat_color_customization'
                    )
                ?? true
            );
    @endphp

    @if($allowSiteAiChatColorCustomization)

<div class="mt-8 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">

    <div class="mb-6">
        <h3 class="text-lg font-semibold text-slate-900">
            AI Chat Identity & Appearance
        </h3>

        <p class="mt-1 text-sm text-slate-500">
            Personalize how your AI assistant appears to website visitors.
        </p>
    </div>

    <form
        method="POST"
        action="{{ route('tenant.cms.site-ai.settings.update', ['subdomain' => $website->subdomain]) }}"
        class="space-y-6"
    >
        @csrf
        @method('PUT')

        <div class="grid grid-cols-1 gap-6 md:grid-cols-2">

            <div>
                <label class="mb-2 block text-sm font-medium text-slate-700">
                    AI Message Color
                </label>

                <input
                    type="color"
                    name="site_ai_color"
                    value="{{ old('site_ai_color', $website->site_ai_color ?? '#0b1f3a') }}"
                    class="h-12 w-20 cursor-pointer rounded-lg border border-slate-300 bg-white p-1"
                >
            </div>

            <div>
                <label class="mb-2 block text-sm font-medium text-slate-700">
                    AI Text Color
                </label>

                <input
                    type="color"
                    name="site_ai_text_color"
                    value="{{ old('site_ai_text_color', $website->site_ai_text_color ?? '#ffffff') }}"
                    class="h-12 w-20 cursor-pointer rounded-lg border border-slate-300 bg-white p-1"
                >
            </div>

            <div>
                <label class="mb-2 block text-sm font-medium text-slate-700">
                    Visitor Message Color
                </label>

                <input
                    type="color"
                    name="site_ai_user_color"
                    value="{{ old('site_ai_user_color', $website->site_ai_user_color ?? '#f1f5f9') }}"
                    class="h-12 w-20 cursor-pointer rounded-lg border border-slate-300 bg-white p-1"
                >
            </div>

            <div>
                <label class="mb-2 block text-sm font-medium text-slate-700">
                    Visitor Text Color
                </label>

                <input
                    type="color"
                    name="site_ai_user_text_color"
                    value="{{ old('site_ai_user_text_color', $website->site_ai_user_text_color ?? '#0f172a') }}"
                    class="h-12 w-20 cursor-pointer rounded-lg border border-slate-300 bg-white p-1"
                >
            </div>

        </div>

        <div class="flex justify-end">
            <button
                type="submit"
                class="rounded-xl bg-blue-600 px-5 py-3 text-sm font-semibold text-white hover:bg-blue-700"
            >
                Save Chat Appearance
            </button>
        </div>

    </form>

</div>

    @endif


@endsection
