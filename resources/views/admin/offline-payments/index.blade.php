@extends('admin.layouts.app')

@section('content')
<div class="space-y-6">

    <div>
        <h1 class="text-2xl font-black text-slate-900">
            Offline Payments
        </h1>

        <p class="mt-1 text-sm text-slate-500">
            Review offline payment submissions and confirm payments manually.
        </p>
    </div>

    <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">

        <div class="overflow-x-auto">
            <table class="min-w-full text-left">
                <thead class="border-b border-slate-200 bg-slate-50">
                    <tr>
                        <th class="px-5 py-4 text-xs font-black uppercase tracking-wider text-slate-500">
                            Transaction
                        </th>

                        <th class="px-5 py-4 text-xs font-black uppercase tracking-wider text-slate-500">
                            Payment Method
                        </th>

                        <th class="px-5 py-4 text-xs font-black uppercase tracking-wider text-slate-500">
                            Amount
                        </th>

                        <th class="px-5 py-4 text-xs font-black uppercase tracking-wider text-slate-500">
                            Status
                        </th>

                        <th class="px-5 py-4 text-xs font-black uppercase tracking-wider text-slate-500">
                            Receipt
                        </th>

                        <th class="px-5 py-4 text-right text-xs font-black uppercase tracking-wider text-slate-500">
                            Action
                        </th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-slate-100">

                    @forelse($payments as $payment)

                        @php
                            $isSuccessful = $payment->status === 'successful';
                            $isFailed = $payment->status === 'failed';

                            $receipt = $payment->metadata['receipt'] ?? null;
                        @endphp

                        <tr class="hover:bg-slate-50">

                            <td class="px-5 py-5">
                                <div class="font-bold text-slate-900">
                                    {{ $payment->transaction_reference }}
                                </div>

                                <div class="mt-1 text-xs text-slate-500">
                                    {{ optional($payment->created_at)->format('M d, Y h:i A') }}
                                </div>
                            </td>

                            <td class="px-5 py-5">
                                <div class="font-bold text-slate-800">
                                    {{ $payment->payment_method_name ?? 'Offline Payment' }}
                                </div>

                                <div class="mt-1 text-xs capitalize text-slate-500">
                                    {{ str_replace('_', ' ', $payment->payment_method_type ?? '') }}
                                </div>
                            </td>

                            <td class="px-5 py-5">
                                <div class="font-black text-slate-900">
                                    {{ $payment->currency }}
                                    {{ number_format((float) $payment->amount, 2) }}
                                </div>
                            </td>

                            <td class="px-5 py-5">

                                @if($isSuccessful)

                                    <span class="inline-flex rounded-full bg-blue-50 px-3 py-1 text-xs font-black text-blue-700">
                                        Paid
                                    </span>

                                @elseif($isFailed)

                                    <span class="inline-flex rounded-full bg-red-50 px-3 py-1 text-xs font-black text-red-700">
                                        Rejected
                                    </span>

                                @else

                                    <span class="inline-flex rounded-full bg-amber-50 px-3 py-1 text-xs font-black text-amber-700">
                                        Pending
                                    </span>

                                @endif

                            </td>

                            <td class="px-5 py-5">

                                @if($receipt)

                                    <a
                                        href="{{ $receipt }}"
                                        target="_blank"
                                        rel="noopener"
                                        class="font-bold text-blue-600 hover:text-blue-800"
                                    >
                                        View Receipt
                                    </a>

                                @else

                                    <span class="text-sm text-slate-400">
                                        No receipt
                                    </span>

                                @endif

                            </td>

                            <td class="px-5 py-5 text-right">

                                @if(!$isSuccessful && !$isFailed)

                                    <div class="flex justify-end gap-2">

                                        <form
                                            method="POST"
                                            action="{{ route('admin.payment-gateways.offline-payments.mark-paid', $payment->id) }}"
                                        >
                                            @csrf

                                            <button
                                                type="submit"
                                                class="rounded-xl bg-blue-600 px-4 py-2 text-xs font-black text-white transition hover:bg-blue-700"
                                            >
                                                Mark Paid
                                            </button>
                                        </form>

                                        <form
                                            method="POST"
                                            action="{{ route('admin.payment-gateways.offline-payments.reject', $payment->id) }}"
                                        >
                                            @csrf

                                            <button
                                                type="submit"
                                                class="rounded-xl border border-slate-300 bg-white px-4 py-2 text-xs font-black text-slate-700 transition hover:bg-slate-50"
                                            >
                                                Reject
                                            </button>
                                        </form>

                                    </div>

                                @else

                                    <span class="text-xs font-bold text-slate-400">
                                        No action
                                    </span>

                                @endif

                            </td>

                        </tr>

                    @empty

                        <tr>
                            <td
                                colspan="6"
                                class="px-5 py-16 text-center"
                            >
                                <div class="text-sm font-bold text-slate-500">
                                    No offline payment submissions yet.
                                </div>

                                <div class="mt-1 text-xs text-slate-400">
                                    Offline payments will appear here after users submit them.
                                </div>
                            </td>
                        </tr>

                    @endforelse

                </tbody>
            </table>
        </div>

    </div>

</div>
@endsection
