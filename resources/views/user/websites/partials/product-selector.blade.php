{{-- ESUBIZ_WEBSITE_EDIT_SINGLE_PRODUCT_ORDER_V1
One selector submission = one product/package grant or order.
No quantity selection on Website Edit.
--}}
{{-- ESUBIZ_SHARED_CASCADING_PRODUCT_SELECTOR_V1 --}}
@php
    $selectorMode =
        $selectorMode ?? 'user';

    $selectorTree = app(
        \App\Services\Core\WebsiteProductSelectorService::class
    )->tree($website ?? null);

    $selectorIsAdmin =
        $selectorMode === 'admin';

    $selectorId =
        'website-product-selector-'
        . $selectorMode;
@endphp

<div
    id="{{ $selectorId }}"
    class="mt-6 rounded-2xl border border-slate-200 bg-white p-5"
    data-website-product-selector
>
    <div class="mb-5">
        <div
            class="text-xs font-black uppercase tracking-widest text-blue-600"
        >
            Products
        </div>

        <h3 class="mt-2 text-lg font-black text-slate-900">
            {{ $selectorIsAdmin ? 'Add Product' : 'Purchase Product' }}
        </h3>

        <p class="mt-1 text-sm leading-6 text-slate-500">
            Select the product category, product and package or option.
        </p>
    </div>

    <form
        method="POST"
        action="{{
            $selectorIsAdmin
                ? $updateRoute
                : route('marketplace.checkout.create')
        }}"
        data-product-selector-form
    >
        @csrf

        @if($selectorIsAdmin)
            @method('PATCH')

            <input
                type="hidden"
                name="section"
                value="product_grant"
                data-product-section
            >
        @else
            <input
                type="hidden"
                name="website_id"
                value="{{ $website->id }}"
            >

            <input
                type="hidden"
                name="deployment_type"
                value="{{
                    !empty($website->subdomain)
                        ? 'saas'
                        : 'off_server'
                }}"
            >

            <input
                type="hidden"
                name="checkout_origin"
                value="central_account"
            >
        @endif

        <input
            type="hidden"
            name="product_type"
            data-selected-product-type
        >

        <input
            type="hidden"
            name="product_id"
            data-selected-product-id
        >

        @if($selectorIsAdmin)
            <input
                type="hidden"
                name="credit_package_id"
                data-selected-credit-package-id
            >
        @endif

        <div class="grid gap-4 md:grid-cols-2">
            <div>
                <label
                    class="mb-2 block text-sm font-bold text-slate-700"
                >
                    Product Category
                </label>

                <select
                    required
                    data-product-category
                    class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-800"
                >
                    <option value="">
                        Select category
                    </option>
                </select>
            </div>

            <div>
                <label
                    class="mb-2 block text-sm font-bold text-slate-700"
                >
                    Product
                </label>

                <select
                    required
                    disabled
                    data-product-family
                    class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-800 disabled:bg-slate-100"
                >
                    <option value="">
                        Select product
                    </option>
                </select>
            </div>

            <div>
                <label
                    class="mb-2 block text-sm font-bold text-slate-700"
                >
                    Package / Option
                </label>

                <select
                    required
                    disabled
                    data-product-option
                    class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-800 disabled:bg-slate-100"
                >
                    <option value="">
                        Select package
                    </option>
                </select>
            </div>


        </div>

        <div
            class="mt-4 hidden rounded-xl bg-slate-50 p-4"
            data-product-summary
        >
            <div
                class="font-bold text-slate-900"
                data-summary-name
            ></div>

            <div
                class="mt-1 text-sm text-slate-500"
                data-summary-unit
            ></div>

            <div
                class="mt-3 flex items-center justify-between border-t border-slate-200 pt-3"
            >
                <span class="text-sm font-bold text-slate-600">
                    Total Value
                </span>

                <span
                    class="text-lg font-black text-slate-900"
                    data-summary-total
                ></span>
            </div>
        </div>

        <button
            type="submit"
            class="mt-5 inline-flex items-center justify-center rounded-xl bg-slate-900 px-5 py-3 text-sm font-bold text-white transition hover:bg-slate-800"
        >
            {{
                $selectorIsAdmin
                    ? 'Grant Product'
                    : 'Proceed to Checkout'
            }}
        </button>
    </form>
</div>

