@extends('admin.layouts.app')

@section('title', 'Buy Credits')

@section('content')
<div class="mx-auto max-w-5xl space-y-6 px-4 py-6" data-credit-volume>
    <div class="rounded-3xl bg-gradient-to-r from-blue-700 to-blue-500 p-7 text-white shadow-lg">
        <p class="text-xs font-black uppercase tracking-widest text-blue-100">Esubiz Marketplace</p>
        <h1 class="mt-2 text-3xl font-black">Buy credits</h1>
        <p class="mt-2 text-sm text-blue-100">
            Choose a Core website and enter the credits you need. Pricing is calculated by Central.
        </p>
    </div>

    <div class="flex flex-wrap gap-2" aria-label="Deployment">
        @foreach(['saas' => 'SaaS websites', 'off_server' => 'Off-server websites'] as $value => $label)
            <a href="{{ route('marketplace.credits.index', ['deployment' => $value]) }}"
               class="rounded-xl border px-5 py-3 text-sm font-bold transition
               {{ $deployment === $value
                   ? 'border-blue-600 bg-blue-600 text-white'
                   : 'border-slate-200 bg-white text-slate-600 hover:border-blue-400 hover:text-blue-700' }}">
                {{ $label }}
            </a>
        @endforeach
    </div>

    @if($errors->any())
        <div class="rounded-2xl border border-red-200 bg-red-50 p-4 text-sm text-red-700">
            {{ $errors->first() }}
        </div>
    @endif

    <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
        @if($websites->isEmpty())
            <p class="text-sm text-slate-600">
                No active {{ $deployment === 'saas' ? 'SaaS' : 'off-server' }}
                website is linked to this account.
            </p>
        @else
            <form method="POST"
                  action="{{ route('marketplace.credits.store', ['deployment' => $deployment]) }}"
                  class="space-y-6" data-credit-form>
                @csrf

                <div class="grid gap-5 md:grid-cols-2">
                    <label class="block text-sm font-bold text-slate-700">
                        Core website
                        <select name="website_id" required
                                class="mt-2 w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm focus:border-blue-500 focus:ring-blue-500">
                            <option value="">Select a website</option>
                            @foreach($websites as $website)
                                <option value="{{ $website->id }}"
                                        @selected((string) old('website_id', $selectedWebsiteId) === (string) $website->id)>
                                    {{ $website->name }}
                                    ({{ $website->domain ?: $website->subdomain ?: '#'.$website->id }})
                                </option>
                            @endforeach
                        </select>
                    </label>

                    <label class="block text-sm font-bold text-slate-700">
                        Credit product
                        <select name="credit_type" required
                                class="mt-2 w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm focus:border-blue-500 focus:ring-blue-500">
                            <option value="">Select credits</option>
                            @foreach($creditTypes as $type => $label)
                                <option value="{{ $type }}" @selected(old('credit_type', $selectedCreditType) === $type)>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                    </label>
                </div>

                <label class="block max-w-md text-sm font-bold text-slate-700">
                    Number of credits
                    <input name="quantity" type="number" min="1" max="100000000"
                           value="{{ old('quantity') }}" required
                           class="mt-2 w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm focus:border-blue-500 focus:ring-blue-500">
                </label>

                <div class="rounded-2xl border border-blue-100 bg-blue-50 p-5">
                    <div class="flex flex-wrap items-end justify-between gap-4">
                        <div>
                            <p class="text-xs font-black uppercase tracking-wider text-blue-600">Your quote</p>
                            <p class="mt-2 text-sm text-slate-600" data-credit-unit>
                                Enter a quantity to see your volume price.
                            </p>
                        </div>
                        <div class="text-right">
                            <p class="text-xs font-bold text-slate-500">Total</p>
                            <p class="text-3xl font-black text-blue-700" data-credit-total>—</p>
                        </div>
                    </div>
                    <p class="mt-3 text-xs text-slate-500" data-credit-message aria-live="polite"></p>
                </div>

                <button type="submit" disabled data-credit-submit
                        class="w-full rounded-2xl bg-blue-600 px-5 py-4 text-sm font-black text-white transition hover:bg-blue-700 disabled:cursor-not-allowed disabled:opacity-40">
                    Continue to Payment
                </button>
            </form>
        @endif
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const root = document.querySelector('[data-credit-volume]');
    const form = root?.querySelector('[data-credit-form]');
    if (!form) return;

    const type = form.querySelector('[name="credit_type"]');
    const website = form.querySelector('[name="website_id"]');
    const quantity = form.querySelector('[name="quantity"]');
    const unit = form.querySelector('[data-credit-unit]');
    const total = form.querySelector('[data-credit-total]');
    const message = form.querySelector('[data-credit-message]');
    const submit = form.querySelector('[data-credit-submit]');
    const endpoint = @json(route('marketplace.credit-volume.quote'));
    const deployment = @json($deployment);
    let requestId = 0;
    let timer;

    function reset() {
        submit.disabled = true;
        unit.textContent = 'Enter a quantity to see your volume price.';
        total.textContent = '—';
        message.textContent = '';
    }

    async function quote() {
        const current = ++requestId;
        reset();
        const count = Number(quantity.value);
        if (!website.value || !type.value ||
            !Number.isSafeInteger(count) || count < 1 || count > 100000000) return;

        message.textContent = 'Calculating your price…';
        try {
            const url = new URL(endpoint, window.location.href);
            url.searchParams.set('credit_type', type.value);
            url.searchParams.set('quantity', String(count));
            url.searchParams.set('deployment_type', deployment);
            const response = await fetch(url, {
                headers: {'Accept': 'application/json'}
            });
            const result = await response.json();
            if (current !== requestId) return;
            if (!response.ok) throw new Error(result.message || 'Price is unavailable.');

            const format = value => new Intl.NumberFormat(undefined, {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            }).format(Number(value));
            unit.textContent = result.currency + ' ' +
                format(result.unit_price) + ' per credit';
            total.textContent = result.currency + ' ' + format(result.total);
            message.textContent = 'Final price is verified again when checkout starts.';
            submit.disabled = false;
        } catch (error) {
            if (current === requestId) message.textContent = error.message;
        }
    }

    for (const input of [type, website, quantity]) {
        input.addEventListener('input', function () {
            ++requestId;
            reset();
            clearTimeout(timer);
            timer = setTimeout(quote, 250);
        });
        input.addEventListener('change', quote);
    }
    quote();
});
</script>
@endsection
