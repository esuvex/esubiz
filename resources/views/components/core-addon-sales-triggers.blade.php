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
                {{--
                    ESUBIZ_UNIVERSAL_SALES_TRIGGER_LAYOUT_V3

                    One shared presentation for Dashboard, Settings,
                    Page Builder and Widgets.

                    No decorative icon and no floating badge.
                --}}
                <div class="flex flex-col items-start gap-3 sm:flex-row sm:items-center">
                    <div class="min-w-0 flex-1">
                        {{-- ESUBIZ_ADMIN_CONTROLLED_TRIGGER_CONTENT_RENDER_V1 --}}
                        @if(!empty($recommendation['title']))
                            <div class="text-base font-black text-blue-700">
                                {{ $recommendation['title'] }}
                            </div>
                        @endif

                        @if(!empty($recommendation['message']))
                            <div class="mt-1 text-sm leading-6 text-slate-600">
                                {{ $recommendation['message'] }}
                            </div>
                        @endif
                    </div>

                    @if(!empty($recommendation['cta_text']))
                        <button
                            type="button"
                            class="w-auto max-w-full shrink-0 rounded-xl bg-blue-600 px-5 py-3 text-sm font-black text-white shadow-sm transition hover:bg-blue-700 hover:shadow-md focus:outline-none focus:ring-4 focus:ring-blue-200"
                            data-addon-sales-trigger-cta
                            data-addon-id="{{ $recommendation['addon_id'] }}"
                            data-addon-key="{{ $recommendation['addon_key'] }}"
                            data-deployment-type="{{ $recommendation['deployment_type'] }}"
                        >
                            <span
    {{-- ESUBIZ_UNIVERSAL_PURCHASE_OPTION_SELECTOR_V2 --}}
    role="button"
    tabindex="0"
    class="cursor-pointer"
                @php
        /*
         * ESUBIZ_FEATURE_PLACEMENT_PURCHASE_OPTION_V1
         *
         * Dashboard keeps its resolver-generated resource alternatives.
         * Feature placements use the Add-on already resolved for their
         * trigger. No resource context is fabricated.
         */
        $esubizPlacementPurchaseOptions =
            $recommendation['purchase_options'] ?? [];

        if (
            (string) $location !== 'dashboard'
            && empty($esubizPlacementPurchaseOptions)
            && !empty($recommendation['addon_id'])
        ) {
            $esubizPlacementPurchaseOptions = [[
                'id' => (int) $recommendation['addon_id'],
                'addon_id' => (int) $recommendation['addon_id'],
                'product_id' => (int) $recommendation['addon_id'],
                'name' => $recommendation['addon_name']
                    ?? $recommendation['title']
                    ?? 'Add-on',
                'description' => $recommendation['message'] ?? null,
                'allocation' => $recommendation['allocation'] ?? 0,
                'is_unlimited' => $recommendation['is_unlimited'] ?? false,
                'unit' => $recommendation['unit'] ?? '',
            ]];
        }
    @endphp
    data-esubiz-addon-purchase-options='@json($esubizPlacementPurchaseOptions)'
    data-esubiz-addon-selector-title="{{ $recommendation['title'] ?? 'Choose an option' }}"
    {{-- ESUBIZ_UNIVERSAL_PLACEMENT_CHECKOUT_RELATIVE_URL_V1 --}}
    {{-- ESUBIZ_UNIVERSAL_TENANT_ADDON_CHECKOUT_URL_V1 --}}
    {{-- ESUBIZ_UNIVERSAL_DASHBOARD_CHECKOUT_HANDOFF_V1 --}}
{-- ESUBIZ_TENANT_ADMIN_ADDON_CHECKOUT_URL_V1 --}
    data-esubiz-checkout-url="{{ route('tenant.admin.addons.checkout', [
        'subdomain' => request()->route('subdomain'),
        'website' => (int) $website->id,
    ]) }}"
    {{-- ESUBIZ_UNIVERSAL_START_RETURN_CURRENT_PAGE_V1 --}}
    data-esubiz-start-url="{{ request()->fullUrl() }}"
    data-esubiz-return-url="{{ request()->fullUrl() }}"
    data-esubiz-return-area="{{ $location ?? '' }}"
    onclick="window.esubizAddonPurchaseSelect(this); return false;"
    onkeydown="if(event.key === 'Enter' || event.key === ' ') { event.preventDefault(); window.esubizAddonPurchaseSelect(this); }"
>{{ $recommendation['cta_text'] }}</span>
                            <span class="ml-1" aria-hidden="true">→</span>
                        </button>
                    @endif
                </div>
            </div>
        @endforeach
    </div>
