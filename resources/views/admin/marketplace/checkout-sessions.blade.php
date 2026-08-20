@extends('admin.layouts.app')

@section('title', 'Marketplace Checkout Sessions')

@section('content')
<div class="min-h-screen bg-slate-50">
    <div class="mx-auto max-w-7xl px-6 py-8">

        <div class="mb-8">
            <h1 class="text-2xl font-black text-slate-900">
                Marketplace Checkout Sessions
            </h1>

            <p class="mt-2 text-sm text-slate-500">
                Manage pending User and Developer marketplace checkouts.
            </p>
        </div>

        @if($sessions->isEmpty())

            <div class="rounded-3xl border border-slate-200 bg-white p-10 text-center shadow-sm">
                <div class="text-lg font-black text-slate-900">
                    No pending checkout sessions
                </div>

                <p class="mt-2 text-sm text-slate-500">
                    The marketplace currently has no active or pending-payment checkouts.
                </p>
            </div>

        @else

            <div class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">

                <div class="overflow-x-auto">
                    <table class="min-w-full text-left">

                        <thead class="border-b border-slate-100 bg-slate-50">
                            <tr>
                                <th class="px-6 py-4 text-[10px] font-black uppercase tracking-wider text-slate-400">Buyer</th>
                                <th class="px-6 py-4 text-[10px] font-black uppercase tracking-wider text-slate-400">Mode</th>
                                <th class="px-6 py-4 text-[10px] font-black uppercase tracking-wider text-slate-400">Product</th>
                                <th class="px-6 py-4 text-[10px] font-black uppercase tracking-wider text-slate-400">Amount</th>
                                <th class="px-6 py-4 text-[10px] font-black uppercase tracking-wider text-slate-400">Status</th>
                                <th class="px-6 py-4 text-right text-[10px] font-black uppercase tracking-wider text-slate-400">Created</th>
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-slate-100">

                            @foreach($sessions as $session)

                                <tr class="transition hover:bg-slate-50">

                                    <td class="px-6 py-5">
                                        <div class="text-sm font-bold text-slate-900">
                                            {{ $session->buyer_name ?: 'Unknown' }}
                                        </div>
                                        <div class="mt-1 text-xs text-slate-400">
                                            {{ $session->buyer_email }}
                                        </div>
                                    </td>

                                    <td class="px-6 py-5">
                                        <span class="rounded-full bg-blue-50 px-3 py-1 text-[10px] font-black uppercase text-blue-600">
                                            {{ $session->account_mode }}
                                        </span>
                                    </td>

                                    <td class="px-6 py-5">
                                        <div class="text-sm font-bold text-slate-800">
                                            {{ ucwords(str_replace('_', ' ', $session->product_type)) }}
                                        </div>

                                        <div class="mt-1 text-xs text-slate-400">
                                            Product #{{ $session->product_id }}
                                        </div>
                                    </td>

                                    <td class="px-6 py-5">
                                        <span class="text-sm font-black text-slate-900">
                                            {{ $session->currency }}
                                            {{ number_format($session->total_amount, 2) }}
                                        </span>
                                    </td>

                                    <td class="px-6 py-5">
                                        <span class="rounded-full bg-amber-50 px-3 py-1 text-[10px] font-black uppercase text-amber-600">
                                            {{ str_replace('_', ' ', $session->status) }}
                                        </span>
                                    </td>

                                    <td class="px-6 py-5">
                                        <div class="text-right text-xs font-semibold text-slate-400">
                                            {{ $session->created_at }}
                                        </div>

                                        <form
                                            method="POST"
                                            action="{{ route('admin.marketplace.checkout-sessions.delete', $session->id) }}"
                                            class="mt-3 text-right"
                                            onsubmit="return confirm('Cancel this unpaid checkout session?');"
                                        >
                                            @csrf
                                            @method('DELETE')

                                            <button
                                                type="submit"
                                                class="rounded-xl bg-red-50 px-3 py-2 text-[10px] font-black uppercase text-red-600 transition hover:bg-red-100"
                                            >
                                                Delete
                                            </button>
                                        </form>
                                    </td>

                                </tr>

                            @endforeach

                        </tbody>

                    </table>
                </div>

            </div>

        @endif

    </div>
</div>
@endsection
