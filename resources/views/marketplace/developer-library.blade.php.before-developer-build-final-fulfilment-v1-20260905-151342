@extends('admin.layouts.app')

<style>
@keyframes developerLibraryCardIn {
    from {
        opacity: 0;
        transform: translateY(14px) scale(.985);
    }
    to {
        opacity: 1;
        transform: translateY(0) scale(1);
    }
}

.developer-library-card {
    animation: developerLibraryCardIn .55s ease-out both;
    transition: transform .25s ease, box-shadow .25s ease;
}

.developer-library-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 18px 40px rgba(15, 23, 42, .08);
}
</style>



@section('content')
<div class="min-h-screen bg-slate-50 px-4 py-8 sm:px-6 lg:px-8">
    <div class="mx-auto max-w-7xl">

        <div class="mb-8">
            <p class="text-xs font-black uppercase tracking-widest text-blue-600">
                Developer Marketplace
            </p>

            <h1 class="mt-2 text-3xl font-black text-slate-900">
                My Library
            </h1>

            <p class="mt-2 max-w-2xl text-sm leading-6 text-slate-500">
                Access the marketplace products you have purchased for
                development and off-server use.
            </p>
        </div>

        @if($orders->isEmpty())

            <div class="rounded-3xl border border-slate-200 bg-white px-6 py-16 text-center shadow-sm">
                <h2 class="text-lg font-black text-slate-900">
                    Your library is empty
                </h2>

                <p class="mx-auto mt-2 max-w-md text-sm text-slate-500">
                    Purchased website types, bundles, addons, themes and
                    modules will appear here.
                </p>

                <a
                    href="{{ route('developer.marketplace') }}"
                    class="mt-6 inline-flex rounded-xl bg-blue-600 px-6 py-3 text-sm font-black text-white hover:bg-blue-700"
                >
                    Browse Marketplace
                </a>
            </div>

        @else

            <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3">

                @foreach($orders as $order)

                    @php
                        $typeLabels = [
                            'website_type' => 'Website Type',
                            'core_addon' => 'Addon',
                            'core_bundle' => 'Addon Bundle',
                            'theme' => 'Theme',
                            'module' => 'Module',
                        ];

                        $typeLabel = $typeLabels[$order->product_type]
                            ?? ucwords(str_replace(['_', '-'], ' ', $order->product_type));
                    @endphp

                    <div class="developer-library-card flex flex-col rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">

                        <div class="flex items-start justify-between gap-4">

                            <div>
                                <span class="inline-flex rounded-full bg-blue-50 px-3 py-1 text-[10px] font-black uppercase tracking-wide text-blue-700">
                                    {{ $typeLabel }}
                                </span>

                                <h2 class="mt-4 text-lg font-black text-slate-900">
                                    {{ $order->product_title }}
                                </h2>
                            </div>

                            <span class="shrink-0 rounded-full bg-emerald-50 px-3 py-1 text-[10px] font-black uppercase text-emerald-700">
                                Purchased
                            </span>

                        </div>

                        <div class="mt-5 space-y-2 text-xs text-slate-500">

                            <div class="flex justify-between gap-4">
                                <span>Order</span>
                                <span class="font-bold text-slate-700">
                                    {{ $order->reference }}
                                </span>
                            </div>

                            <div class="flex justify-between gap-4">
                                <span>Amount</span>
                                <span class="font-bold text-slate-700">
                                    {{ $order->currency }}
                                    {{ number_format((float) $order->amount, 2) }}
                                </span>
                            </div>

                            <div class="flex justify-between gap-4">
                                <span>Status</span>
                                <span class="font-bold text-emerald-600">
                                    {{ ucfirst($order->status) }}
                                </span>
                            </div>

                        </div>

                        <div class="mt-6 pt-4 border-t border-slate-100">

                            <button
                                type="button"
                                disabled
                                class="w-full cursor-not-allowed rounded-xl bg-slate-100 px-5 py-3 text-sm font-black text-slate-400"
                            >
                                Download / Access
                            </button>

                            <p class="mt-2 text-center text-[10px] font-semibold text-slate-400">
                                Product access will be provided from the registered product package.
                            </p>

                        </div>

                    </div>

                @endforeach

            </div>

        @endif

    </div>
</div>
@endsection
