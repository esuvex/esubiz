@extends('admin.layouts.app')

@section('title', 'Gift Card')

@section('content')
<div class="min-h-full bg-slate-50 px-6 py-8">
    <div class="mx-auto max-w-6xl">

        <div class="mb-5">
            <a href="{{ route('admin.payment-gateways.gift-card') }}"
               class="inline-flex items-center rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-bold text-slate-700 shadow-sm hover:bg-slate-50">
                ← Back to Gift Cards
            </a>
        </div>

        @if(session('success'))
            <div class="mb-6 rounded-2xl border border-emerald-200 bg-emerald-50 px-5 py-4 text-sm font-semibold text-emerald-700">
                {{ session('success') }}
            </div>
        @endif

        <div class="mb-8 flex flex-wrap items-start justify-between gap-4">
            <div>
                <p class="text-xs font-black uppercase tracking-[0.2em] text-amber-600">
                    Payment Gateway
                </p>

                <h1 class="mt-2 text-3xl font-black tracking-tight text-slate-900">
                    {{ $giftCard->name }}
                </h1>

                <p class="mt-2 text-sm font-semibold text-slate-500">
                    {{ $giftCard->code }}
                </p>
            </div>

            <div class="flex flex-wrap gap-2">
                @if($giftCard->enabled)
                    <form method="POST"
                          action="{{ route('admin.payment-gateways.gift-card.disable', $giftCard->id) }}">
                        @csrf
                        <button type="submit"
                                class="rounded-xl bg-amber-600 px-4 py-2.5 text-sm font-black text-white hover:bg-amber-700">
                            Disable
                        </button>
                    </form>
                @else
                    <form method="POST"
                          action="{{ route('admin.payment-gateways.gift-card.enable', $giftCard->id) }}">
                        @csrf
                        <button type="submit"
                                class="rounded-xl bg-emerald-600 px-4 py-2.5 text-sm font-black text-white hover:bg-emerald-700">
                            Enable
                        </button>
                    </form>
                @endif

                <form method="POST"
                      action="{{ route('admin.payment-gateways.gift-card.destroy', $giftCard->id) }}"
                      onsubmit="return confirm('Delete this gift card?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit"
                            class="rounded-xl bg-red-600 px-4 py-2.5 text-sm font-black text-white hover:bg-red-700">
                        Delete
                    </button>
                </form>
            </div>
        </div>

        <div class="grid gap-6 lg:grid-cols-3">

            <div class="rounded-3xl border border-slate-200 bg-white p-7 shadow-sm lg:col-span-2">
                <h2 class="text-xl font-black text-slate-900">
                    Gift Card Settings
                </h2>

                <form method="POST"
                      action="{{ route('admin.payment-gateways.gift-card.update', $giftCard->id) }}"
                      class="mt-6">
                    @csrf
                    @method('PATCH')

                    <div class="grid gap-5 md:grid-cols-2">

                        <div class="md:col-span-2">
                            <label class="text-xs font-bold text-slate-500">Name</label>
                            <input type="text"
                                   name="name"
                                   value="{{ $giftCard->name }}"
                                   required
                                   class="mt-2 w-full rounded-xl border border-slate-200 px-4 py-3 text-sm outline-none focus:border-amber-500">
                        </div>

                        <div>
                            <label class="text-xs font-bold text-slate-500">Usage Limit</label>
                            <input type="number"
                                   name="usage_limit"
                                   min="1"
                                   value="{{ $giftCard->usage_limit }}"
                                   class="mt-2 w-full rounded-xl border border-slate-200 px-4 py-3 text-sm outline-none focus:border-amber-500">
                        </div>

                        <div>
                            <label class="text-xs font-bold text-slate-500">Starts At</label>
                            <input type="datetime-local"
                                   name="starts_at"
                                   value="{{ $giftCard->starts_at ? \Carbon\Carbon::parse($giftCard->starts_at)->format('Y-m-d\TH:i') : '' }}"
                                   class="mt-2 w-full rounded-xl border border-slate-200 px-4 py-3 text-sm outline-none focus:border-amber-500">
                        </div>

                        <div>
                            <label class="text-xs font-bold text-slate-500">Expires At</label>
                            <input type="datetime-local"
                                   name="expires_at"
                                   value="{{ $giftCard->expires_at ? \Carbon\Carbon::parse($giftCard->expires_at)->format('Y-m-d\TH:i') : '' }}"
                                   class="mt-2 w-full rounded-xl border border-slate-200 px-4 py-3 text-sm outline-none focus:border-amber-500">
                        </div>

                    </div>

                    <div class="mt-6 grid gap-3 md:grid-cols-3">

                        <label class="flex items-center gap-3 rounded-2xl border border-slate-200 p-4">
                            <input type="checkbox"
                                   name="usable_at_checkout"
                                   value="1"
                                   {{ $giftCard->usable_at_checkout ? 'checked' : '' }}>
                            <span class="text-sm font-bold text-slate-700">Checkout</span>
                        </label>

                        <label class="flex items-center gap-3 rounded-2xl border border-slate-200 p-4">
                            <input type="checkbox"
                                   name="usable_for_wallet_funding"
                                   value="1"
                                   {{ $giftCard->usable_for_wallet_funding ? 'checked' : '' }}>
                            <span class="text-sm font-bold text-slate-700">Wallet Funding</span>
                        </label>

                        <label class="flex items-center gap-3 rounded-2xl border border-slate-200 p-4">
                            <input type="checkbox"
                                   name="allow_partial_redemption"
                                   value="1"
                                   {{ $giftCard->allow_partial_redemption ? 'checked' : '' }}>
                            <span class="text-sm font-bold text-slate-700">Partial Redemption</span>
                        </label>

                    </div>

                    <div class="mt-6 flex justify-end">
                        <button type="submit"
                                class="rounded-xl bg-blue-600 px-6 py-3 text-sm font-black text-white hover:bg-blue-700">
                            Save Changes
                        </button>
                    </div>
                </form>
            </div>

            <div class="rounded-3xl border border-slate-200 bg-white p-7 shadow-sm">
                <h2 class="text-xl font-black text-slate-900">
                    Balance
                </h2>

                <div class="mt-5 text-3xl font-black text-slate-900">
                    {{ $giftCard->currency }}
                    {{ number_format((float) $giftCard->remaining_balance, 2) }}
                </div>

                <div class="mt-6 space-y-3 text-sm">
                    <div class="flex justify-between">
                        <span class="text-slate-500">Original</span>
                        <span class="font-bold">{{ $giftCard->currency }} {{ number_format((float) $giftCard->initial_amount, 2) }}</span>
                    </div>

                    <div class="flex justify-between">
                        <span class="text-slate-500">Status</span>
                        <span class="font-bold capitalize">{{ $giftCard->status }}</span>
                    </div>
                </div>
            </div>

        </div>

        <div class="mt-6 rounded-3xl border border-slate-200 bg-white p-7 shadow-sm">
            <h2 class="text-xl font-black text-slate-900">
                Gift Card Transactions
            </h2>

            <div class="mt-5 overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead>
                        <tr class="border-b border-slate-100 text-left text-xs font-black uppercase text-slate-500">
                            <th class="px-4 py-3">Date</th>
                            <th class="px-4 py-3">Type</th>
                            <th class="px-4 py-3">Amount</th>
                            <th class="px-4 py-3">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($transactions as $transaction)
                            <tr class="border-b border-slate-100">
                                <td class="px-4 py-4">{{ $transaction->created_at }}</td>
                                <td class="px-4 py-4">{{ $transaction->type }}</td>
                                <td class="px-4 py-4">{{ $giftCard->currency }} {{ number_format((float) $transaction->amount, 2) }}</td>
                                <td class="px-4 py-4 capitalize">{{ $transaction->type }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-4 py-8 text-center text-slate-500">
                                    No transactions yet.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-5">
                {{ $transactions->links() }}
            </div>
        </div>

    </div>
</div>
@endsection
