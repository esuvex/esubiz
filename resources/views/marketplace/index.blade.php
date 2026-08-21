@extends('admin.layouts.app')

@section('content')
<div class="min-h-screen bg-slate-50 px-4 py-8 sm:px-6 lg:px-8">
    <div class="mx-auto max-w-7xl">

        {{-- Header --}}
        <div class="mb-10">
            <div class="text-xs font-black uppercase tracking-[0.25em] text-blue-600">
                ESUBIZ MARKETPLACE
            </div>

            <h1 class="mt-2 text-3xl font-black tracking-tight text-slate-950 sm:text-4xl">
                Extend your website
            </h1>

            <p class="mt-2 max-w-3xl text-sm leading-6 text-slate-500">
                Add the exact capabilities you need, or choose a bundle for a complete package.
            </p>
        </div>


        {{-- ============================================================
             INDIVIDUAL ADD-ONS
        ============================================================= --}}
        <section>
            <div class="mb-6">
                <h2 class="text-2xl font-black text-slate-950">
                    Add-ons
                </h2>

                <p class="mt-1 text-sm text-slate-500">
                    Add individual capabilities to your website.
                </p>
            </div>

            <div class="grid grid-cols-1 gap-5 md:grid-cols-2 lg:grid-cols-4">

                @forelse($addons as $addon)

                    @php
                        $rawFeatures = $addon->capability_list ?? [];
                        $decodedFeatures = is_string($rawFeatures)
                            ? json_decode($rawFeatures, true)
                            : $rawFeatures;
                        $features = collect(is_array($decodedFeatures) ? $decodedFeatures : []);
                        $visibleFeatures = $features->take(5);
                        $remainingFeatures = $features->slice(5);
                    @endphp

                    <div
                        x-data="{ expanded: false, checkout: false }"
                        class="flex min-h-[430px] flex-col overflow-hidden rounded-[26px] border border-slate-200 bg-white shadow-sm transition duration-300 hover:-translate-y-1 hover:shadow-xl"
                    >

                        {{-- Card body --}}
                        <div class="flex-1 p-5">

                            <div class="flex items-start justify-between gap-3">
                                <span class="rounded-full bg-blue-50 px-3 py-1 text-[10px] font-black uppercase tracking-wider text-blue-700">
                                    Add-on
                                </span>
                            </div>

                            <h3 class="mt-4 text-xl font-black text-slate-950">
                                {{ $addon->name }}
                            </h3>

                            @if($addon->description)
                                <p class="mt-2 min-h-[40px] text-xs leading-5 text-slate-500">
                                    {{ $addon->description }}
                                </p>
                            @endif

                            {{-- Allocated features --}}
                            @php
                                $allocations = collect($addon->capability_allocations ?? []);
                            @endphp

                            <div
                                x-data="{ expanded: false }"
                                class="mt-4 rounded-2xl border border-slate-100 bg-white px-4 py-3"
                            >
                                <div class="text-[10px] font-black uppercase tracking-[0.18em] text-slate-400">
                                    Included features
                                </div>

                                <div class="mt-3 space-y-2">
                                    @forelse($allocations as $index => $allocation)
                                        <div
                                            x-show="expanded || {{ $index }} < 5"
                                            class="flex items-center justify-between gap-3 text-sm"
                                        >
                                            <span class="font-semibold text-slate-800">
                                                {{ $allocation->capability_name
                                                    ?? $allocation->name
                                                    ?? $allocation->capability_key
                                                    ?? $allocation->key
                                                    ?? 'Feature' }}
                                            </span>

                                            <span class="whitespace-nowrap rounded-full bg-blue-50 px-3 py-1 text-[10px] font-black text-blue-700">
                                                @if((bool) ($allocation->is_unlimited ?? false))
                                                    Unlimited{{ !empty($addon->allocation_unit) ? ' ' . $addon->allocation_unit : '' }}
                                                @elseif($allocation->allocation !== null && $allocation->allocation !== '')
                                                    {{ number_format((float) $allocation->allocation) }}{{ !empty($addon->allocation_unit) ? ' ' . $addon->allocation_unit : '' }}
                                                @else
                                                    Not configured
                                                @endif
                                            </span>
                                        </div>
                                    @empty
                                        <div class="text-sm text-slate-400">
                                            No allocation details available.
                                        </div>
                                    @endforelse
                                </div>

                                @if($allocations->count() > 5)
                                    <button
                                        type="button"
                                        @click="expanded = !expanded"
                                        class="mt-3 text-xs font-bold text-blue-600"
                                    >
                                        <span x-show="!expanded">See more</span>
                                        <span x-show="expanded">See less</span>
                                    </button>
                                @endif
                            </div>

                            
                        </div>


                        {{-- Pricing / Buy --}}
                        <div class="border-t border-slate-100 p-5">

                            <div class="mb-4 flex items-end justify-between gap-2">

                                <div>
                                    <div class="text-[10px] font-bold uppercase tracking-wider text-slate-400">
                                        SaaS
                                    </div>

                                    <div class="mt-1 text-lg font-black text-slate-950">
                                        {{ $addon->saas_currency ?? 'NGN' }}
                                        {{ number_format((float)($addon->saas_price ?? 0), 2) }}
                                    </div>
                                </div>

                                <div class="pb-1 text-right text-[10px] text-slate-400">
                                    / {{ $addon->saas_billing_period ?? 12 }}
                                    {{ $addon->saas_billing_interval ?? 'month' }}
                                </div>

                            </div>

                            <button
                                type="button"
                                @click="checkout = true"
                                class="w-full rounded-2xl bg-blue-600 px-4 py-3 text-sm font-black text-white shadow-lg shadow-blue-600/20 transition hover:bg-blue-700"
                            >
                                Buy Add-on
                            </button>


                            {{-- Inline checkout --}}
                            <div
                                x-show="checkout"
                                x-cloak
                                x-transition
                                class="mt-4 rounded-2xl border border-blue-100 bg-blue-50 p-4"
                            >

                                <div class="mb-3">
                                    <div class="text-sm font-black text-slate-950">
                                        {{ $addon->name }}
                                    </div>

                                    <div class="mt-1 text-xs text-slate-500">
                                        Select the website where this add-on should be deployed.
                                    </div>
                                </div>

                                <form method="POST" action="{{ route('marketplace.checkout.create') }}">
                                    @csrf

                                    <input type="hidden" name="product_type" value="addon">
                                    <input type="hidden" name="product_id" value="{{ $addon->id }}">

                                    <select
                                        name="website_id"
                                        required
                                        class="w-full rounded-xl border-slate-200 bg-white text-sm focus:border-blue-500 focus:ring-blue-500"
                                    >
                                        <option value="">Select website</option>

                                        @foreach(
                                            \App\Models\Website::query()
                                                ->where('owner_id', auth()->id())
                                                ->orderBy('name')
                                                ->get()
                                            as $website
                                        )
                                            <option value="{{ $website->id }}">
                                                {{ $website->name }}
                                            </option>
                                        @endforeach
                                    </select>

                                    <input type="hidden" name="deployment_type" value="saas">

                                    <div class="mt-3 flex gap-2">
                                        <button
                                            type="submit"
                                            class="flex-1 rounded-xl bg-blue-600 px-3 py-2.5 text-xs font-black text-white hover:bg-blue-700"
                                        >
                                            Continue
                                        </button>

                                        <button
                                            type="button"
                                            @click="checkout = false"
                                            class="rounded-xl bg-white px-3 py-2.5 text-xs font-bold text-slate-600 ring-1 ring-slate-200"
                                        >
                                            Cancel
                                        </button>
                                    </div>
                                </form>

                            </div>

                        </div>
                    </div>

                @empty

                    <div class="col-span-full rounded-3xl border border-dashed border-slate-300 bg-white p-12 text-center">
                        <div class="text-sm font-bold text-slate-600">
                            No add-ons are currently available.
                        </div>
                    </div>

                @endforelse

            </div>
        </section>


        {{-- ============================================================
             BUNDLES
        ============================================================= --}}
        <section class="mt-16">

            <div class="mb-6">
                <h2 class="text-2xl font-black text-slate-950">
                    Addon Bundles
                </h2>

                <p class="mt-1 text-sm text-slate-500">
                    Get multiple capabilities together in one package.
                </p>
            </div>


            <div class="grid grid-cols-1 gap-5 md:grid-cols-2 lg:grid-cols-4">

                @forelse($bundles as $bundle)

                    @php
                        $items = collect($bundleItems->get($bundle->id, []));
                    @endphp

                    <div
                        x-data="{ checkout: false }"
                        class="flex min-h-[470px] flex-col overflow-hidden rounded-[26px] border border-slate-200 bg-white shadow-sm transition duration-300 hover:-translate-y-1 hover:shadow-xl"
                    >

                        <div class="flex-1 p-5">

                            <div class="flex items-start justify-between gap-3">

                                <span class="rounded-full bg-amber-50 px-3 py-1 text-[10px] font-black uppercase tracking-wider text-amber-700">
                                    Bundle
                                </span>

                                <div class="text-right">
                                    <div class="text-lg font-black text-slate-950">
                                        {{ $bundle->saas_currency ?? 'NGN' }}
                                        {{ number_format((float)($bundle->saas_price ?? 0), 2) }}
                                    </div>

                                    <div class="text-[10px] text-slate-400">
                                        / {{ $bundle->saas_billing_period ?? 12 }}
                                        {{ $bundle->saas_billing_interval ?? 'month' }}
                                    </div>
                                </div>

                            </div>

                            <h3 class="mt-4 text-xl font-black text-slate-950">
                                {{ $bundle->name }}
                            </h3>

                            @if($bundle->description)
                                <p class="mt-2 text-xs leading-5 text-slate-500">
                                    {{ $bundle->description }}
                                </p>
                            @endif


                            {{-- Full bundle contents --}}
                            <div class="mt-5 rounded-2xl bg-slate-50 p-4">

                                <div class="mb-4 text-[10px] font-black uppercase tracking-[0.18em] text-slate-400">
                                    Everything included
                                </div>

                                <div class="space-y-4">

                                    @forelse($items as $item)

                                        <div class="border-b border-slate-200 pb-3 last:border-0 last:pb-0">

                                            <div class="flex items-start justify-between gap-2">

                                                <div class="flex items-start gap-2">
                                                    <span class="font-black text-amber-600">
                                                        ✓
                                                    </span>

                                                    <span class="text-xs font-black text-slate-800">
                                                        {{ $item->name }}
                                                    </span>
                                                </div>

                                                <span class="shrink-0 rounded-full bg-white px-2 py-1 text-[9px] font-bold text-slate-500 ring-1 ring-slate-200">
                                                    @if($item->is_unlimited)
                                                        Unlimited
                                                    @elseif($item->allocation !== null && $item->allocation !== '')
                                                        {{ $item->allocation }}
                                                    @else
                                                        Included
                                                    @endif
                                                </span>

                                            </div>


                                            @if(!empty($item->capability_list))

                                                <div class="mt-2 ml-5 space-y-1">

                                                    @foreach($item->capability_list as $feature)

                                                        <div class="text-[10px] leading-4 text-slate-500">
                                                            • {{ ucwords(str_replace(['_', '-'], ' ', $feature)) }}
                                                        </div>

                                                    @endforeach

                                                </div>

                                            @endif

                                        </div>

                                    @empty

                                        <div class="text-xs text-slate-400">
                                            No bundle contents available.
                                        </div>

                                    @endforelse

                                </div>
                            </div>

                        </div>


                        {{-- Bundle purchase --}}
                        <div class="border-t border-slate-100 p-5">

                            <button
                                type="button"
                                @click="checkout = true"
                                class="w-full rounded-2xl bg-slate-950 px-4 py-3 text-sm font-black text-white shadow-lg shadow-slate-950/20 transition hover:bg-slate-800"
                            >
                                Buy Bundle
                            </button>


                            {{-- Inline checkout --}}
                            <div
                                x-show="checkout"
                                x-cloak
                                x-transition
                                class="mt-4 rounded-2xl border border-slate-200 bg-slate-50 p-4"
                            >

                                <div class="mb-3">
                                    <div class="text-sm font-black text-slate-950">
                                        {{ $bundle->name }}
                                    </div>

                                    <div class="mt-1 text-xs text-slate-500">
                                        Select the website where this bundle should be deployed.
                                    </div>
                                </div>

                                <form method="POST" action="{{ route('marketplace.checkout.create') }}">
                                    @csrf

                                    <input type="hidden" name="product_type" value="bundle">
                                    <input type="hidden" name="product_id" value="{{ $bundle->id }}">
                                    <input type="hidden" name="deployment_type" value="saas">

                                    <select
                                        name="website_id"
                                        required
                                        class="w-full rounded-xl border-slate-200 bg-white text-sm focus:border-slate-500 focus:ring-slate-500"
                                    >
                                        <option value="">Select website</option>

                                        @foreach(
                                            \App\Models\Website::query()
                                                ->where('owner_id', auth()->id())
                                                ->orderBy('name')
                                                ->get()
                                            as $website
                                        )
                                            <option value="{{ $website->id }}">
                                                {{ $website->name }}
                                            </option>
                                        @endforeach
                                    </select>

                                    <div class="mt-3 flex gap-2">
                                        <button
                                            type="submit"
                                            class="flex-1 rounded-xl bg-slate-950 px-3 py-2.5 text-xs font-black text-white hover:bg-slate-800"
                                        >
                                            Continue
                                        </button>

                                        <button
                                            type="button"
                                            @click="checkout = false"
                                            class="rounded-xl bg-white px-3 py-2.5 text-xs font-bold text-slate-600 ring-1 ring-slate-200"
                                        >
                                            Cancel
                                        </button>
                                    </div>
                                </form>

                            </div>

                        </div>

                    </div>

                @empty

                    <div class="col-span-full rounded-3xl border border-dashed border-slate-300 bg-white p-12 text-center">
                        <div class="text-sm font-bold text-slate-600">
                            No bundles are currently available.
                        </div>
                    </div>

                @endforelse

            </div>
        </section>

    </div>
</div>
@endsection
