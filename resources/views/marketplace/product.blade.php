@extends('layouts.app')

@section('content')

<div class="min-h-screen bg-slate-50 px-6 py-8">
    <div class="mx-auto max-w-5xl">

        @if(session('success'))
            <div class="mb-6 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-700">
                {{ session('success') }}
            </div>
        @endif

        @if($errors->any())
            <div class="mb-6 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                {{ $errors->first() }}
            </div>
        @endif

        <div class="rounded-3xl border border-slate-200 bg-white p-8 shadow-sm">

            <div class="flex flex-wrap items-start justify-between gap-5">
                <div>
                    <span class="rounded-full bg-blue-50 px-3 py-1 text-[11px] font-bold uppercase text-blue-700">
                        {{ $productType === 'bundle' ? 'Bundle' : 'Addon' }}
                    </span>

                    <h1 class="mt-4 text-3xl font-black text-slate-900">
                        {{ $product->name }}
                    </h1>

                    <p class="mt-2 max-w-2xl text-sm leading-6 text-slate-500">
                        {{ $product->description ?: 'Extend your Esubiz website with additional functionality.' }}
                    </p>
                </div>
            </div>

            <div class="mt-8">
                <h2 class="text-sm font-black uppercase tracking-wider text-slate-400">
                    Included functionality
                </h2>

                <div class="mt-3 flex flex-wrap gap-2">
                    @foreach($items as $item)
                        <span class="rounded-full bg-slate-100 px-4 py-2 text-xs font-semibold text-slate-700">
                            {{ $item['name'] ?? 'Unnamed add-on' }}
                            ·
                            {{ ($item['is_unlimited'] ?? $item->is_unlimited) ? 'Unlimited' : (($item['allocation'] ?? $item->allocation) ?? 'Default') }}
                        </span>
                    @endforeach
                </div>
            </div>

            <form method="POST"
                  action="{{ route('marketplace.checkout.create') }}"
                  class="mt-10 rounded-2xl border border-slate-200 bg-slate-50 p-6">
                @csrf

                <input type="hidden" name="product_type" value="{{ $productType }}">
                <input type="hidden" name="product_id" value="{{ $product->id }}">

                <h2 class="text-lg font-black text-slate-900">
                    Checkout
                </h2>

                <div class="mt-5">
                    <label class="text-sm font-semibold text-slate-700">
                        Website
                    </label>

                    <select name="website_id"
                            required
                            class="mt-2 w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm">
                        <option value="">Select a website</option>

                        @foreach($websites as $website)
                            <option value="{{ $website->id }}">
                                {{ $website->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="mt-5">
                    <label class="text-sm font-semibold text-slate-700">
                        Deployment
                    </label>

                    <select name="deployment_type"
                            required
                            class="mt-2 w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm">
                        @if($product->saas_available)
                            <option value="saas">SaaS website — rental/subscription</option>
                        @endif

                        @if($product->off_server_available)
                            <option value="off_server">Off-server website — license</option>
                        @endif
                    </select>
                </div>

                <button type="submit"
                        class="mt-6 w-full rounded-xl bg-slate-900 px-6 py-3 text-sm font-bold text-white">
                    Continue to Payment
                </button>

                <p class="mt-3 text-center text-[11px] text-slate-400">
                    Your order will remain pending until payment is confirmed.
                </p>
            </form>

        </div>
    </div>
</div>

@endsection
