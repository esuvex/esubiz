@extends('admin.layouts.app')

@section('content')

<div style="display:grid;grid-template-columns:minmax(0,1fr) minmax(0,1fr);gap:1.5rem;align-items:start;">

    {{-- LEFT COLUMN: CREATE PRODUCT + CONFIGURE PACKAGE --}}
    <div class="space-y-6">

        {{-- CREATE CREDIT PRODUCT --}}
        <div class="rounded-3xl bg-white border border-slate-200 shadow-sm p-6">

            <div class="mb-5">
                <h3 class="text-lg font-bold text-slate-900">
                    Create Credit Product
                </h3>
                <p class="mt-1 text-sm text-slate-500">
                    Create the catalogue product that represents the credit inventory.
                </p>
            </div>

            <form method="POST"
                  action="{{ route('admin.credit-packages.create-product') }}"
                  class="space-y-4">

                @csrf

                <select name="credit_type"
                        required
                        class="w-full rounded-xl border-slate-300">
                    <option value="">Select credit type</option>

                    @foreach($types as $type)
                        <option value="{{ $type }}">
                            {{ ucwords(str_replace('_', ' ', $type)) }}
                        </option>
                    @endforeach
                </select>

                <input
                    name="name"
                    required
                    placeholder="Product name, e.g. 10,000 SMS Credits"
                    class="w-full rounded-xl border-slate-300"
                >

                <input
                    name="credit_quantity"
                    type="number"
                    min="1"
                    required
                    placeholder="Credit quantity"
                    class="w-full rounded-xl border-slate-300"
                >

                <button
                    type="submit"
                    class="w-full rounded-xl bg-slate-900 px-4 py-3 font-semibold text-white"
                >
                    Create Credit Product
                </button>

            </form>

        </div>


        {{-- CONFIGURE CREDIT PACKAGE --}}
        <div class="rounded-3xl bg-white border border-slate-200 shadow-sm p-6">

            <div class="mb-5">
                <h3 class="text-lg font-bold text-slate-900">
                    Configure Credit Package
                </h3>
                <p class="mt-1 text-sm text-slate-500">
                    Set the price, allocation and availability of a credit package.
                </p>
            </div>

            <form method="POST"
                  action="{{ route('admin.credit-packages.store') }}"
                  class="space-y-4">

                @csrf

                <select
                    name="catalog_product_id"
                    required
                    class="w-full rounded-xl border-slate-300"
                >
                    <option value="">Select catalogue product</option>

                    @foreach($catalogProducts as $product)
                        <option value="{{ $product->id }}">
                            #{{ $product->id }} — {{ $product->name }}

                            @if($product->credit_quantity)
                                ({{ number_format($product->credit_quantity) }} credits)
                            @endif
                        </option>
                    @endforeach
                </select>

                <select
                    name="credit_type"
                    required
                    class="w-full rounded-xl border-slate-300"
                >
                    <option value="">Credit type</option>

                    @foreach($types as $type)
                        <option value="{{ $type }}">
                            {{ ucwords(str_replace('_', ' ', $type)) }}
                        </option>
                    @endforeach
                </select>

                <input
                    name="name"
                    required
                    placeholder="Package name"
                    class="w-full rounded-xl border-slate-300"
                >

                <input
                    name="credit_quantity"
                    type="number"
                    min="1"
                    required
                    placeholder="Credits allocated"
                    class="w-full rounded-xl border-slate-300"
                >

                <input
                    name="price"
                    type="number"
                    step="0.01"
                    min="0"
                    required
                    placeholder="Package price"
                    class="w-full rounded-xl border-slate-300"
                >

                <input
                    name="currency"
                    value="NGN"
                    required
                    class="w-full rounded-xl border-slate-300"
                >

                <input
                    name="expiry_days"
                    type="number"
                    min="1"
                    placeholder="Expiry days (optional)"
                    class="w-full rounded-xl border-slate-300"
                >

                <textarea
                    name="description"
                    placeholder="Description"
                    class="w-full rounded-xl border-slate-300"
                ></textarea>

                <input
                    name="sort_order"
                    type="number"
                    min="0"
                    value="0"
                    class="w-full rounded-xl border-slate-300"
                >

                <label class="flex items-center gap-2 text-sm text-slate-700">
                    <input
                        type="checkbox"
                        name="is_active"
                        value="1"
                        checked
                    >
                    Active
                </label>

                <button
                    type="submit"
                    class="w-full rounded-xl bg-blue-600 px-5 py-3 font-semibold text-white"
                >
                    Save Credit Package
                </button>

            </form>

        </div>

    </div>


    {{-- RIGHT COLUMN: CONFIGURED PACKAGES --}}
    <div class="rounded-3xl bg-white border border-slate-200 shadow-sm overflow-hidden">

        <div class="p-6 border-b border-slate-100">
            <h2 class="text-lg font-bold text-slate-900">
                Configured Packages
            </h2>

            <p class="mt-1 text-sm text-slate-500">
                Credit packages currently available for purchase.
            </p>
        </div>

        <div class="overflow-x-auto">

            <table class="w-full text-sm">

                <thead class="bg-slate-50">
                    <tr>
                        <th class="px-5 py-4 text-left">Package</th>
                        <th class="px-5 py-4 text-left">Type</th>
                        <th class="px-5 py-4 text-right">Credits</th>
                        <th class="px-5 py-4 text-right">Price</th>
                        <th class="px-5 py-4 text-center">Status</th>
                        <th class="px-5 py-4 text-right">Action</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-slate-100">

                    @forelse($packages as $package)

                        <tr>

                            <td class="px-5 py-4">
                                <div class="font-semibold text-slate-900">
                                    {{ $package->name }}
                                </div>

                                <div class="text-xs text-slate-500">
                                    Product #{{ $package->catalog_product_id }}
                                </div>
                            </td>

                            <td class="px-5 py-4">
                                {{ ucwords(str_replace('_', ' ', $package->credit_type)) }}
                            </td>

                            <td class="px-5 py-4 text-right font-semibold">
                                {{ number_format($package->credit_quantity) }}
                            </td>

                            <td class="px-5 py-4 text-right whitespace-nowrap">
                                {{ $package->currency }}
                                {{ number_format($package->price, 2) }}
                            </td>

                            <td class="px-5 py-4 text-center">
                                <span class="rounded-full bg-slate-100 px-3 py-1 text-xs">
                                    {{ $package->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>

                            <td class="px-5 py-4 text-right">

                                <form
                                    method="POST"
                                    action="{{ route('admin.credit-packages.toggle', $package->id) }}"
                                >

                                    @csrf

                                    <button
                                        type="submit"
                                        class="text-blue-600 font-semibold"
                                    >
                                        {{ $package->is_active ? 'Disable' : 'Enable' }}
                                    </button>

                                </form>

                            </td>

                        </tr>

                    @empty

                        <tr>
                            <td
                                colspan="6"
                                class="px-5 py-10 text-center text-slate-500"
                            >
                                No credit packages configured.
                            </td>
                        </tr>

                    @endforelse

                </tbody>

            </table>

        </div>

    </div>

</div>

@endsection
