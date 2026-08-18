@extends('admin.layouts.app')

@section('content')
<div class="max-w-3xl mx-auto">
    <div class="rounded-3xl bg-white border border-slate-200 shadow-sm p-6">
        <h1 class="text-xl font-bold text-slate-900">Edit Credit Package</h1>
        <p class="mt-1 mb-6 text-sm text-slate-500">
            Edit the customer-facing package without changing the underlying credit product.
        </p>

        <form method="POST" action="{{ route('admin.credit-packages.update', $package->id) }}" class="space-y-4">
            @csrf

            <select name="catalog_product_id" required class="w-full rounded-xl border-slate-300">
                @foreach($catalogProducts as $product)
                    <option value="{{ $product->id }}" @selected($package->catalog_product_id == $product->id)>
                        #{{ $product->id }} — {{ $product->name }}
                    </option>
                @endforeach
            </select>

            <select name="credit_type" required class="w-full rounded-xl border-slate-300">
                @foreach($types as $type)
                    <option value="{{ $type }}" @selected($package->credit_type === $type)>
                        {{ ucwords(str_replace('_', ' ', $type)) }}
                    </option>
                @endforeach
            </select>

            <input name="name" value="{{ $package->name }}" required
                class="w-full rounded-xl border-slate-300" placeholder="Package name">

            <input name="credit_quantity" type="number" min="1"
                value="{{ $package->credit_quantity }}" required
                class="w-full rounded-xl border-slate-300" placeholder="Credits allocated">

            <input name="price" type="number" step="0.01" min="0"
                value="{{ $package->price }}" required
                class="w-full rounded-xl border-slate-300" placeholder="Package price">

            <input name="currency" value="{{ $package->currency }}" required
                class="w-full rounded-xl border-slate-300">

            <input name="expiry_days" type="number" min="1"
                value="{{ $package->expiry_days }}"
                class="w-full rounded-xl border-slate-300" placeholder="Expiry days">

            <textarea name="description"
                class="w-full rounded-xl border-slate-300"
                placeholder="Description">{{ $package->description }}</textarea>

            <input name="sort_order" type="number" min="0"
                value="{{ $package->sort_order }}"
                class="w-full rounded-xl border-slate-300">

            <label class="flex items-center gap-2 text-sm">
                <input type="checkbox" name="is_active" value="1" @checked($package->is_active)>
                Active
            </label>

            <button class="w-full rounded-xl bg-blue-600 px-5 py-3 font-semibold text-white">
                Update Credit Package
            </button>
        </form>
    </div>
</div>
@endsection
