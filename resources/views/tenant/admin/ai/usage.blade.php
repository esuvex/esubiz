{{-- ESUBIZ_TENANT_AI_USAGE_PRICING_PAGE_V1 --}}
@extends('tenant.admin.layouts.app')

@section('title', 'AI Usage & Pricing')

@section('content')

<div class="mx-auto max-w-7xl space-y-8">

    <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
        <div>
            <div class="text-xs font-black uppercase tracking-[.16em] text-blue-600">
                Esubiz AI
            </div>

            <h1 class="mt-2 text-3xl font-black text-slate-900">
                AI Usage & Pricing
            </h1>

            <p class="mt-2 max-w-3xl text-sm leading-6 text-slate-500">
                See estimated AI Credit costs and review your website's
                recent AI consumption.
            </p>
        </div>

        <div class="rounded-2xl border border-blue-100 bg-blue-50 px-5 py-4">
            <div class="text-[10px] font-black uppercase tracking-wide text-blue-500">
                Available Balance
            </div>

            <div class="mt-1 text-2xl font-black text-blue-700">
                {{ number_format((float) $aiCreditBalance, 0) }}
                Credits
            </div>
        </div>
    </div>


    {{-- =====================================================
         PRICING FIRST
    ====================================================== --}}
    {{-- ESUBIZ_AI_USAGE_AJAX_PRICING_TARGET_V1 --}}
    <section
        id="ai-pricing-card"
        data-ai-pagination-card="pricing"
        class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm"
    >

        <div>
            <div class="text-xs font-black uppercase tracking-[.16em] text-blue-600">
                AI Pricing Guide
            </div>

            <h2 class="mt-2 text-2xl font-black text-slate-900">
                Estimated Credit Usage
            </h2>

            <p class="mt-2 max-w-4xl text-sm leading-6 text-slate-500">
                These ranges help you estimate the AI Credits a task may use.
                Actual usage can vary depending on the size and complexity
                of your request.
            </p>
        </div>


        <div class="mt-6 overflow-x-auto">
            <table class="w-full min-w-[680px] text-left text-sm">

                <thead class="border-b border-slate-200 text-xs uppercase text-slate-400">
                    <tr>
                        <th class="px-3 py-3">AI Task</th>
                        <th class="px-3 py-3">What It Covers</th>
                        <th class="px-3 py-3 text-right">Estimated Credits</th>
                    </tr>
                </thead>

                <tbody>
                    @foreach($pricingGuide as $item)
                        <tr class="border-b border-slate-100">
                            <td class="px-3 py-4 font-black text-slate-900">
                                {{ $item['task'] }}
                            </td>

                            <td class="px-3 py-4 text-slate-500">
                                {{ $item['description'] }}
                            </td>

                            <td class="px-3 py-4 text-right font-black text-blue-600">
                                {{ number_format($item['minimum_credits']) }}
                                –
                                {{ number_format($item['maximum_credits']) }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>

            </table>
        </div>


        @if($pricingGuide->hasPages())
            <div class="mt-6">
                {{ $pricingGuide->links() }}
            </div>
        @endif

    </section>


    {{-- =====================================================
         HISTORY UNDER PRICING
    ====================================================== --}}
    {{-- ESUBIZ_AI_USAGE_AJAX_HISTORY_TARGET_V1 --}}
    <section
        id="ai-history-card"
        data-ai-pagination-card="history"
        class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm"
    >

        <div>
            <div class="text-xs font-black uppercase tracking-[.16em] text-blue-600">
                AI Consumption
            </div>

            <h2 class="mt-2 text-2xl font-black text-slate-900">
                Usage History
            </h2>

            <p class="mt-2 text-sm leading-6 text-slate-500">
                Your website's recent AI Credit consumption.
            </p>
        </div>


        <div class="mt-6 overflow-x-auto">
            <table class="w-full min-w-[760px] text-left text-sm">

                <thead class="border-b border-slate-200 text-xs uppercase text-slate-400">
                    <tr>
                        <th class="px-3 py-3">Task</th>
                        <th class="px-3 py-3">Credits Used</th>
                        <th class="px-3 py-3">Balance After</th>
                        <th class="px-3 py-3">Status</th>
                        <th class="px-3 py-3">Date</th>
                    </tr>
                </thead>

                <tbody>

                    @forelse($usageHistory as $usage)

                        @php
                            $taskLabel =
                                match($usage->route_key) {
                                    'site' =>
                                        'Website AI',

                                    'live_chat' =>
                                        'Live Chat AI',

                                    'whatsapp' =>
                                        'WhatsApp AI',

                                    'email' =>
                                        'Email AI',

                                    'sms' =>
                                        'SMS AI',

                                    'social_media' =>
                                        'Social Media AI',

                                    'ads' =>
                                        'Ads AI',

                                    'theme.homepage' =>
                                        'Website Generation',

                                    'app_builder' =>
                                        'App Builder AI',

                                    'module_builder' =>
                                        'Module Builder AI',

                                    'addon_builder' =>
                                        'Addon Builder AI',

                                    default =>
                                        'Esubiz AI',
                                };
                        @endphp

                        <tr class="border-b border-slate-100">

                            <td class="px-3 py-4 font-black text-slate-900">
                                {{ $taskLabel }}
                            </td>

                            <td class="px-3 py-4 font-black text-blue-600">
                                {{ number_format((float) $usage->credits, 0) }}
                            </td>

                            <td class="px-3 py-4 text-slate-600">
                                {{ $usage->balance_after !== null
                                    ? number_format((float) $usage->balance_after, 0)
                                    : '—' }}
                            </td>

                            <td class="px-3 py-4">
                                <span class="inline-flex rounded-full bg-emerald-50 px-3 py-1 text-xs font-black text-emerald-700">
                                    {{ ucfirst($usage->status ?: 'completed') }}
                                </span>
                            </td>

                            <td class="px-3 py-4 text-slate-500">
                                {{ optional($usage->created_at)->format('d M Y, g:i A') }}
                            </td>

                        </tr>

                    @empty

                        <tr>
                            <td colspan="5" class="px-3 py-10 text-center text-slate-500">
                                No AI usage has been recorded for this website yet.
                            </td>
                        </tr>

                    @endforelse

                </tbody>

            </table>
        </div>


        @if($usageHistory->hasPages())
            <div class="mt-6">
                {{ $usageHistory->links() }}
            </div>
        @endif

    </section>

</div>


{{-- ESUBIZ_AI_USAGE_AJAX_PAGINATION_V1 --}}
<script>
document.addEventListener('DOMContentLoaded', function () {
    const selector = '[data-ai-pagination-card]';

    async function loadAiPage(card, url) {
        if (!card || !url || card.dataset.loading === '1') {
            return;
        }

        card.dataset.loading = '1';

        const originalOpacity = card.style.opacity;
        const originalPointerEvents = card.style.pointerEvents;

        card.style.opacity = '0.55';
        card.style.pointerEvents = 'none';

        try {
            const response = await fetch(url, {
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'text/html'
                },
                credentials: 'same-origin'
            });

            if (!response.ok) {
                throw new Error(
                    'Pagination request failed with HTTP '
                    + response.status
                );
            }

            const html = await response.text();

            const documentResult =
                new DOMParser().parseFromString(
                    html,
                    'text/html'
                );

            const cardType =
                card.dataset.aiPaginationCard;

            const replacement =
                documentResult.querySelector(
                    '[data-ai-pagination-card="'
                    + cardType
                    + '"]'
                );

            if (!replacement) {
                throw new Error(
                    'Pagination replacement card was not found.'
                );
            }

            card.replaceWith(replacement);

            /*
             * Keep the browser URL synchronized without causing
             * a full page navigation.
             */
            window.history.replaceState(
                {},
                '',
                url
            );

            replacement.scrollIntoView({
                behavior: 'smooth',
                block: 'nearest'
            });

        } catch (error) {
            console.error(
                'Esubiz AI pagination:',
                error
            );

            /*
             * Graceful fallback: if AJAX ever fails, use Laravel's
             * normal URL so pagination remains functional.
             */
            window.location.href = url;

        } finally {
            if (card.isConnected) {
                card.dataset.loading = '0';
                card.style.opacity = originalOpacity;
                card.style.pointerEvents = originalPointerEvents;
            }
        }
    }


    document.addEventListener('click', function (event) {
        const link =
            event.target.closest(
                '[data-ai-pagination-card] nav a'
            );

        if (!link) {
            return;
        }

        const card =
            link.closest(
                selector
            );

        if (!card) {
            return;
        }

        const url =
            link.getAttribute('href');

        if (!url) {
            return;
        }

        event.preventDefault();

        loadAiPage(
            card,
            url
        );
    });
});
</script>

@endsection
