@extends('admin.layouts.app')

@section('content')
<div class="min-h-screen bg-slate-50">
    <div class="mx-auto max-w-7xl px-6 py-8">

        <div class="mb-8">
            <h1 class="text-2xl font-black text-slate-900">
                Developer Add-ons
            </h1>
            <p class="mt-2 text-sm text-slate-500">
                Purchase add-ons for your off-server websites.
            </p>
        </div>

        @if($addons->isEmpty())
            <div class="rounded-3xl border border-slate-200 bg-white p-10 text-center">
                <div class="text-lg font-bold text-slate-900">
                    No off-server add-ons available
                </div>
                <p class="mt-2 text-sm text-slate-500">
                    There are currently no add-ons enabled for off-server websites.
                </p>
            </div>
        @else
            <div class="grid gap-6 md:grid-cols-2 xl:grid-cols-3">
                @foreach($addons as $addon)
                    <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm transition-all duration-300 ease-out hover:-translate-y-1 hover:shadow-xl">

                        <h2 class="text-lg font-black text-slate-900">
                            {{ $addon->name }}
                        </h2>

                        @if($addon->description)
                            <p class="mt-2 text-sm leading-6 text-slate-500">
                                {{ $addon->description }}
                            </p>
                        @endif

                        <div class="mt-5 rounded-2xl bg-slate-50 p-4">
                            <div class="mb-3 text-[10px] font-black uppercase tracking-[0.18em] text-slate-400">
                                Included
                            </div>

                            <div class="space-y-2">
                                @forelse($addon->capability_allocations as $allocation)
                                    <div class="flex items-center justify-between gap-3 text-sm">
                                        <span class="font-semibold text-slate-800">
                                            {{ ucwords(str_replace(['_', '-'], ' ', $allocation->capability_key)) }}
                                        </span>

                                        <span class="whitespace-nowrap rounded-full bg-white px-3 py-1 text-[10px] font-black text-slate-700 ring-1 ring-slate-200">
                                            @if($allocation->is_unlimited)
                                                Unlimited
                                            @elseif($allocation->allocation !== null)
                                                {{ number_format((float) $allocation->allocation) }}
                                                {{ $addon->allocation_unit ?? '' }}
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
                        </div>

                        <div class="mt-6 flex items-center justify-between">
                            <div>
                                <div class="text-[10px] font-black uppercase tracking-wider text-slate-400">
                                    Off-server
                                </div>
                                <div class="mt-1 text-lg font-black text-slate-900">
                                    {{ $addon->off_server_currency ?? 'NGN' }}
                                    {{ number_format((float) ($addon->off_server_price ?? 0), 2) }}
                                </div>
                            </div>

                            <div x-data="{ purchasing: false, quantity: 1 }" class="flex flex-col items-end gap-3">

    <button
        type="button"
        x-show="!purchasing"
        x-transition
        @click="purchasing = true"
        style="color:#fff !important;" class="rounded-xl bg-blue-600 px-6 py-3 text-xs font-black !text-white shadow-sm transition-all duration-300 ease-out hover:-translate-y-0.5 hover:bg-blue-700 hover:shadow-md"
    >
        Purchase
    </button>

    <div
        x-show="purchasing"
        x-cloak
        x-transition
        class="w-48 rounded-2xl border border-blue-100 bg-blue-50 p-3"
    >
        <div class="text-[10px] font-black uppercase tracking-wider text-blue-700">
            Quantity
        </div>

        <input
            type="number"
            min="1"
            step="1"
            x-model.number="quantity"
            class="mt-2 w-full rounded-xl border border-blue-200 bg-white px-3 py-2 text-sm font-bold text-slate-900"
        >

        <button
            type="button"
            @click.prevent.stop="purchasing = false"
            class="mt-2 block w-full rounded-xl border border-blue-200 bg-white px-4 py-2 text-center text-xs font-black text-blue-700 transition hover:bg-blue-100"
        >
            Cancel
        </button>

        <a
            :href="'{{ route('marketplace.developer.checkout', ['productType' => 'addon', 'productId' => $addon->id]) }}?quantity=' + Math.max(1, quantity)"
            style="color:#fff !important;" class="mt-2 block w-full rounded-xl bg-blue-600 px-4 py-2 text-center text-xs font-black !text-white transition hover:bg-blue-700"
        >
            Continue
        </a>
    </div>

