@extends('admin.layouts.app')

@section('content')
<div class="min-h-screen bg-slate-50 px-4 py-8 sm:px-6 lg:px-8">
    <div class="mx-auto max-w-7xl">

        <div class="mb-8">
            <div class="text-xs font-black uppercase tracking-[0.18em] text-blue-600">
                Billing
            </div>

            <h1 class="mt-2 text-3xl font-black text-slate-950">
                Orders
            </h1>

            <p class="mt-2 text-sm text-slate-500">
                View your SaaS marketplace purchases and the websites they belong to.
            </p>
        </div>

        <div class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200">

                    <thead class="bg-slate-50">
                        <tr>
                            <th class="px-5 py-4 text-left text-[10px] font-black uppercase tracking-wider text-slate-500">
                                Order
                            </th>

                            <th class="px-5 py-4 text-left text-[10px] font-black uppercase tracking-wider text-slate-500">
                                Product
                            </th>

                            <th class="px-5 py-4 text-left text-[10px] font-black uppercase tracking-wider text-slate-500">
                                Website
                            </th>

                            <th class="px-5 py-4 text-left text-[10px] font-black uppercase tracking-wider text-slate-500">
                                Amount
                            </th>

                            <th class="px-5 py-4 text-left text-[10px] font-black uppercase tracking-wider text-slate-500">
                                Payment
                            </th>

                            <th class="px-5 py-4 text-left text-[10px] font-black uppercase tracking-wider text-slate-500">
                                Order Status
                            </th>

                            <th class="px-5 py-4 text-left text-[10px] font-black uppercase tracking-wider text-slate-500">
                                Purchased
                            </th>

                            <th class="px-5 py-4 text-right text-[10px] font-black uppercase tracking-wider text-slate-500">
                                Action
                            </th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-slate-100 bg-white">
                        @forelse($orders as $order)
                            <tr class="hover:bg-slate-50/70">

                                <td class="whitespace-nowrap px-5 py-4">
                                    <div class="text-sm font-black text-slate-900">
                                        {{ $order->reference }}
                                    </div>
                                    <div class="mt-1 text-xs text-slate-400">
                                        #{{ $order->id }}
                                    </div>
                                </td>

                                <td class="px-5 py-4">
                                    <div class="text-sm font-black text-slate-900">
                                        {{ $order->product_title ?? 'Marketplace Product' }}
                                    </div>

                                    <div class="mt-1 text-xs font-semibold text-slate-400">
                                        {{ ucwords(str_replace(['_', '-'], ' ', $order->product_type ?? 'product')) }}
                                    </div>
                                </td>

                                <td class="px-5 py-4">
                                    @if(!empty($order->website_name))
                                        <div class="text-sm font-black text-slate-900">
                                            {{ $order->website_name }}
                                        </div>

                                        <div class="mt-1 text-xs text-slate-400">
                                            {{ $order->website_domain
                                                ?: $order->website_subdomain
                                                ?: 'Website #' . $order->website_id }}
                                        </div>
                                    @elseif(!empty($order->website_id))
                                        <div class="text-sm font-semibold text-slate-600">
                                            Website #{{ $order->website_id }}
                                        </div>
                                    @else
                                        <div class="text-sm font-semibold text-slate-400">
                                            Account-level purchase
                                        </div>
                                    @endif
                                </td>

                                <td class="whitespace-nowrap px-5 py-4">
                                    <div class="text-sm font-black text-slate-900">
                                        {{ strtoupper($order->currency ?? 'NGN') }}
                                        {{ number_format((float) ($order->amount ?? 0), 2) }}
                                    </div>
                                </td>

                                <td class="whitespace-nowrap px-5 py-4">
                                    @php
                                        $paymentStatus = strtolower($order->payment_status ?? 'pending');
                                    @endphp

                                    <span class="inline-flex rounded-full px-3 py-1 text-xs font-black
                                        {{ $paymentStatus === 'paid'
                                            ? 'bg-emerald-50 text-emerald-700'
                                            : ($paymentStatus === 'failed'
                                                ? 'bg-red-50 text-red-700'
                                                : 'bg-amber-50 text-amber-700') }}">
                                        {{ ucfirst($paymentStatus) }}
                                    </span>
                                </td>

                                <td class="whitespace-nowrap px-5 py-4">
                                    @php
                                        $orderStatus = strtolower($order->status ?? 'pending');
                                    @endphp

                                    <span class="inline-flex rounded-full px-3 py-1 text-xs font-black
                                        {{ $orderStatus === 'fulfilled'
                                            ? 'bg-blue-50 text-blue-700'
                                            : ($orderStatus === 'cancelled'
                                                ? 'bg-slate-100 text-slate-500'
                                                : 'bg-slate-100 text-slate-700') }}">
                                        {{ ucfirst($orderStatus) }}
                                    </span>
                                </td>

                                <td class="whitespace-nowrap px-5 py-4 text-sm font-semibold text-slate-600">
                                    {{ \Carbon\Carbon::parse($order->created_at)->format('d M Y, h:i A') }}
                                </td>

                                <td class="whitespace-nowrap px-5 py-4 text-right">
                                    @if(($order->payment_status ?? null) === 'pending')
                                        <a
                                            href="{{ route('marketplace.checkout', ['order' => $order->id]) }}"
                                            class="inline-flex rounded-xl bg-blue-600 px-4 py-2 text-xs font-black text-white hover:bg-blue-700"
                                        >
                                            Continue Payment
                                        </a>
                                    @else
                                        <a
                                            href="{{ route('marketplace.payment-status', ['order' => $order->id]) }}"
                                            class="inline-flex rounded-xl border border-slate-200 bg-white px-4 py-2 text-xs font-black text-slate-700 hover:bg-slate-50"
                                        >
                                            View
                                        </a>
                                    @endif
                                </td>

                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-6 py-16 text-center">
                                    <div class="text-base font-black text-slate-700">
                                        No SaaS orders yet
                                    </div>

                                    <p class="mt-2 text-sm text-slate-400">
                                        Purchases made for your Esubiz websites will appear here.
                                    </p>
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
