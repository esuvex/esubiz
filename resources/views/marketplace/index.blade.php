@extends('admin.layouts.app')

@section('content')
<div class="mx-auto max-w-7xl space-y-7">

    <div class="rounded-3xl bg-gradient-to-br from-slate-950 via-slate-900 to-blue-950 px-6 py-8 text-white shadow-sm sm:px-8">
        <div class="max-w-3xl">
            <p class="text-xs font-bold uppercase tracking-[0.2em] text-blue-300">Esubiz Marketplace</p>
            <h1 class="mt-3 text-3xl font-bold tracking-tight sm:text-4xl">Products for your Esubiz platform</h1>
            <p class="mt-3 text-sm leading-6 text-slate-300 sm:text-base">
                Browse Esubiz products by category. Product pricing, compatibility and availability are resolved for the applicable deployment when you open a catalog.
            </p>
        </div>
    </div>

    <div>
        <div class="mb-5 flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <h2 class="text-xl font-bold text-slate-900">Marketplace Products</h2>
                <p class="mt-1 text-sm text-slate-500">Choose a product family to continue.</p>
            </div>

            <div class="w-full sm:w-80">
                <input
                    id="marketplaceProductSearch"
                    type="search"
                    placeholder="Search marketplace..."
                    class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm outline-none transition focus:border-blue-500 focus:ring-4 focus:ring-blue-100"
                >
            </div>
        </div>

        <div id="marketplaceProductGrid" class="grid grid-cols-1 gap-5 md:grid-cols-2 xl:grid-cols-3">
            @foreach($products as $product)
                <a
                    href="{{ $product['url'] }}"
                    data-marketplace-product="{{ strtolower($product['name'].' '.$product['description']) }}"
                    class="group flex min-h-[210px] flex-col rounded-2xl border border-slate-200 bg-white p-6 shadow-sm transition hover:-translate-y-0.5 hover:border-blue-200 hover:shadow-lg"
                >
                    <div class="flex items-start justify-between gap-4">
                        <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-slate-100 text-2xl transition group-hover:bg-blue-50">
                            {{ $product['icon'] }}
                        </div>

                        @if($product['available'])
                            <span class="rounded-full bg-emerald-50 px-3 py-1 text-xs font-bold text-emerald-700">Available</span>
                        @else
                            <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-bold text-slate-500">Coming Soon</span>
                        @endif
                    </div>

                    <div class="mt-5 flex-1">
                        <h3 class="text-lg font-bold text-slate-900 group-hover:text-blue-700">
                            {{ $product['name'] }}
                        </h3>
                        <p class="mt-2 text-sm leading-6 text-slate-500">
                            {{ $product['description'] }}
                        </p>
                    </div>

                    <div class="mt-5 flex items-center justify-between border-t border-slate-100 pt-4">
                        <span class="text-sm font-semibold text-blue-700">
                            {{ $product['available'] ? 'Browse Products' : 'View Product' }}
                        </span>
                        <span class="text-lg text-slate-400 transition group-hover:translate-x-1 group-hover:text-blue-600">→</span>
                    </div>
                </a>
            @endforeach
        </div>

        <div id="marketplaceNoResults" class="hidden rounded-2xl border border-dashed border-slate-300 bg-white p-10 text-center">
            <p class="font-semibold text-slate-700">No marketplace products found.</p>
            <p class="mt-1 text-sm text-slate-500">Try another search term.</p>
        </div>
    </div>

</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const input = document.getElementById('marketplaceProductSearch');
    const cards = Array.from(document.querySelectorAll('[data-marketplace-product]'));
    const empty = document.getElementById('marketplaceNoResults');

    if (!input) return;

    input.addEventListener('input', function () {
        const query = this.value.trim().toLowerCase();
        let visible = 0;

        cards.forEach(card => {
            const match = !query || card.dataset.marketplaceProduct.includes(query);
            card.classList.toggle('hidden', !match);
            if (match) visible++;
        });

        empty.classList.toggle('hidden', visible !== 0);
    });
});
</script>
@endsection
