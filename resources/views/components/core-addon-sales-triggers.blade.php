{{--
    ESUBIZ_GENERIC_ADDON_SALES_TRIGGER_COMPONENT_V1

    Universal Add-on sales-trigger hook.

    Usage from any registered Core/module location:

    <x-core-addon-sales-triggers
        location="page_builder.widgets"
        :website="$website"
    />

    The component contains no product-specific logic.
--}}

@props([
    'location',
    'website',
    'context' => [],
])

@php
    $salesTriggerRecommendations = collect();

    if (
        !empty($location) &&
        isset($website) &&
        is_object($website)
    ) {
        try {
            $salesTriggerRecommendations = app(
                \App\Services\Core\CoreAddonSalesTriggerResolver::class
            )->resolve(
                (string) $location,
                $website,
                is_array($context) ? $context : []
            );
        } catch (\Throwable $e) {
            report($e);
            $salesTriggerRecommendations = collect();
        }
    }
@endphp

@if($salesTriggerRecommendations->isNotEmpty())
    <div
        {{ $attributes->merge([
            'class' => 'esubiz-addon-sales-triggers space-y-3',
        ]) }}
        data-sales-trigger-location="{{ $location }}"
    >
        {{-- ESUBIZ_GENERIC_SALES_TRIGGER_BLUE_CARD_V1 --}}
        @foreach($salesTriggerRecommendations as $recommendation)
            <div
                class="relative overflow-hidden rounded-2xl border-2 border-blue-500 bg-gradient-to-br from-blue-50 via-white to-blue-50 p-4 shadow-sm transition hover:shadow-md"
                data-addon-sales-trigger="{{ $recommendation['trigger_id'] }}"
                data-addon-id="{{ $recommendation['addon_id'] }}"
                data-addon-key="{{ $recommendation['addon_key'] }}"
                data-deployment-type="{{ $recommendation['deployment_type'] }}"
            >
                <div
                    class="absolute right-3 top-3 rounded-md bg-blue-100 px-2 py-1 text-[10px] font-black uppercase tracking-wider text-blue-700"
                >
                    Pro Feature
                </div>

                <div class="flex items-center gap-4 pr-24">
                    <div
                        class="flex h-14 w-14 shrink-0 items-center justify-center rounded-full border border-blue-200 bg-white text-blue-600 shadow-sm"
                    >
                        <svg
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="2"
                            class="h-7 w-7"
                            aria-hidden="true"
                        >
                            <circle cx="12" cy="12" r="9"></circle>
                            <path d="M3 12h18"></path>
                            <path d="M12 3a15 15 0 0 1 0 18"></path>
                            <path d="M12 3a15 15 0 0 0 0 18"></path>
                        </svg>
                    </div>

                    <div class="min-w-0 flex-1">
                        <div class="text-base font-black text-blue-700">
                            {{ $recommendation['title'] }}
                        </div>

                        @if(!empty($recommendation['message']))
                            <div class="mt-1 text-sm leading-6 text-slate-600">
                                {{ $recommendation['message'] }}
                            </div>
                        @endif
                    </div>

                    <button
                        type="button"
                        class="shrink-0 rounded-xl bg-blue-600 px-5 py-3 text-sm font-black text-white shadow-sm transition hover:bg-blue-700 hover:shadow-md focus:outline-none focus:ring-4 focus:ring-blue-200"
                        data-addon-sales-trigger-cta
                        data-addon-id="{{ $recommendation['addon_id'] }}"
                        data-addon-key="{{ $recommendation['addon_key'] }}"
                        data-deployment-type="{{ $recommendation['deployment_type'] }}"
                    >
                        {{ $recommendation['cta_text'] }}
                        <span class="ml-1" aria-hidden="true">→</span>
                    </button>
                </div>
            </div>
        @endforeach
    </div>
@endif