{{-- ESUBIZ_SELECTOR_CHECKOUT_IDENTITY_JS_V2 --}}
<script>
(() => {
    const root =
        document.getElementById(
            @json($selectorId)
        );

    if (!root) {
        return;
    }

    const tree =
        @json($selectorTree);

    const isAdmin =
        @json($selectorIsAdmin);

    const category =
        root.querySelector(
            '[data-product-category]'
        );

    const product =
        root.querySelector(
            '[data-product-family]'
        );

    const option =
        root.querySelector(
            '[data-product-option]'
        );

    const quantity =
        root.querySelector(
            '[data-product-quantity]'
        );

    const typeInput =
        root.querySelector(
            '[data-selected-product-type]'
        );

    const idInput =
        root.querySelector(
            '[data-selected-product-id]'
        );

    const packageInput =
        root.querySelector(
            '[data-selected-credit-package-id]'
        );

    const sectionInput =
        root.querySelector(
            '[data-product-section]'
        );

    const summary =
        root.querySelector(
            '[data-product-summary]'
        );

    const summaryName =
        root.querySelector(
            '[data-summary-name]'
        );

    const summaryUnit =
        root.querySelector(
            '[data-summary-unit]'
        );

    const summaryTotal =
        root.querySelector(
            '[data-summary-total]'
        );

    let selectedChoice = null;

    const resetSelect = (
        field,
        placeholder
    ) => {
        field.innerHTML = '';

        const first =
            document.createElement(
                'option'
            );

        first.value = '';
        first.textContent = placeholder;

        field.appendChild(first);
    };

    const money = (
        value,
        currency
    ) => {
        return `${
            currency || 'NGN'
        } ${
            Number(value).toLocaleString(
                undefined,
                {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2,
                }
            )
        }`;
    };

    const updateSummary = () => {
        if (!selectedChoice) {
            summary.classList.add(
                'hidden'
            );

            return;
        }

        const units =
            Math.max(
                1,
                parseInt(
                    quantity.value || '1',
                    10
                )
            );

        quantity.value = units;

        summaryName.textContent =
            selectedChoice.name || '';

        if (
            selectedChoice.quantity_value
        ) {
            const totalCredits =
                Number(
                    selectedChoice.quantity_value
                ) * units;

            summaryUnit.textContent =
                `${units.toLocaleString()} × ${
                    Number(
                        selectedChoice.quantity_value
                    ).toLocaleString()
                } credits = ${
                    totalCredits.toLocaleString()
                } credits`;
        } else {
            summaryUnit.textContent =
                `Quantity: ${
                    units.toLocaleString()
                }`;
        }

        if (
            selectedChoice.price !== null
            && selectedChoice.price
                !== undefined
        ) {
            const total =
                Number(
                    selectedChoice.price
                ) * units;

            summaryTotal.textContent =
                money(
                    total,
                    selectedChoice.currency
                );
        } else {
            summaryTotal.textContent =
                'Resolved at checkout';
        }

        summary.classList.remove(
            'hidden'
        );
    };

    Object.entries(tree).forEach(
        ([key, group]) => {
            const item =
                document.createElement(
                    'option'
                );

            item.value = key;
            item.textContent =
                group.label || key;

            category.appendChild(item);
        }
    );

    category.addEventListener(
        'change',
        () => {
            resetSelect(
                product,
                'Select product'
            );

            resetSelect(
                option,
                'Select package'
            );

            product.disabled = true;
            option.disabled = true;

            selectedChoice = null;

            typeInput.value = '';
            idInput.value = '';

            if (packageInput) {
                packageInput.value = '';
            }

            summary.classList.add(
                'hidden'
            );

            const group =
                tree[category.value];

            if (
                !group
                || !Array.isArray(
                    group.products
                )
            ) {
                return;
            }

            group.products.forEach(
                (entry, index) => {
                    const item =
                        document.createElement(
                            'option'
                        );

                    item.value = index;

                    item.textContent =
                        entry.label
                        || `Product ${
                            index + 1
                        }`;

                    product.appendChild(
                        item
                    );
                }
            );

            product.disabled = false;
        }
    );

    product.addEventListener(
        'change',
        () => {
            resetSelect(
                option,
                'Select package'
            );

            option.disabled = true;

            selectedChoice = null;

            const group =
                tree[category.value];

            const entry =
                group?.products?.[
                    Number(product.value)
                ];

            if (
                !entry
                || !Array.isArray(
                    entry.options
                )
            ) {
                return;
            }

            entry.options.forEach(
                (choice, index) => {
                    const item =
                        document.createElement(
                            'option'
                        );

                    item.value = index;

                    item.textContent =
                        choice.price !== null
                        && choice.price
                            !== undefined
                            ? `${
                                choice.name
                            } — ${
                                money(
                                    choice.price,
                                    choice.currency
                                )
                            }`
                            : choice.name;

                    option.appendChild(
                        item
                    );
                }
            );

            option.disabled = false;
        }
    );

    option.addEventListener(
        'change',
        () => {
            const group =
                tree[category.value];

            const entry =
                group?.products?.[
                    Number(product.value)
                ];

            selectedChoice =
                entry?.options?.[
                    Number(option.value)
                ] || null;

            if (!selectedChoice) {
                return;
            }

            typeInput.value =
                (
                    selectedChoice.checkout_product_type
                    ?? selectedChoice.product_type
                    ?? ''
                )
                || '';

            idInput.value =
                (
                    selectedChoice.checkout_product_id
                    ?? selectedChoice.catalog_product_id
                    ?? selectedChoice.id
                    ?? ''
                )
                || selectedChoice.id
                || '';

            if (packageInput) {
                packageInput.value =
                    category.value === 'credits'
                        ? (
                            selectedChoice.id
                            || ''
                        )
                        : '';
            }

            if (sectionInput) {
                sectionInput.value =
                    category.value === 'credits'
                        ? 'credits'
                        : 'product_grant';
            }

            updateSummary();
        }
    );

    quantity.addEventListener(
        'input',
        updateSummary
    );

    quantity.addEventListener(
        'change',
        updateSummary
    );
})();
</script>
