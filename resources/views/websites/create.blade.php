<x-wizard.layout
    title="Create Website"
    :step="1"
    :steps="8"
    heading="What would you like to build today?"
    description="Set up your website in under 2 minutes. Choose a website category to get started.">

    @php

        $types = [

            [
                'id' => 'business',
                'icon' => '🏢',
                'title' => 'Business',
                'desc' => 'Professional websites for companies and brands.',
                'count' => '24 Templates'
            ],

            [
                'id' => 'ecommerce',
                'icon' => '🛒',
                'title' => 'Online Store',
                'desc' => 'Sell products and receive payments online.',
                'count' => '18 Templates'
            ],

            [
                'id' => 'school',
                'icon' => '🎓',
                'title' => 'School',
                'desc' => 'Schools and educational institutions.',
                'count' => '12 Templates'
            ],

            [
                'id' => 'church',
                'icon' => '⛪',
                'title' => 'Church',
                'desc' => 'Churches and faith organizations.',
                'count' => '10 Templates'
            ],

            [
                'id' => 'hotel',
                'icon' => '🏨',
                'title' => 'Hotel',
                'desc' => 'Hotels and hospitality businesses.',
                'count' => '9 Templates'
            ],

            [
                'id' => 'restaurant',
                'icon' => '🍽️',
                'title' => 'Restaurant',
                'desc' => 'Restaurants, cafés and food businesses.',
                'count' => '11 Templates'
            ],

        ];

    @endphp

    <form method="GET"
          action="{{ route('websites.theme') }}">

        <div class="grid gap-8 md:grid-cols-2 xl:grid-cols-3">

            @foreach($types as $type)

                <label class="website-card group cursor-pointer">

                    <input
                        type="radio"
                        name="type"
                        value="{{ $type['id'] }}"
                        class="peer hidden"
                        required>

                    <div class="rounded-3xl border-2 border-slate-200 bg-white p-8 transition-all duration-300 hover:-translate-y-1 hover:border-blue-500 hover:shadow-xl peer-checked:border-blue-600 peer-checked:bg-blue-50 peer-checked:shadow-2xl">

                        <div class="flex items-start justify-between">

                            <div class="text-6xl">

                                {{ $type['icon'] }}

                            </div>

                            <div class="hidden h-10 w-10 items-center justify-center rounded-full bg-blue-600 text-lg font-bold text-white peer-checked:flex">

                                ✓

                            </div>

                        </div>

                        <h3 class="mt-8 text-3xl font-bold text-slate-900">

                            {{ $type['title'] }}

                        </h3>

                        <p class="mt-4 leading-7 text-slate-500">

                            {{ $type['desc'] }}

                        </p>

                        <div class="mt-8 flex items-center justify-between">

                            <span class="rounded-full bg-slate-100 px-4 py-2 text-sm font-semibold text-slate-700 peer-checked:bg-blue-600 peer-checked:text-white">

                                {{ $type['count'] }}

                            </span>

                            <span class="text-sm font-medium text-slate-400">

                                Click to Select

                            </span>

                        </div>

                    </div>

                </label>

            @endforeach

        </div>

        <div class="mt-12 flex items-center justify-between">

            <a href="{{ route('admin.dashboard') }}"
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

        const cards = document.querySelectorAll('input[name="type"]');
        const button = document.getElementById('continueButton');

        cards.forEach(card => {

            card.addEventListener('change', function () {

                button.disabled = false;
                button.classList.remove('opacity-50');

            });

        });

    </script>

</x-wizard.layout>
