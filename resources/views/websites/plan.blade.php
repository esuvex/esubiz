<x-wizard.layout
    title="Choose Your Plan"
    :step="4"
    :steps="8"
    heading="Choose your plan">

    <form method="GET"
          action="{{ route('websites.address') }}">

        @foreach($website as $key => $value)
            <input type="hidden"
                   name="{{ $key }}"
                   value="{{ $value }}">
        @endforeach

        <div class="grid gap-8 lg:grid-cols-3">

            @foreach($plans as $plan)

                <label class="cursor-pointer">

                    <input
                        type="radio"
                        name="plan_id"
                        value="{{ $plan['id'] }}"
                        class="peer hidden"
                        required>

                    <div class="relative h-full rounded-3xl border-2 border-slate-200 bg-white p-8 transition-all duration-300 hover:-translate-y-1 hover:border-blue-500 hover:shadow-xl peer-checked:border-blue-600 peer-checked:bg-blue-50 peer-checked:shadow-2xl">

                        @if(!empty($plan['popular']))

                            <div class="absolute right-6 top-6 rounded-full bg-blue-600 px-4 py-2 text-xs font-bold uppercase tracking-wider text-white">

                                Most Popular

                            </div>

                        @endif

                        <div class="flex items-start justify-between">

                            <div>

                                <h2 class="text-3xl font-bold text-slate-900">

                                    {{ $plan['name'] }}

                                </h2>

                                <p class="mt-4 text-5xl font-bold text-blue-600">

                                    {{ $plan['price'] }}

                                </p>

                            </div>

                            <div class="hidden h-10 w-10 items-center justify-center rounded-full bg-blue-600 text-lg font-bold text-white peer-checked:flex">

                                ✓

                            </div>

                        </div>

                        <p class="mt-6 leading-7 text-slate-500">

                            {{ $plan['description'] }}

                        </p>

                        <div class="mt-8 space-y-4">

                            @foreach($plan['features'] as $feature)

                                <div class="flex items-center gap-3">

                                    <span class="flex h-7 w-7 items-center justify-center rounded-full bg-blue-100 text-sm text-blue-600">

                                        ✓

                                    </span>

                                    <span class="text-slate-700">

                                        {{ $feature }}

                                    </span>

                                </div>

                            @endforeach

                        </div>

                    </div>

                </label>

            @endforeach

        </div>

        <div class="mt-12 flex items-center justify-between">

            <a
                href="{{ route('websites.information', request()->query()) }}"
                class="rounded-2xl border border-slate-300 bg-white px-8 py-4 font-semibold hover:bg-slate-100">

                ← Back

            </a>

            <button
                id="continueButton"
                type="submit"
                disabled
                class="rounded-2xl bg-blue-600 px-10 py-4 font-semibold text-white opacity-50 transition disabled:cursor-not-allowed">

                Continue →

            </button>

        </div>

    </form>

    <script>

        const plans = document.querySelectorAll('input[name="plan_id"]');
        const button = document.getElementById('continueButton');

        plans.forEach(plan => {

            plan.addEventListener('change', function () {

                button.disabled = false;
                button.classList.remove('opacity-50');

            });

        });

    </script>

</x-wizard.layout>
