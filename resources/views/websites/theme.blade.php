<x-wizard.layout
    title="Choose Template"
    :step="2"
    :steps="8"
    heading="Choose a template">

    <form method="GET"
          action="{{ route('websites.information') }}">

        @foreach($website as $key => $value)
            <input type="hidden"
                   name="{{ $key }}"
                   value="{{ $value }}">
        @endforeach

        <div class="grid gap-8 md:grid-cols-2 xl:grid-cols-3">

            @foreach($themes as $theme)

                <label class="cursor-pointer">

                    <input
                        type="radio"
                        name="theme"
                        value="{{ $theme['id'] }}"
                        class="peer hidden"
                        required>

                    <div class="rounded-3xl border-2 border-slate-200 bg-white transition-all duration-300 hover:-translate-y-1 hover:border-blue-500 hover:shadow-xl peer-checked:border-blue-600 peer-checked:bg-blue-50 peer-checked:shadow-2xl overflow-hidden">

                        <div class="relative">

                            <div class="aspect-[16/10] bg-gradient-to-br from-slate-100 to-slate-200 flex items-center justify-center">

                                <div class="text-6xl">

                                    🖥️

                                </div>

                            </div>

                            <div class="absolute right-5 top-5 hidden h-10 w-10 items-center justify-center rounded-full bg-blue-600 text-lg font-bold text-white peer-checked:flex">

                                ✓

                            </div>

                        </div>

                        <div class="p-8">

                            <h3 class="text-3xl font-bold text-slate-900">

                                {{ $theme['name'] }}

                            </h3>

                            <p class="mt-4 leading-7 text-slate-500">

                                {{ $theme['description'] }}

                            </p>

                            <div class="mt-8 flex items-center justify-between">

                                <span class="rounded-full bg-slate-100 px-4 py-2 text-sm font-semibold text-slate-700 peer-checked:bg-blue-600 peer-checked:text-white">

                                    Live Preview

                                </span>

                                <span class="text-sm text-slate-400">

                                    Click to Select

                                </span>

                            </div>

                        </div>

                    </div>

                </label>

            @endforeach

        </div>

        <div class="mt-12 flex items-center justify-between">

            <a
                href="{{ route('websites.create') }}"
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

        const cards = document.querySelectorAll('input[name="theme"]');
        const button = document.getElementById('continueButton');

        cards.forEach(card => {

            card.addEventListener('change', function () {

                button.disabled = false;
                button.classList.remove('opacity-50');

            });

        });

    </script>

</x-wizard.layout>
