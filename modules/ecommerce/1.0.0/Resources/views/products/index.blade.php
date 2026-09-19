@extends('tenant.admin.layouts.app')

@section('title', 'Products')

@section('content')
<div class="mx-auto max-w-7xl space-y-6" id="ecommerce-products-page">

    <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <div class="text-xs font-black uppercase tracking-[.16em] text-blue-600">
                Ecommerce Module
            </div>

            <h1 class="mt-2 text-3xl font-black tracking-tight text-slate-900">
                Products
            </h1>

            <p class="mt-2 text-sm leading-6 text-slate-500">
                Manage products, pricing, SKUs, stock and product status.
            </p>
        </div>

        <button
            type="button"
            class="inline-flex items-center justify-center rounded-xl bg-blue-600 px-4 py-2.5 text-sm font-black text-white hover:bg-blue-700"
        >
            Add Product
        </button>
    </div>

    <div class="rounded-2xl border border-slate-200 bg-white shadow-sm">
        <form
            method="GET"
            action="{{ route('core.ecommerce.products.index') }}"
            class="grid gap-3 border-b border-slate-200 p-4 md:grid-cols-[1fr_190px_auto]"
            data-products-filter
        >
            <input
                type="search"
                name="search"
                value="{{ $search }}"
                placeholder="Search product or SKU..."
                class="w-full rounded-xl border border-slate-300 px-4 py-2.5 text-sm"
            >

            <select
                name="status"
                class="w-full rounded-xl border border-slate-300 px-4 py-2.5 text-sm"
            >
                <option value="">All Statuses</option>
                <option value="active" @selected($status === 'active')>
                    Active
                </option>
                <option value="draft" @selected($status === 'draft')>
                    Draft
                </option>
                <option value="archived" @selected($status === 'archived')>
                    Archived
                </option>
            </select>

            <button
                type="submit"
                class="rounded-xl border border-slate-300 px-4 py-2.5 text-sm font-black text-slate-700 hover:bg-slate-50"
            >
                Filter
            </button>
        </form>

        <div data-products-results>
            @include('ecommerce::products.table', [
                'products' => $products,
            ])
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const page = document.getElementById('ecommerce-products-page');

    if (!page) return;

    const form = page.querySelector('[data-products-filter]');
    const results = page.querySelector('[data-products-results]');

    const load = async (url) => {
        const response = await fetch(url, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
            },
        });

        if (!response.ok) return;

        const html = await response.text();
        const parser = new DOMParser();
        const documentResult = parser.parseFromString(html, 'text/html');
        const nextResults = documentResult.querySelector(
            '[data-products-results]'
        );

        if (!nextResults) return;

        results.innerHTML = nextResults.innerHTML;
        window.history.replaceState({}, '', url);
    };

    form.addEventListener('submit', (event) => {
        event.preventDefault();

        const params = new URLSearchParams(new FormData(form));
        const url = form.action + '?' + params.toString();

        load(url);
    });

    page.addEventListener('click', (event) => {
        const link = event.target.closest(
            '[data-products-pagination] a'
        );

        if (!link) return;

        event.preventDefault();
        load(link.href);
    });
});
</script>
@endsection