</div>
                        </div>

                    </div>
                @endforeach
            </div>
        @endif


    @if(isset($bundles) && $bundles->isNotEmpty())
        <div class="mt-10">
            <div class="mb-5">
                <h2 class="text-xl font-black text-slate-900">Bundles</h2>
                <p class="mt-1 text-sm text-slate-500">
                    Add-on bundles enabled for off-server websites.
                </p>
            </div>

            <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                @foreach($bundles as $bundle)
                    <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm transition-all duration-300 ease-out hover:-translate-y-1 hover:shadow-xl">
                        <h3 class="text-lg font-black text-slate-900">
                            {{ $bundle->name }}
                        </h3>

                        @if($bundle->description)
                            <p class="mt-2 text-sm leading-6 text-slate-500">
                                {{ $bundle->description }}
                            </p>
                        @endif

                        @if(isset($bundleItems) && $bundleItems->has($bundle->id))
                            <div class="mt-5 space-y-2">
                                @foreach($bundleItems->get($bundle->id) as $item)
                                    <div class="flex items-center justify-between gap-3 text-sm">
                                        <span class="font-semibold text-slate-700">
                                            {{ $item->name }}
                                        </span>

                                        <span class="whitespace-nowrap rounded-full bg-slate-50 px-3 py-1 text-[10px] font-black text-slate-600">
                                            @if($item->is_unlimited)
                                                Unlimited
                                            @elseif($item->allocation !== null && $item->allocation !== '')
                                                {{ $item->allocation }}
                                            @else
                                                Included
                                            @endif
                                        </span>
                                    </div>
                                @endforeach
                            </div>
                        @endif

                        <div class="mt-6 flex items-center justify-between">
                            <span class="text-sm font-black text-slate-900">
                                @if($bundle->off_server_price !== null)
                                    {{ $bundle->off_server_currency }} {{ number_format($bundle->off_server_price, 2) }}
                                @else
                                    Contact for price
                                @endif
                            </span>

                            <div x-data="{ purchasing: false, quantity: 1 }" class="flex flex-col items-end gap-3">

    <button
        type="button"
        @click="purchasing = !purchasing"
        style="color:#fff !important;" class="rounded-xl bg-blue-600 px-6 py-3 text-xs font-black !text-white shadow-sm transition-all duration-300 ease-out hover:-translate-y-0.5 hover:bg-blue-700 hover:shadow-md"
    >
        Purchase
    </button>

    <div
        x-show="purchasing"
        x-cloak
        x-transition
        class="w-48 rounded-2xl border border-blue-100 bg-blue-50 p-3"
    >
        <div class="text-[10px] font-black uppercase tracking-wider text-blue-700">
            Quantity
        </div>

        <input
            type="number"
            min="1"
            step="1"
            x-model.number="quantity"
            class="mt-2 w-full rounded-xl border border-blue-200 bg-white px-3 py-2 text-sm font-bold text-slate-900"
        >

        <button
            type="button"
            @click.prevent.stop="purchasing = false"
            class="mt-2 block w-full rounded-xl border border-blue-200 bg-white px-4 py-2 text-center text-xs font-black text-blue-700 transition hover:bg-blue-100"
        >
            Cancel
        </button>

        <a
            :href="'{{ route('marketplace.developer.checkout', ['productType' => 'bundle', 'productId' => $bundle->id]) }}?quantity=' + Math.max(1, quantity)"
            style="color:#fff !important;" class="mt-2 block w-full rounded-xl bg-blue-600 px-4 py-2 text-center text-xs font-black !text-white transition hover:bg-blue-700"
        >
            Continue
        </a>
    </div>

</div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    </div>
</div>
@endsection
