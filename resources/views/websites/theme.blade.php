<x-wizard.layout
    title="Choose Theme"
    :step="2"
    :steps="7"
    heading="Choose a Template"
    description="Select a professionally designed template for your website.">

    <form method="GET" action="{{ route('websites.plan') }}">

        <input type="hidden" name="type" value="{{ $website['type'] }}">

        <div class="max-w-7xl mx-auto">

            <div class="mb-10">

                <h2 class="text-4xl font-bold text-slate-900">

                    Select Your Template

                </h2>

                <p class="mt-3 text-lg text-slate-500">

                    Every template is fully customizable after deployment.

                </p>

            </div>

            <div class="grid gap-8 lg:grid-cols-3">

                @foreach($themes as $theme)

                    <label class="group cursor-pointer">

                        <input
                            type="radio"
                            name="theme"
                            value="{{ $theme['id'] }}"
                            class="peer hidden"
                            required>

                        <div class="overflow-hidden rounded-3xl border-2 border-slate-200 bg-white transition-all duration-300 peer-checked:border-blue-600 peer-checked:shadow-2xl hover:-translate-y-1 hover:border-blue-400">

                            <div class="flex aspect-[16/10] items-center justify-center bg-slate-100 text-6xl">

                                🖥️

                            </div>

                            <div class="p-8">

                                <h3 class="text-2xl font-bold">

                                    {{ $theme['name'] }}

                                </h3>

                                <p class="mt-3 text-slate-500">

                                    {{ $theme['description'] }}

                                </p>

                                <div class="mt-6 flex items-center justify-between">

                                    <span class="rounded-full bg-blue-50 px-4 py-2 text-sm font-semibold text-blue-700">

                                        Responsive

                                    </span>

                                    <span class="text-blue-600 font-semibold">

                                        Select →

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
                    disabled
                    type="submit"
                    class="rounded-2xl bg-blue-600 px-10 py-4 font-semibold text-white opacity-50 disabled:cursor-not-allowed">

                    Continue →

                </button>

            </div>

        </div>

    </form>

    <script>
        document.querySelectorAll('input[name="theme"]').forEach(function(input){
            input.addEventListener('change', function(){
                const btn = document.getElementById('continueButton');
                btn.disabled = false;
                btn.classList.remove('opacity-50');
            });
        });
    </script>

</x-wizard.layout>
