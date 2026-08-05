<x-wizard.layout
    title="Create Website"
    :step="1"
    :steps="7"
    heading="What would you like to build today?"
    description="Choose the type of website you want to create. Every website includes Esubiz Core, CRM, Finance, HR, AI integration, Marketplace support and high-performance hosting.">

    <form method="GET" action="{{ route('websites.theme') }}" id="wizardForm">

        <div class="max-w-6xl mx-auto">

            <div class="mb-10">

                <h2 class="text-4xl font-bold text-slate-900">

                    Choose Website Type

                </h2>

                <p class="mt-3 text-lg text-slate-500">

                    Select the kind of website you want to build.

                </p>

            </div>

            <div class="grid gap-6 md:grid-cols-2 xl:grid-cols-3">

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
                            'desc' => 'Schools, colleges and educational institutions.',
                            'count' => '12 Templates'
                        ],

                        [
                            'id' => 'church',
                            'icon' => '⛪',
                            'title' => 'Church',
                            'desc' => 'Churches, ministries and faith organizations.',
                            'count' => '10 Templates'
                        ],

                        [
                            'id' => 'hotel',
                            'icon' => '🏨',
                            'title' => 'Hotel',
                            'desc' => 'Hotels, resorts and hospitality businesses.',
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

                @foreach($types as $type)

                    <label
                        class="group relative cursor-pointer rounded-3xl border-2 border-slate-200 bg-white p-8 transition-all duration-300 hover:-translate-y-1 hover:border-blue-600 hover:shadow-2xl">

                        <input
                            type="radio"
                            name="type"
                            value="{{ $type['id'] }}"
                            class="peer sr-only"
                            required>

                        <div class="absolute right-6 top-6 hidden h-9 w-9 items-center justify-center rounded-full bg-blue-600 text-white peer-checked:flex">

                            ✓

                        </div>

                        <div class="text-6xl">

                            {{ $type['icon'] }}

                        </div>

                        <h3 class="mt-6 text-3xl font-bold text-slate-900">

                            {{ $type['title'] }}

                        </h3>

                        <p class="mt-4 leading-7 text-slate-500">

                            {{ $type['desc'] }}

                        </p>

                        <div class="mt-8 flex items-center justify-between">

                            <span class="rounded-full bg-blue-50 px-4 py-2 text-sm font-semibold text-blue-700">

                                {{ $type['count'] }}

                            </span>

                            <span class="text-sm text-slate-400">

                                Click to Select

                            </span>

                        </div>

                    </label>

                @endforeach

            </div>

            <div class="mt-12 flex items-center justify-between">

                <a
                    href="{{ route('admin.dashboard') }}"
                    class="rounded-2xl border border-slate-300 bg-white px-8 py-4 font-semibold transition hover:bg-slate-100">

                    ← Back

                </a>

                <button
                    id="continueButton"
                    type="submit"
                    disabled
                    class="rounded-2xl bg-blue-600 px-10 py-4 font-semibold text-white opacity-50 transition hover:bg-blue-700 disabled:cursor-not-allowed">

                    Continue →

                </button>

            </div>

        </div>

    </form>

    <script>

        document.querySelectorAll('input[name="type"]').forEach(function(input){

            input.addEventListener('change', function(){

                document.getElementById('continueButton').disabled = false;

                document.getElementById('continueButton').classList.remove('opacity-50');

            });

        });

    </script>

</x-wizard.layout>
