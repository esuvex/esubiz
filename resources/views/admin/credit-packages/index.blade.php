@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto px-6 py-8">

    <div class="mb-8">
        <h1 class="text-2xl font-bold text-slate-900">Credit Packages</h1>
        <p class="mt-1 text-sm text-slate-500">
            Configure the price and exact credit allocation for Esubiz credit products.
        </p>
    </div>

    @if(session('success'))
        <div class="mb-6 rounded-xl bg-emerald-50 px-4 py-3 text-emerald-700">
            {{ session('success') }}
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <div class="lg:col-span-1 rounded-3xl bg-white border border-slate-200 p-6 shadow-sm">
            <h2 class="text-lg font-bold mb-5">Add / Update Package</h2>

            <form method="POST" action="{{ route('admin.credit-packages.store') }}" class="space-y-4">
                @csrf

                <input name="catalog_product_id" type="number" required
                    placeholder="Catalog Product ID"
                    class="w-full rounded-xl border-slate-300">

                <select name="credit_type" required class="w-full rounded-xl border-slate-300">
                    <option value="">Credit type</option>
                    @foreach($types as $type)
                        <option value="{{ $type }}">
                            {{ ucwords(str_replace('_', ' ', $type)) }}
                        </option>
                    @endforeach
                </select>

                <input name="name" required placeholder="Package name"
                    class="w-full rounded-xl border-slate-300">

                <input name="credit_quantity" type="number" min="1" required
                    placeholder="Credits allocated"
                    class="w-full rounded-xl border-slate-300">

                <input name="price" type="number" step="0.01" min="0" required
                    placeholder="Package price"
                    class="w-full rounded-xl border-slate-300">

                <input name="currency" value="NGN" required
                    class="w-full rounded-xl border-slate-300">

                <input name="expiry_days" type="number" min="1"
                    placeholder="Expiry days (optional)"
                    class="w-full rounded-xl border-slate-300">

                <textarea name="description" placeholder="Description"
                    class="w-full rounded-xl border-slate-300"></textarea>

                <input name="sort_order" type="number" min="0" value="0"
                    class="w-full rounded-xl border-slate-300">

                <label class="flex items-center gap-2 text-sm">
                    <input type="checkbox" name="is_active" value="1" checked>
                    Active
                </label>

                <button class="w-full rounded-xl bg-blue-600 px-5 py-3 font-semibold text-white">
                    Save Credit Package
                </button>
            </form>
        </div>

        <div class="lg:col-span-2 rounded-3xl bg-white border border-slate-200 shadow-sm overflow-hidden">
            <div class="p-6 border-b border-slate-100">
                <h2 class="text-lg font-bold">Configured Packages</h2>
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
                                    <div class="font-semibold">{{ $package->name }}</div>
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

                                <td class="px-5 py-4 text-right">
                                    {{ $package->currency }}
                                    {{ number_format($package->price, 2) }}
                                </td>

                                <td class="px-5 py-4 text-center">
                                    {{ $package->is_active ? 'Active' : 'Inactive' }}
                                </td>

                                <td class="px-5 py-4 text-right">
                                    <form method="POST"
                                        action="{{ route('admin.credit-packages.toggle', $package->id) }}">
                                        @csrf
                                        <button class="text-blue-600 font-semibold">
                                            {{ $package->is_active ? 'Disable' : 'Enable' }}
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-5 py-10 text-center text-slate-500">
                                    No credit packages configured.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</div>
@endsection
