<section id="trusted" class="bg-white py-20 border-y border-slate-200">

    <div class="max-w-7xl mx-auto px-6 lg:px-8">

        <!-- Heading -->

        <div class="text-center max-w-3xl mx-auto">

            <span class="inline-flex items-center rounded-full bg-blue-50 text-blue-700 px-4 py-2 text-sm font-semibold">

                Trusted Business Platform

            </span>

            <h2 class="mt-6 text-4xl font-black tracking-tight text-slate-900">

                Built for modern businesses.

            </h2>

            <p class="mt-5 text-lg text-slate-600 leading-8">

                Whether you're launching your first business, scaling an established company or
                managing multiple brands, Esubiz gives you one platform to build, manage and grow.

            </p>

        </div>


        <!-- Statistics -->

        <div class="grid grid-cols-2 lg:grid-cols-4 gap-6 mt-16">

            <div class="rounded-3xl border border-slate-200 bg-slate-50 p-8 text-center">

                <div class="text-4xl font-black text-slate-900">

                    All-in-One

                </div>

                <p class="mt-3 text-slate-600">

                    Business Platform

                </p>

            </div>


            <div class="rounded-3xl border border-slate-200 bg-slate-50 p-8 text-center">

                <div class="text-4xl font-black text-slate-900">

                    24/7

                </div>

                <p class="mt-3 text-slate-600">

                    Cloud Access

                </p>

            </div>


            <div class="rounded-3xl border border-slate-200 bg-slate-50 p-8 text-center">

                <div class="text-4xl font-black text-slate-900">

                    AI

                </div>

                <p class="mt-3 text-slate-600">

                    Productivity Tools

                </p>

            </div>


            <div class="rounded-3xl border border-slate-200 bg-slate-50 p-8 text-center">

                <div class="text-4xl font-black text-slate-900">

                    Secure

                </div>

                <p class="mt-3 text-slate-600">

                    Cloud Infrastructure

                </p>

            </div>

        </div>


        <!-- Business Types -->

        <div class="mt-20">

            <h3 class="text-center text-sm uppercase tracking-[0.25em] text-slate-500 font-semibold">

                Designed For

            </h3>

            <div class="mt-10 flex flex-wrap justify-center gap-4">

                @foreach([
                    'Entrepreneurs',
                    'Small Businesses',
                    'Startups',
                    'Agencies',
                    'Consultants',
                    'Retail Stores',
                    'Restaurants',
                    'Hotels',
                    'Schools',
                    'Churches',
                    'NGOs',
                    'Professional Services'
                ] as $business)

                    <span class="px-6 py-3 rounded-full border border-slate-200 bg-white shadow-sm text-slate-700 font-medium hover:border-blue-500 hover:text-blue-600 transition">

                        {{ $business }}

                    </span>

                @endforeach

            </div>

        </div>


        <!-- Trust Banner -->

        <div class="mt-20 rounded-3xl bg-gradient-to-r from-slate-900 to-slate-800 text-white p-10 lg:p-14">

            <div class="grid lg:grid-cols-3 gap-10 items-center">

                <div class="lg:col-span-2">

                    <h3 class="text-3xl font-black">

                        One login. Every business tool.

                    </h3>

                    <p class="mt-4 text-slate-300 text-lg leading-8">

                        Stop switching between disconnected software.
                        Build your website, manage customers, sell online,
                        automate daily tasks and grow your business from one workspace.

                    </p>

                </div>

                <div class="text-center lg:text-right">

                    <a href="{{ route('register') }}"
                       class="inline-flex items-center justify-center rounded-xl bg-amber-400 hover:bg-amber-300 text-slate-900 font-bold px-8 py-4 transition">

                        Start Free →

                    </a>

                </div>

            </div>

        </div>

    </div>

</section>
