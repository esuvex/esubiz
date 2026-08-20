@extends('admin.layouts.app')

@section('content')
<div class="p-6">
    <div class="mb-6">
        <h1 class="text-3xl font-bold text-slate-900">Pending Checkouts</h1>
        <p class="mt-2 text-slate-500">Continue any marketplace checkout you have not completed.</p>
    </div>

    @if($orders->count())
        <div class="space-y-4">
            @foreach($orders as $order)
                <div class="rounded-2xl bg-white p-6 shadow-sm flex items-center justify-between">
                    <div>
                        <div class="text-sm text-slate-500">
                            {{ ucwords(str_replace(['_', '-'], ' ', $order->product_type)) }}
                        </div>
                        <h2 class="mt-1 text-lg font-semibold text-slate-900">
                            {{ $order->listing_title }}
                        </h2>
                        <div class="mt-1 text-sm text-slate-400">
                            Order #{{ $order->id }}
                        </div>
                    </div>

                    <a href="{{ route('marketplace.checkout', $order->id) }}"
                       class="rounded-xl bg-blue-600 px-5 py-3 font-medium text-white hover:bg-blue-700">
                        Continue Checkout
                    </a>
                </div>
            @endforeach
        </div>
    @else
        <div class="rounded-2xl bg-white p-10 text-center shadow-sm">
            <h2 class="text-xl font-semibold text-slate-900">No Pending Checkouts</h2>
            <p class="mt-2 text-slate-500">You have no unfinished marketplace checkouts.</p>
        </div>
    @endif
</div>
@endsection
