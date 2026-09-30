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

    $aiReturnUrl = $aiCreditWebsite
        ? request()->fullUrl()
        : null;
@endphp


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

    <div class="flex flex-wrap gap-2 border-b border-blue-100 pb-4" role="tablist" aria-label="AI settings">
        @foreach(['credits' => 'Credits', 'services' => 'AI Services', 'avatar' => 'AI Avatar'] as $key => $label)
            <button type="button" role="tab" data-ai-settings-tab="{{ $key }}"
                aria-selected="{{ $key === 'credits' ? 'true' : 'false' }}"
                class="rounded-xl bg-blue-600 px-5 py-3 text-sm font-bold text-white hover:bg-blue-700">
                {{ $label }}
            </button>
        @endforeach
        @if($allowSiteAiChatColorCustomization)
            <button type="button" role="tab" data-ai-settings-tab="appearance"
                aria-selected="false"
                class="rounded-xl bg-blue-600 px-5 py-3 text-sm font-bold text-white hover:bg-blue-700">
                Appearance
            </button>
        @endif
    </div>

    <section data-ai-settings-panel="credits" role="tabpanel">

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
                    Your available AI Credits for this website.
                    Credits are deducted automatically when AI is used.
                </p>

                @include('tenant.admin.partials.credit-history', ['creditType' => 'ai_credits'])

            </div>


            <form
                method="POST"
                action="{{ route('tenant.cms.credits.checkout', ['subdomain' => request()->route('subdomain')]) }}"
                class="w-full max-w-sm space-y-3"
                data-ai-credit-form
                data-quote-url="{{ route('tenant.cms.credits.quote', ['subdomain' => request()->route('subdomain')]) }}"
            >
                @csrf
                <input type="hidden" name="credit_type" value="ai_credits">
                <label class="block text-sm font-bold text-slate-700">
                    Number of AI Credits
                    <input
                        type="number"
                        name="quantity"
                        min="1"
                        max="100000000"
                        step="1"
                        value="{{ old('quantity') }}"
                        required
                        class="mt-2 w-full rounded-xl border border-slate-300 bg-white px-4 py-3"
                    >
                </label>
                @include('tenant.admin.partials.credit-volume-tiers', ['creditType' => 'ai_credits'])
                <p class="text-sm text-slate-600" data-ai-credit-quote aria-live="polite">
                    Enter a quantity to see the current price.
                </p>
                @if($errors->any())
                    <div class="rounded-xl border border-red-200 bg-red-50 p-3 text-sm text-red-700">
                        @foreach($errors->all() as $message)
                            <p>{{ $message }}</p>
                        @endforeach
                    </div>
                @endif
                <button
                    type="submit"
                    disabled
                    data-ai-credit-submit
                    class="rounded-xl bg-blue-600 px-5 py-3 text-sm font-bold text-white hover:bg-blue-700 disabled:opacity-40"
                >
                    Continue to Checkout
                </button>
            </form>
        </div>
        <p class="mt-4 text-sm text-slate-600">
            Enter your quantity to see the current volume price before checkout.
        </p>
    </section>

@endif

<script>
document.addEventListener('DOMContentLoaded', () => {
    const form = document.querySelector('[data-ai-credit-form]');
    if (!form) return;
    const input = form.querySelector('[name="quantity"]');
    const label = form.querySelector('[data-ai-credit-quote]');
    const submit = form.querySelector('[data-ai-credit-submit]');
    let sequence = 0;
    let timer;

    async function quote() {
        const current = ++sequence;
        submit.disabled = true;
        const quantity = Number(input.value);
        if (!Number.isSafeInteger(quantity) || quantity < 1 || quantity > 100000000) {
            label.textContent = 'Enter a valid quantity to see the current price.';
            return;
        }
        label.textContent = 'Calculating price…';
        try {
            const url = new URL(form.dataset.quoteUrl);
            url.searchParams.set('credit_type', 'ai_credits');
            url.searchParams.set('quantity', String(quantity));
            const response = await fetch(url, {headers: {'Accept': 'application/json'}});
            const result = await response.json();
            if (current !== sequence) return;
            if (!response.ok) throw new Error(result.message || 'Price unavailable.');
            const amount = new Intl.NumberFormat(undefined, {
                minimumFractionDigits: 2, maximumFractionDigits: 2
            }).format(Number(result.total));
            label.textContent = result.currency + ' ' + amount + ' total';
            submit.disabled = false;
        } catch (error) {
            if (current === sequence) label.textContent = error.message;
        }
    }
    form.addEventListener('submit', event => {
        const quantity = Number(input.value);
        if (!Number.isSafeInteger(quantity) || quantity < 1 || quantity > 100000000) {
            event.preventDefault();
            label.textContent = 'Enter a whole number of AI Credits.';
            submit.disabled = true;
            return;
        }
        input.value = String(quantity);
    });
    input.addEventListener('input', () => {
        ++sequence;
        submit.disabled = true;
        clearTimeout(timer);
        timer = setTimeout(quote, 300);
    });
    quote();
});
</script>

        @include('tenant.admin.ai.partials.credit-usage-tables')

    </section>

    <section data-ai-settings-panel="services" role="tabpanel" hidden style="display:none">
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


    </section>

    <section data-ai-settings-panel="avatar" role="tabpanel" hidden style="display:none">
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

    </section>

</div>












<section class="mx-auto max-w-7xl" data-ai-settings-panel="appearance" role="tabpanel" hidden style="display:none">
<!-- SITE_AI_CHAT_BRANDING_SETTINGS -->
    

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


</section>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const tabs = [...document.querySelectorAll('[data-ai-settings-tab]')];
    const panels = [...document.querySelectorAll('[data-ai-settings-panel]')];
    const select = key => {
        if (!tabs.some(tab => tab.dataset.aiSettingsTab === key)) key = 'credits';
        tabs.forEach(tab => {
            const active = tab.dataset.aiSettingsTab === key;
            tab.setAttribute('aria-selected', active ? 'true' : 'false');
            tab.classList.toggle('bg-blue-700', active);
            tab.classList.toggle('bg-blue-600', !active);
        });
        panels.forEach(panel => {
            panel.hidden = panel.dataset.aiSettingsPanel !== key;
            panel.style.display = panel.hidden ? 'none' : '';
        });
    };
    tabs.forEach(tab => tab.addEventListener('click', () => {
        const key = tab.dataset.aiSettingsTab;
        select(key);
        history.replaceState(null, '', '#ai-' + key);
    }));
    document.querySelector('a[href="#ai-persona"]')?.addEventListener('click', event => {
        event.preventDefault();
        select('avatar');
        history.replaceState(null, '', '#ai-avatar');
    });
    select(location.hash.startsWith('#ai-') ? location.hash.slice(4) : 'credits');
});
</script>

@endsection