@endif


{{-- ESUBIZ_UNIVERSAL_PURCHASE_OPTION_MODAL_V2 --}}
<div
    id="esubiz-addon-purchase-modal"
    class="fixed inset-0 z-[9999] hidden items-center justify-center p-4"
    role="dialog"
    aria-modal="true"
>
    <div
        class="absolute inset-0 bg-slate-950/50"
        onclick="window.esubizCloseAddonPurchaseModal()"
    ></div>

    {{-- ESUBIZ_PURCHASE_SELECTOR_LAYOUT_FIX_V1 --}}
    <div class="relative z-10 w-full max-w-lg overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-2xl">
        <div class="flex items-center justify-between gap-4 border-b border-slate-200 px-5 py-4">
            <div class="min-w-0">
                <h3
                    id="esubiz-addon-purchase-modal-title"
                    class="text-base font-semibold text-slate-900"
                >
                    Choose an option
                </h3>

                <p class="mt-1 text-xs text-slate-500">
                    Select the Add-on you want to purchase.
                </p>
            </div>

            <button
                type="button"
                onclick="window.esubizCloseAddonPurchaseModal()"
                class="shrink-0 rounded-lg px-2 py-1 text-xl leading-none text-slate-500 hover:bg-slate-100"
                aria-label="Close"
            >
                &times;
            </button>
        </div>

        <div
            id="esubiz-addon-purchase-modal-options"
            class="max-h-[65vh] space-y-3 overflow-y-auto p-5"
        ></div>
    </div>
</div>

<style>
    /*
     * ESUBIZ_PREMIUM_PURCHASE_SELECTOR_V1
     *
     * Premium universal Add-on selector.
     * Presentation only — no checkout logic changed.
     */
    #esubiz-addon-purchase-modal {
        padding: 20px;
    }

    #esubiz-addon-purchase-modal > .relative {
        width: min(100%, 560px);
        max-height: 88vh;
        margin: auto;
        overflow: hidden;
        border: 1px solid rgba(226, 232, 240, 0.9);
        border-radius: 24px;
        background: #ffffff;
        box-shadow:
            0 30px 80px rgba(15, 23, 42, 0.22),
            0 8px 24px rgba(15, 23, 42, 0.10);
    }

    #esubiz-addon-purchase-modal .border-b {
        padding: 22px 24px 18px;
        border-color: rgb(241 245 249);
        background:
            linear-gradient(
                180deg,
                rgb(248 250 252) 0%,
                rgb(255 255 255) 100%
            );
    }

    #esubiz-addon-purchase-modal-title {
        margin: 0;
        font-size: 18px;
        line-height: 1.35;
        font-weight: 800;
        letter-spacing: -0.01em;
        color: rgb(15 23 42);
    }

    #esubiz-addon-purchase-modal-title + p {
        margin-top: 6px;
        font-size: 13px;
        line-height: 1.5;
        color: rgb(100 116 139);
    }

    #esubiz-addon-purchase-modal-options {
        display: flex;
        flex-direction: column;
        gap: 12px;
        max-height: 62vh;
        padding: 20px 24px 24px;
        overflow-y: auto;
        background: rgb(255 255 255);
    }

    #esubiz-addon-purchase-modal-options > button {
        position: relative;
        display: block;
        width: 100%;
        padding: 16px 18px;
        border: 1px solid rgb(226 232 240);
        border-radius: 16px;
        background:
            linear-gradient(
                180deg,
                rgb(255 255 255) 0%,
                rgb(248 250 252) 100%
            );
        text-align: left;
        cursor: pointer;
        transition:
            border-color 160ms ease,
            box-shadow 160ms ease,
            transform 160ms ease,
            background 160ms ease;
    }

    #esubiz-addon-purchase-modal-options > button:hover {
        transform: translateY(-1px);
        border-color: rgb(59 130 246);
        background: rgb(255 255 255);
        box-shadow:
            0 10px 28px rgba(59, 130, 246, 0.10),
            0 2px 8px rgba(15, 23, 42, 0.06);
    }

    #esubiz-addon-purchase-modal-options > button:focus-visible {
        outline: none;
        border-color: rgb(37 99 235);
        box-shadow:
            0 0 0 4px rgba(59, 130, 246, 0.12),
            0 10px 28px rgba(59, 130, 246, 0.10);
    }

    #esubiz-addon-purchase-modal-options > button > * {
        display: block;
        width: 100%;
        min-width: 0;
        max-width: 100%;
        white-space: normal;
        overflow-wrap: anywhere;
    }

    #esubiz-addon-purchase-modal-options > button > div:first-child {
        font-size: 15px;
        line-height: 1.4;
        font-weight: 800;
        color: rgb(15 23 42);
    }

    #esubiz-addon-purchase-modal-options > button > div:nth-child(2) {
        margin-top: 5px;
        font-size: 13px;
        line-height: 1.55;
        font-weight: 400;
        color: rgb(100 116 139);
    }

    #esubiz-addon-purchase-modal-options > button > div:last-child {
        display: inline-flex;
        width: auto;
        margin-top: 12px;
        padding: 5px 9px;
        border: 1px solid rgb(219 234 254);
        border-radius: 999px;
        background: rgb(239 246 255);
        font-size: 11px;
        line-height: 1;
        font-weight: 800;
        letter-spacing: 0.02em;
        color: rgb(29 78 216);
    }

    #esubiz-addon-purchase-modal button[aria-label="Close"] {
        display: inline-flex;
        width: 34px;
        height: 34px;
        align-items: center;
        justify-content: center;
        border-radius: 10px;
        color: rgb(100 116 139);
        transition:
            background 150ms ease,
            color 150ms ease;
    }

    #esubiz-addon-purchase-modal button[aria-label="Close"]:hover {
        background: rgb(241 245 249);
        color: rgb(15 23 42);
    }

    @media (max-width: 640px) {
        #esubiz-addon-purchase-modal {
            align-items: flex-end;
            padding: 10px;
        }

        #esubiz-addon-purchase-modal > .relative {
            width: 100%;
            max-height: 92vh;
            border-radius: 20px;
        }

        #esubiz-addon-purchase-modal .border-b {
            padding: 18px 18px 16px;
        }

        #esubiz-addon-purchase-modal-options {
            padding: 16px 18px 20px;
        }
    }
