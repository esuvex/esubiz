{{-- ESUBIZ_ECOMMERCE_PRODUCTS_TABLE_V1 --}}
<div class="overflow-x-auto">
    <table class="min-w-full divide-y divide-slate-200">
        <thead class="bg-slate-50">
            <tr>
                @foreach([
                    'Product',
                    'SKU',
                    'Price',
                    'Stock',
                    'Type',
                    'Status',
                    'Actions',
                ] as $heading)
                    <th class="px-4 py-3 text-left text-xs font-black uppercase tracking-wide text-slate-500">
                        {{ $heading }}
                    </th>
                @endforeach
            </tr>
        </thead>

        <tbody class="divide-y divide-slate-100 bg-white">
            @forelse($products as $product)
                <tr>
                    <td class="px-4 py-4">
                        <div class="font-black text-slate-900">
                            {{ $product->name }}
                        </div>

                        @if($product->category)
                            <div class="mt-1 text-xs text-slate-500">
                                {{ $product->category->name }}
                            </div>
                        @endif
                    </td>

                    <td class="px-4 py-4 text-sm text-slate-600">
                        {{ $product->sku ?: '—' }}
                    </td>

                    <td class="px-4 py-4 text-sm font-bold text-slate-900">
                        {{ $product->currency }}
                        {{ number_format((float) $product->price, 2) }}
                    </td>

                    <td class="px-4 py-4 text-sm text-slate-600">
                        {{ number_format($product->stock_quantity) }}
                    </td>

                    <td class="px-4 py-4 text-sm capitalize text-slate-600">
                        {{ $product->product_type }}
                    </td>

                    <td class="px-4 py-4">
                        <span class="inline-flex rounded-full bg-slate-100 px-2.5 py-1 text-xs font-black capitalize text-slate-700">
                            {{ $product->status }}
                        </span>
                    </td>

                    <td class="px-4 py-4">
                        <button
                            type="button"
                            class="text-sm font-black text-blue-600 hover:text-blue-700"
                        >
                            Edit
                        </button>
                    </td>
                </tr>
            @empty
                <tr>
                    <td
                        colspan="7"
                        class="px-4 py-12 text-center text-sm text-slate-500"
                    >
                        No products found.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

@if($products->hasPages())
    <div
        class="flex items-center justify-between gap-4 border-t border-slate-200 px-4 py-4"
        data-products-pagination
    >
        <div class="text-xs text-slate-500">
            Showing {{ $products->firstItem() }}–{{ $products->lastItem() }}
            of {{ $products->total() }}
        </div>

        <div class="flex gap-2">
            @if($products->onFirstPage())
                <span class="rounded-lg border border-slate-200 px-3 py-2 text-xs font-bold text-slate-300">
                    Previous
                </span>
            @else
                <a
                    href="{{ $products->previousPageUrl() }}"
                    class="rounded-lg border border-slate-300 px-3 py-2 text-xs font-bold text-slate-700 hover:bg-slate-50"
                >
                    Previous
                </a>
            @endif

            @if($products->hasMorePages())
                <a
                    href="{{ $products->nextPageUrl() }}"
                    class="rounded-lg border border-slate-300 px-3 py-2 text-xs font-bold text-slate-700 hover:bg-slate-50"
                >
                    Next
                </a>
            @else
                <span class="rounded-lg border border-slate-200 px-3 py-2 text-xs font-bold text-slate-300">
                    Next
                </span>
            @endif
        </div>
    </div>
@endif
