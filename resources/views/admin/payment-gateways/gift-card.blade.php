@extends('admin.layouts.app')

@section('title', 'Gift Card Gateway')

@section('content')
<div class="min-h-full bg-slate-50 px-6 py-8">
    <div class="mx-auto max-w-6xl">

        <div class="mb-5">
            <a href="{{ route('admin.payment-gateways.index') }}"
               class="inline-flex items-center rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-bold text-blue-600 shadow-sm transition hover:bg-slate-50">
                ← Back to Gateways
            </a>
        </div>

        <div class="mb-8">
            <p class="text-xs font-black uppercase tracking-[0.2em] text-amber-600">
                Payment Gateways
            </p>

            <h1 class="mt-2 text-3xl font-black tracking-tight text-slate-900">
                Gift Card
            </h1>

            <p class="mt-3 max-w-3xl text-sm leading-6 text-slate-500">
                Configure Esubiz gift cards, including value, validity, usage,
                checkout eligibility and wallet funding.
            </p>
        </div>

        @if(session('success'))
            <div class="mb-6 rounded-2xl border border-emerald-200 bg-emerald-50 px-5 py-4 text-sm font-semibold text-emerald-700">
                {{ session('success') }}
            </div>
        @endif

        <div class="mb-6 rounded-3xl border border-slate-200 bg-white p-7 shadow-sm">
            <div class="mb-6">
                <h2 class="text-xl font-black text-slate-900">
                    Generate Gift Cards
                </h2>
                <p class="mt-2 text-sm text-slate-500">
                    Generate up to 1,000 gift cards in one batch.
                </p>
            </div>

            <form method="POST"
                  action="{{ route('admin.payment-gateways.gift-card.generate') }}">
                @csrf

                <div class="grid gap-4 md:grid-cols-3">

                    <div>
                        <label class="text-xs font-bold text-slate-500">Quantity</label>
                        <input type="number"
                               name="quantity"
                               min="1"
                               max="1000"
                               value="{{ old('quantity', 1) }}"
                               required
                               class="mt-2 w-full rounded-xl border border-slate-200 px-4 py-3 text-sm outline-none focus:border-amber-500">
                    </div>

                    <div>
                        <label class="text-xs font-bold text-slate-500">Name</label>
                        <input type="text"
                               name="name"
                               value="{{ old('name', 'Esubiz Gift Card') }}"
                               class="mt-2 w-full rounded-xl border border-slate-200 px-4 py-3 text-sm outline-none focus:border-amber-500">
                    </div>

                    <div>
                        <label class="text-xs font-bold text-slate-500">Amount</label>
                        <input type="number"
                               name="amount"
                               min="0.01"
                               step="0.01"
                               required
                               class="mt-2 w-full rounded-xl border border-slate-200 px-4 py-3 text-sm outline-none focus:border-amber-500">
                    </div>

                    <div>
                        <label class="text-xs font-bold text-slate-500">Currency</label>
                        <input type="text"
                               name="currency"
                               maxlength="3"
                               value="{{ old('currency', 'NGN') }}"
                               required
                               class="mt-2 w-full rounded-xl border border-slate-200 px-4 py-3 text-sm uppercase outline-none focus:border-amber-500">
                    </div>

                    <div>
                        <label class="text-xs font-bold text-slate-500">Usage Limit</label>
                        <input type="number"
                               name="usage_limit"
                               min="1"
                               value="{{ old('usage_limit') }}"
                               placeholder="Unlimited"
                               class="mt-2 w-full rounded-xl border border-slate-200 px-4 py-3 text-sm outline-none focus:border-amber-500">
                    </div>

                    <div>
                        <label class="text-xs font-bold text-slate-500">Expires At</label>
                        <input type="datetime-local"
                               name="expires_at"
                               value="{{ old('expires_at') }}"
                               class="mt-2 w-full rounded-xl border border-slate-200 px-4 py-3 text-sm outline-none focus:border-amber-500">
                    </div>

                </div>

                <div class="mt-5 flex flex-wrap gap-5">

                    <label class="flex items-center gap-2 text-sm font-bold text-slate-700">
                        <input type="checkbox"
                               name="usable_at_checkout"
                               value="1"
                               checked>
                        Checkout
                    </label>

                    <label class="flex items-center gap-2 text-sm font-bold text-slate-700">
                        <input type="checkbox"
                               name="usable_for_wallet_funding"
                               value="1"
                               checked>
                        Wallet Funding
                    </label>

                    <label class="flex items-center gap-2 text-sm font-bold text-slate-700">
                        <input type="checkbox"
                               name="allow_partial_redemption"
                               value="1"
                               checked>
                        Partial Redemption
                    </label>

                    <label class="flex items-center gap-2 text-sm font-bold text-slate-700">
                        <input type="checkbox"
                               name="enabled"
                               value="1"
                               checked>
                        Enabled
                    </label>

                </div>

                <div class="mt-6 flex justify-end">
                    <button type="submit"
                            class="rounded-xl bg-amber-600 px-6 py-3 text-sm font-black text-white hover:bg-amber-700">
                        Generate Gift Cards
                    </button>
                </div>
            </form>
        </div>

        <div class="mb-6 flex flex-wrap items-center gap-3">

            <a href="{{ route('admin.payment-gateways.gift-card.csv') }}"
               class="inline-flex items-center rounded-xl border border-slate-200 bg-white px-5 py-3 text-sm font-black text-slate-700 shadow-sm hover:bg-slate-50">
                Download CSV
            </a>

            <a href="{{ route('admin.payment-gateways.gift-card.pdf') }}"
               class="inline-flex items-center rounded-xl border border-slate-200 bg-white px-5 py-3 text-sm font-black text-slate-700 shadow-sm hover:bg-slate-50">
                Download PDF
            </a>

        </div>

        <div class="rounded-3xl border border-slate-200 bg-white shadow-sm">

            <div class="flex flex-col gap-4 border-b border-slate-100 px-6 py-5 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h2 class="text-xl font-black text-slate-900">
                        Gift Cards
                    </h2>
                    <p class="mt-1 text-sm text-slate-500">
                        Manage issued gift cards and their balances.
                    </p>
                </div>

                <div class="flex flex-wrap gap-2">
                    <a href="{{ route('admin.payment-gateways.gift-card.csv') }}"
                       class="rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-xs font-black text-slate-700 hover:bg-slate-50">
                        CSV
                    </a>

                    <a href="{{ route('admin.payment-gateways.gift-card.pdf') }}"
                       class="rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-xs font-black text-slate-700 hover:bg-slate-50">
                        PDF
                    </a>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="bg-slate-50">
                        <tr class="text-left text-xs font-black uppercase tracking-wide text-slate-500">
                            <th class="px-5 py-4">Gift Card</th>
                            <th class="px-5 py-4">Value</th>
                            <th class="px-5 py-4">Balance</th>
                            <th class="px-5 py-4">Usage</th>
                            <th class="px-5 py-4">Status</th>
                            <th class="px-5 py-4">Expiry</th>
                            <th class="px-5 py-4">Created</th>
                            <th class="px-5 py-4 text-right"> </th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-slate-100">

                        @forelse($giftCards as $card)
                            <tr class="hover:bg-slate-50">

                                <td class="px-5 py-4">
                                    <div class="font-black text-slate-900">
                                        {{ $card->name }}
                                    </div>

                                    <code class="mt-1 inline-block select-all rounded-lg bg-slate-100 px-2.5 py-1 text-xs font-bold text-slate-700">
                                        {{ $card->code }}
                                    </code>
                                </td>

                                <td class="px-5 py-4 whitespace-nowrap font-semibold text-slate-700">
                                    {{ $card->currency }}
                                    {{ number_format((float) $card->initial_amount, 2) }}
                                </td>

                                <td class="px-5 py-4 whitespace-nowrap font-black text-slate-900">
                                    {{ $card->currency }}
                                    {{ number_format((float) $card->remaining_balance, 2) }}
                                </td>

                                <td class="px-5 py-4 whitespace-nowrap text-slate-600">
                                    {{ $card->usage_count }}
                                    /
                                    {{ $card->usage_limit ?? '∞' }}
                                </td>

                                <td class="px-5 py-4">
                                    <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-bold capitalize text-slate-700">
                                        {{ $card->status }}
                                    </span>
                                </td>

                                <td class="px-5 py-4 whitespace-nowrap text-slate-600">
                                    {{ $card->expires_at ?: 'Never' }}
                                </td>

                                <td class="px-5 py-4 whitespace-nowrap text-slate-600">
                                    {{ $card->created_at }}
                                </td>

                                <td class="px-5 py-4 text-right">
                                    <details class="relative inline-block text-left">
                                        <summary class="flex h-9 w-9 cursor-pointer list-none items-center justify-center rounded-xl border border-slate-200 bg-white text-lg font-black text-slate-600 hover:bg-slate-50">
                                            ⋮
                                        </summary>

                                        <div class="absolute right-0 z-20 mt-2 w-44 rounded-2xl border border-slate-200 bg-white p-2 shadow-xl">

                                            <a href="{{ route('admin.payment-gateways.gift-card.show', $card->id) }}"
                                               class="block rounded-xl px-3 py-2.5 text-sm font-bold text-slate-700 hover:bg-slate-50">
                                                View / Edit
                                            </a>

                                            @if($card->enabled)
                                                <form method="POST"
                                                      action="{{ route('admin.payment-gateways.gift-card.disable', $card->id) }}">
                                                    @csrf
                                                    <button type="submit"
                                                            class="w-full rounded-xl px-3 py-2.5 text-left text-sm font-bold text-amber-700 hover:bg-amber-50">
                                                        Disable
                                                    </button>
                                                </form>
                                            @else
                                                <form method="POST"
                                                      action="{{ route('admin.payment-gateways.gift-card.enable', $card->id) }}">
                                                    @csrf
                                                    <button type="submit"
                                                            class="w-full rounded-xl px-3 py-2.5 text-left text-sm font-bold text-emerald-700 hover:bg-emerald-50">
                                                        Enable
                                                    </button>
                                                </form>
                                            @endif

                                            <form method="POST"
                                                  action="{{ route('admin.payment-gateways.gift-card.destroy', $card->id) }}"
                                                  onsubmit="return confirm('Delete this gift card?');">
                                                @csrf
                                                @method('DELETE')

                                                <button type="submit"
                                                        class="w-full rounded-xl px-3 py-2.5 text-left text-sm font-bold text-red-600 hover:bg-red-50">
                                                    Delete
                                                </button>
                                            </form>

                                        </div>
                                    </details>
                                </td>

                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-5 py-12 text-center text-sm text-slate-500">
                                    No gift cards have been created yet.
                                </td>
                            </tr>
                        @endforelse

                    </tbody>
                </table>
            </div>

            <div class="border-t border-slate-100 px-6 py-5">
                {{ $giftCards->links() }}
            </div>

        </div>

        <div class="mt-6 rounded-3xl border border-slate-200 bg-white shadow-sm">

            <div class="border-b border-slate-100 px-6 py-5">
                <h2 class="text-xl font-black text-slate-900">
                    Gift Card Usage
                </h2>

                <p class="mt-1 text-sm text-slate-500">
                    See who used each gift card and what Esubiz service it was used for.
                </p>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="bg-slate-50">
                        <tr class="text-left text-xs font-black uppercase tracking-wide text-slate-500">
                            <th class="px-5 py-4">User</th>
                            <th class="px-5 py-4">Gift Card</th>
                            <th class="px-5 py-4">Service</th>
                            <th class="px-5 py-4">Amount</th>
                            <th class="px-5 py-4">Balance After</th>
                            <th class="px-5 py-4">Date</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-slate-100">
                        @forelse($giftCardUsage as $usage)
                            <tr class="hover:bg-slate-50">

                                <td class="px-5 py-4">
                                    <div class="font-bold text-slate-900">
                                        {{ $usage->user_name ?: 'Guest / Unknown' }}
                                    </div>
                                    @if($usage->user_email)
                                        <div class="mt-1 text-xs text-slate-500">
                                            {{ $usage->user_email }}
                                        </div>
                                    @endif
                                </td>

                                <td class="px-5 py-4">
                                    <div class="font-bold text-slate-900">
                                        {{ $usage->gift_card_name }}
                                    </div>
                                    <code class="mt-1 inline-block rounded-lg bg-slate-100 px-2 py-1 text-xs font-bold text-slate-600">
                                        {{ $usage->gift_card_code }}
                                    </code>
                                </td>

                                <td class="px-5 py-4">
                                    <div class="font-semibold capitalize text-slate-700">
                                        {{ str_replace('_', ' ', $usage->usage_context ?: $usage->reference_type ?: $usage->type) }}
                                    </div>

                                    @if($usage->reference_id)
                                        <div class="mt-1 text-xs text-slate-500">
                                            Reference #{{ $usage->reference_id }}
                                        </div>
                                    @endif
                                </td>

                                <td class="px-5 py-4 whitespace-nowrap font-bold text-slate-700">
                                    {{ $usage->currency }}
                                    {{ number_format((float) $usage->amount, 2) }}
                                </td>

                                <td class="px-5 py-4 whitespace-nowrap font-black text-slate-900">
                                    {{ $usage->currency }}
                                    {{ number_format((float) $usage->balance_after, 2) }}
                                </td>

                                <td class="px-5 py-4 whitespace-nowrap text-slate-500">
                                    {{ $usage->created_at }}
                                </td>

                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-5 py-10 text-center text-sm text-slate-500">
                                    No Gift Card usage records yet.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

        </div>

        </div>

    </div>
</div>
@endsection