</style>

<script>
(function () {
    if (window.esubizAddonPurchaseSelectorLoaded) {
        return;
    }

    window.esubizAddonPurchaseSelectorLoaded = true;

    /*
     * ESUBIZ_ADDON_CHECKOUT_HANDOFF_CLIENT_V1
     *
     * The selector emits only the selected central Add-on.
     * Server-side checkout service remains authoritative.
     */
    function emitSelection(option, source) {
        window.dispatchEvent(
            new CustomEvent(
                'esubiz-addon-checkout-selected',
                {
                    detail: {
                        option: option,
                        checkoutUrl:
                            source
                                ? source.getAttribute(
                                    'data-esubiz-checkout-url'
                                )
                                : null,
                        startUrl:
                            source
                                ? source.getAttribute(
                                    'data-esubiz-start-url'
                                )
                                : null,
                        returnUrl:
                            source
                                ? source.getAttribute(
                                    'data-esubiz-return-url'
                                )
                                : null,
                        returnArea:
                            source
                                ? source.getAttribute(
                                    'data-esubiz-return-area'
                                )
                                : null
                    }
                }
            )
        );
    }

    window.esubizCloseAddonPurchaseModal = function () {
        const modal = document.getElementById(
            'esubiz-addon-purchase-modal'
        );

        if (!modal) {
            return;
        }

        modal.classList.add('hidden');
        modal.classList.remove('flex');
    };

    window.esubizAddonPurchaseSelect = function (element) {
                let options = [];

        try {
            options = JSON.parse(
                element.getAttribute(
                    'data-esubiz-addon-purchase-options'
                ) || '[]'
            );
        } catch (error) {
            options = [];
        }

        if (!options.length) {
            return;
        }

        /*
         * One eligible Add-on:
         * skip product selection and proceed to the checkout
         * handoff that we connect next.
         */
        if (options.length === 1) {
            emitSelection(options[0], element);
            return;
        }

        const modal = document.getElementById(
            'esubiz-addon-purchase-modal'
        );

        const container = document.getElementById(
            'esubiz-addon-purchase-modal-options'
        );

        const title = document.getElementById(
            'esubiz-addon-purchase-modal-title'
        );

        if (!modal || !container || !title) {
            return;
        }

        title.textContent =
            element.getAttribute(
                'data-esubiz-addon-selector-title'
            ) || 'Choose an option';

        container.innerHTML = '';

        /*
         * Preserve the CTA that opened this selector so the chosen
         * product returns to the correct website placement.
         */
        modal._esubizPurchaseSource = element;

        options.forEach(function (option) {
            const product = document.createElement('button');

            product.type = 'button';

            product.className =
                'block w-full rounded-xl border border-slate-200 ' +
                'p-4 text-left transition hover:border-slate-400 ' +
                'hover:bg-slate-50';

            const name = document.createElement('div');

            name.className =
                'text-sm font-semibold text-slate-900';

            name.textContent =
                option.name || 'Add-on';

            product.appendChild(name);

            if (option.description) {
                const description =
                    document.createElement('div');

                description.className =
                    'mt-1 text-xs leading-5 text-slate-500';

                description.textContent =
                    option.description;

                product.appendChild(description);
            }

            /*
             * ESUBIZ_PURCHASE_OPTION_ALLOCATION_DISPLAY_V1
             *
             * Every Add-on option shows the actual allocation it
             * provides for this resource/capability.
             */
            const allocation =
                document.createElement('div');

            allocation.className =
                'mt-2 text-xs font-semibold text-slate-700';

            if (option.is_unlimited) {
                allocation.textContent =
                    'Unlimited';
            } else {
                const numericAllocation =
                    Number(option.allocation || 0);

                const formattedAllocation =
                    Number.isInteger(numericAllocation)
                        ? String(numericAllocation)
                        : String(numericAllocation);

                const unit =
                    String(option.unit || '').trim();

                allocation.textContent =
                    unit
                        ? formattedAllocation + ' ' + unit
                        : formattedAllocation;
            }

            product.appendChild(allocation);

            product.addEventListener(
                'click',
                function () {
                    const source =
                        modal._esubizPurchaseSource || null;

                    window.esubizCloseAddonPurchaseModal();
                    emitSelection(option, source);
                }
            );

            container.appendChild(product);
        });

        modal.classList.remove('hidden');
        modal.classList.add('flex');
    };

    /*
     * Selected Add-on -> existing signed SaaS Marketplace checkout.
     */
    window.addEventListener(
        'esubiz-addon-checkout-selected',
        async function (event) {
            const detail = event.detail || {};
            const option = detail.option || {};
            const checkoutUrl = detail.checkoutUrl;

            /*
             * ESUBIZ_SELECTED_ADDON_ID_NORMALIZATION_V1
             *
             * Universal selector options may expose the central Add-on
             * identity as addon_id, product_id or id.
             *
             * Normalize that identity here without changing the resolver,
             * placement rendering or Marketplace checkout.
             */
            const selectedAddonId = Number(
                option.addon_id
                || option.product_id
                || option.id
                || 0
            );

            if (!checkoutUrl || selectedAddonId <= 0) {
                return;
            }

            try {
                const token =
                    document.querySelector(
                        'meta[name="csrf-token"]'
                    )?.getAttribute('content') || '';

                const response = await fetch(
                    checkoutUrl,
                    {
                        method: 'POST',
                        credentials: 'same-origin',
                        headers: {
                            'Accept': 'application/json',
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': token,
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        body: JSON.stringify({
                            addon_id:
                                selectedAddonId,

                            start_url:
                            detail.startUrl || window.location.href,

                        return_url:
                            detail.returnUrl
                                || detail.startUrl
                                || window.location.href,

                            return_area:
                                detail.returnArea || null
                        })
                    }
                );

                /*
                 * ESUBIZ_CHECKOUT_EXACT_ERROR_V1
                 *
                 * Preserve checkout behavior but expose the actual HTTP
                 * response instead of hiding it behind the generic popup.
                 */
                const responseText = await response.text();

                let payload = {};

                try {
                    payload = responseText
                        ? JSON.parse(responseText)
                        : {};
                } catch (e) {
                    payload = {};
                }

                if (!response.ok || !payload.url) {
                    throw new Error(
                        'Checkout URL: ' + checkoutUrl + '\nHTTP ' + response.status +
                        (
                            payload.message
                                ? ': ' + payload.message
                                : (
                                    responseText
                                        ? ': ' + responseText
                                            .replace(/<[^>]*>/g, ' ')
                                            .replace(/\s+/g, ' ')
                                            .trim()
                                            .substring(0, 180)
                                        : ''
                                )
                        )
                    );
                }

                window.location.assign(
                    payload.url
                );
            } catch (error) {
                console.error(
                    'Esubiz Add-on checkout:',
                    error
                );

                window.alert(
                    error.message
                    || 'Unable to start checkout. Please try again.'
                );
            }
        }
    );

    document.addEventListener(
        'keydown',
        function (event) {
            if (event.key === 'Escape') {
                window.esubizCloseAddonPurchaseModal();
            }
        }
    );
})();
</script>
