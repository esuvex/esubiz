@extends('admin.layouts.app')

@section('content')

<div class="max-w-3xl">

    <div class="rounded-3xl bg-white border border-slate-200 shadow-sm p-6">

        <div class="mb-6">
            <h2 class="text-xl font-bold text-slate-900">
                Edit Credit Product
            </h2>

            <p class="mt-1 text-sm text-slate-500">
                Update the catalogue credit product.
            </p>
        </div>

        <form
            method="POST"
            action="{{ route('admin.credit-packages.update-product', $product->id) }}"
            class="space-y-4"
        >

            @csrf

            <select
                name="credit_type"
                required
                class="w-full rounded-xl border-slate-300"
            >
                @foreach($types as $type)
                    <option
                        value="{{ $type }}"
                        @selected($product->product_type === $type)
                    >
                        {{ ucwords(str_replace('_', ' ', $type)) }}
                    </option>
                @endforeach
            </select>

            <input
                name="name"
                value="{{ $product->name }}"
                required
                class="w-full rounded-xl border-slate-300"
                placeholder="Product name"
            >

            <input
                name="credit_quantity"
                type="number"
                min="1"
                value="{{ $product->credit_quantity }}"
                required
                class="w-full rounded-xl border-slate-300"
                placeholder="Credit quantity"
            >

            <div class="flex gap-3">

                <a
                    href="{{ route('admin.credit-packages.index') }}"
                    class="flex-1 rounded-xl border border-slate-300 px-5 py-3 text-center font-semibold"
                >
                    Cancel
                </a>

                <button
                    type="submit"
                    class="flex-1 rounded-xl bg-blue-600 px-5 py-3 font-semibold text-white"
                >
                    Update Credit Product
                </button>

            </div>

        </form>

    </div>

</div>

@endsection
