<div class="overflow-x-auto">
    <table class="min-w-full divide-y divide-slate-200">
        <thead class="bg-slate-50">
            <tr>
                <th class="px-5 py-3 text-left text-xs font-semibold uppercase text-slate-500">
                    Category
                </th>
                <th class="px-5 py-3 text-left text-xs font-semibold uppercase text-slate-500">
                    Product Types
                </th>
                <th class="px-5 py-3 text-left text-xs font-semibold uppercase text-slate-500">
                    Products
                </th>
                <th class="px-5 py-3 text-left text-xs font-semibold uppercase text-slate-500">
                    Status
                </th>
                <th class="px-5 py-3 text-left text-xs font-semibold uppercase text-slate-500">
                    Order
                </th>
                <th class="px-5 py-3 text-right text-xs font-semibold uppercase text-slate-500">
                    Actions
                </th>
            </tr>
        </thead>

        <tbody class="divide-y divide-slate-100 bg-white">
            @forelse($categories as $category)
                <tr>
                    <td class="px-5 py-4">
                        <div class="font-medium text-slate-900">
                            {{ $category->name }}
                        </div>
                        <div class="text-xs text-slate-500">
                            {{ $category->slug }}
                        </div>
                    </td>

                    <td class="px-5 py-4 text-sm text-slate-600">
                        @if(empty($category->product_types))
                            All Product Types
                        @else
                            {{ collect($category->product_types)
                                ->map(fn ($type) => ucwords(str_replace('_', ' ', $type)))
                                ->join(', ') }}
                        @endif
                    </td>

                    <td class="px-5 py-4 text-sm text-slate-600">
                        {{ $category->catalog_products_count }}
                    </td>

                    <td class="px-5 py-4">
                        <span class="rounded-full px-2.5 py-1 text-xs font-medium
                            {{ $category->is_active
                                ? 'bg-green-100 text-green-700'
                                : 'bg-slate-100 text-slate-600' }}">
                            {{ $category->is_active ? 'Active' : 'Inactive' }}
                        </span>
                    </td>

                    <td class="px-5 py-4 text-sm text-slate-600">
                        {{ $category->sort_order }}
                    </td>

                    <td class="px-5 py-4 text-right">
                        <form
                            method="POST"
                            action="{{ route(
                                'admin.marketplace.categories.destroy',
                                $category
                            ) }}"
                            class="inline"
                            onsubmit="return confirm('Delete this Marketplace category?');"
                        >
                            @csrf
                            @method('DELETE')

                            <button
                                type="submit"
                                class="text-sm font-medium text-red-600 hover:text-red-700"
                            >
                                Delete
                            </button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr>
                    <td
                        colspan="6"
                        class="px-5 py-10 text-center text-sm text-slate-500"
                    >
                        No Marketplace categories have been created yet.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

@if($categories->hasPages())
    <div class="flex items-center justify-between border-t border-slate-200 px-5 py-4">
        <div class="text-sm text-slate-500">
            Showing {{ $categories->firstItem() }}
            – {{ $categories->lastItem() }}
            of {{ $categories->total() }}
        </div>

        <div class="flex gap-2">
            @if($categories->previousPageUrl())
                <a
                    href="{{ $categories->previousPageUrl() }}"
                    data-category-page
                    class="rounded-lg border border-slate-300 px-4 py-2 text-sm hover:bg-slate-50"
                >
                    Previous
                </a>
            @endif

            @if($categories->nextPageUrl())
                <a
                    href="{{ $categories->nextPageUrl() }}"
                    data-category-page
                    class="rounded-lg border border-slate-300 px-4 py-2 text-sm hover:bg-slate-50"
                >
                    Next
                </a>
            @endif
        </div>
    </div>
@endif
