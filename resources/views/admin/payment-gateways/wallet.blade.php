@extends('admin.layouts.app')

@section('title', 'Wallet Gateway')

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
            <p class="text-xs font-black uppercase tracking-[0.2em] text-blue-600">
                Payment Gateways
            </p>

            <h1 class="mt-2 text-3xl font-black tracking-tight text-slate-900">
                Wallet
            </h1>

            <p class="mt-3 max-w-3xl text-sm leading-6 text-slate-500">
                Configure how users can fund and withdraw from their Esubiz wallets.
                Wallet funding and payouts are wallet movements and are not platform
                revenue or expenses.
            </p>
        </div>

        @if(session('success'))
            <div class="mb-6 rounded-2xl border border-emerald-200 bg-emerald-50 px-5 py-4 text-sm font-semibold text-emerald-700">
                {{ session('success') }}
            </div>
        @endif

        <form method="POST"
              action="{{ route('admin.payment-gateways.wallet.update') }}"
              class="rounded-3xl border border-slate-200 bg-white p-7 shadow-sm">
            @csrf
            @method('PATCH')

            <div class="grid gap-6 md:grid-cols-2">

                <label class="flex items-center justify-between rounded-2xl border border-slate-200 p-5">
                    <div>
                        <div class="font-black text-slate-900">Wallet Funding</div>
                        <div class="mt-1 text-sm text-slate-500">
                            Allow users to fund their wallets.
                        </div>
                    </div>

                    <input type="checkbox"
                           name="funding_enabled"
                           value="1"
                           class="h-5 w-5"
                           @checked((bool) ($settings->funding_enabled ?? true))>
                </label>

                <label class="flex items-center justify-between rounded-2xl border border-slate-200 p-5">
                    <div>
                        <div class="font-black text-slate-900">Wallet Payouts</div>
                        <div class="mt-1 text-sm text-slate-500">
                            Allow users to request wallet payouts.
                        </div>
                    </div>

                    <input type="checkbox"
                           name="payout_enabled"
                           value="1"
                           class="h-5 w-5"
                           @checked((bool) ($settings->payout_enabled ?? true))>
                </label>

                <div>
                    <label class="text-xs font-bold text-slate-500">
                        Minimum Funding
                    </label>
                    <input type="number"
                           step="0.01"
                           name="minimum_funding"
                           value="{{ $settings->minimum_funding ?? 0 }}"
                           class="mt-2 w-full rounded-xl border border-slate-200 px-4 py-3 text-sm">
                </div>

                <div>
                    <label class="text-xs font-bold text-slate-500">
                        Maximum Funding
                    </label>
                    <input type="number"
                           step="0.01"
                           name="maximum_funding"
                           value="{{ $settings->maximum_funding ?? '' }}"
                           placeholder="No limit"
                           class="mt-2 w-full rounded-xl border border-slate-200 px-4 py-3 text-sm">
                </div>

                <div>
                    <label class="text-xs font-bold text-slate-500">
                        Funding Fee Type
                    </label>
                    <select name="funding_fee_type"
                            class="mt-2 w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm">
                        <option value="free" @selected(($settings->funding_fee_type ?? 'free') === 'free')>
                            Free
                        </option>
                        <option value="fixed" @selected(($settings->funding_fee_type ?? '') === 'fixed')>
                            Fixed
                        </option>
                        <option value="percentage" @selected(($settings->funding_fee_type ?? '') === 'percentage')>
                            Percentage
                        </option>
                    </select>
                </div>

                <div>
                    <label class="text-xs font-bold text-slate-500">
                        Funding Fee
                    </label>
                    <input type="number"
                           step="0.01"
                           name="funding_fee"
                           value="{{ ($settings->funding_fee ?? 0) ?: '' }}"
                           placeholder="Leave blank for free"
                           class="mt-2 w-full rounded-xl border border-slate-200 px-4 py-3 text-sm">
                </div>

                <div>
                    <label class="text-xs font-bold text-slate-500">
                        Minimum Payout
                    </label>
                    <input type="number"
                           step="0.01"
                           name="minimum_payout"
                           value="{{ $settings->minimum_payout ?? 0 }}"
                           class="mt-2 w-full rounded-xl border border-slate-200 px-4 py-3 text-sm">
                </div>

                <div>
                    <label class="text-xs font-bold text-slate-500">
                        Maximum Payout
                    </label>
                    <input type="number"
                           step="0.01"
                           name="maximum_payout"
                           value="{{ $settings->maximum_payout ?? '' }}"
                           placeholder="No limit"
                           class="mt-2 w-full rounded-xl border border-slate-200 px-4 py-3 text-sm">
                </div>

                <div>
                    <label class="text-xs font-bold text-slate-500">
                        Payout Fee Type
                    </label>
                    <select name="payout_fee_type"
                            class="mt-2 w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm">
                        <option value="free" @selected(($settings->payout_fee_type ?? 'free') === 'free')>
                            Free
                        </option>
                        <option value="fixed" @selected(($settings->payout_fee_type ?? '') === 'fixed')>
                            Fixed
                        </option>
                        <option value="percentage" @selected(($settings->payout_fee_type ?? '') === 'percentage')>
                            Percentage
                        </option>
                    </select>
                </div>

                <div>
                    <label class="text-xs font-bold text-slate-500">
                        Payout Fee
                    </label>
                    <input type="number"
                           step="0.01"
                           name="payout_fee"
                           value="{{ ($settings->payout_fee ?? 0) ?: '' }}"
                           placeholder="Leave blank for free"
                           class="mt-2 w-full rounded-xl border border-slate-200 px-4 py-3 text-sm">
                </div>

                <div>
                    <label class="text-xs font-bold text-slate-500">
                        Payout Cycle
                    </label>
                    <select name="payout_cycle"
                            class="mt-2 w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm">
                        @foreach(['instant', 'manual', 'daily', 'weekly', 'monthly'] as $cycle)
                            <option value="{{ $cycle }}"
                                @selected(($settings->payout_cycle ?? 'instant') === $cycle)>
                                {{ ucfirst($cycle) }}
                            </option>
                        @endforeach
                    </select>
                </div>

            </div>

            <div class="mt-8 flex justify-end">
                <button type="submit"
                        class="rounded-xl bg-blue-600 px-6 py-3 text-sm font-black text-white hover:bg-blue-700">
                    Save Wallet Settings
                </button>
            </div>
        </form>

    </div>
</div>
@endsection
