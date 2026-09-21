{{-- ESUBIZ_CORE_RESOURCE_MEASUREMENT_V1 --}}
@php
    $coreResourceMeasurement = app(
        \App\Services\Core\Resources\CoreResourceMeasurementService::class
    )->measure(
        $resourceKey,
        $website,
        $salesTriggerLocation ?? null
    );
@endphp

@if($coreResourceMeasurement['resolved'])
    <div
        class="flex flex-col gap-3 rounded-2xl border border-slate-200 bg-white px-5 py-4 shadow-sm sm:flex-row sm:items-center sm:justify-between"
        data-core-resource-measurement="{{ $resourceKey }}"
    >
        <div class="flex items-center gap-4">
            <div>
                <div class="text-xs font-black uppercase tracking-wide text-slate-400">
                    {{ $label ?? 'Usage' }}
                </div>

                <div class="mt-1 text-lg font-black text-slate-900">
                    {{ $coreResourceMeasurement['usage_text'] }}
                </div>
            </div>

            @if(!$coreResourceMeasurement['is_unlimited'])
                <div class="hidden h-9 w-px bg-slate-200 sm:block"></div>

                <div class="text-sm font-semibold text-slate-500">
                    {{ $coreResourceMeasurement['percentage_text'] }}
                </div>
            @endif
        </div>

        @if($coreResourceMeasurement['sales_triggers']->isNotEmpty())
            <div class="flex flex-wrap items-center gap-2">
                @foreach($coreResourceMeasurement['sales_triggers'] as $trigger)
                    <button
                        type="button"
                        class="inline-flex items-center justify-center rounded-xl bg-blue-600 px-4 py-2 text-xs font-black text-white hover:bg-blue-700"
                        data-core-addon-sales-trigger
                        data-trigger-id="{{ $trigger['trigger_id'] }}"
                        data-addon-id="{{ $trigger['addon_id'] }}"
                        data-addon-uuid="{{ $trigger['addon_uuid'] }}"
                        data-resource-key="{{ $trigger['resource_key'] ?? $resourceKey }}"
                        data-purchase-options='@json($trigger["purchase_options"] ?? [])'
                    >
                        {{ filled($trigger['cta_text'] ?? null)
                            ? $trigger['cta_text']
                            : 'Increase Limit' }}
                    </button>
                @endforeach
            </div>
        @endif
    </div>
@endif
