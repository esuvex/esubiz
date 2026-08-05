<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>

    <meta charset="utf-8">

    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>{{ $title ?? 'Create Website' }} | Esubiz</title>

    @vite(['resources/css/app.css','resources/js/app.js'])

</head>

<body class="bg-slate-100 min-h-screen">

<div class="min-h-screen flex items-center justify-center p-8">

    <div class="w-full max-w-[1500px] min-h-[860px] rounded-3xl overflow-hidden shadow-2xl bg-white grid lg:grid-cols-12">

        <!-- ===================================================== -->
        <!-- LEFT PANEL -->
        <!-- ===================================================== -->

        <aside class="lg:col-span-4 bg-gradient-to-br from-slate-950 via-slate-900 to-blue-900 text-white p-12 flex flex-col">

            <div>

                <div class="inline-flex items-center gap-3">

                    <div class="h-12 w-12 rounded-xl bg-blue-600 flex items-center justify-center font-bold text-xl">

                        E

                    </div>

                    <div>

                        <p class="text-sm uppercase tracking-[0.3em] text-blue-300">

                            ESUBIZ

                        </p>

                        <h2 class="text-2xl font-bold">

                            Website Builder

                        </h2>

                    </div>

                </div>

            </div>

            <div class="mt-16">

                <p class="uppercase text-blue-300 tracking-[0.3em] text-sm">

                    Step {{ $step ?? 1 }} of {{ $steps ?? 7 }}

                </p>

                <h1 class="mt-5 text-5xl font-bold leading-tight">

                    {{ $heading ?? 'What would you like to build today?' }}

                </h1>

                <p class="mt-6 text-lg text-slate-300 leading-8">

                    {{ $description ?? 'Create a professional website in minutes. Every website includes powerful business tools from day one.' }}

                </p>

            </div>

            <div class="mt-10">

                <div class="h-3 rounded-full bg-slate-700 overflow-hidden">

                    <div
                        class="h-full rounded-full bg-blue-500 transition-all duration-500"
                        style="width: {{ (($step ?? 1) / ($steps ?? 7)) * 100 }}%;">
                    </div>

                </div>

            </div>

            <div class="mt-16 space-y-5 text-lg">

                <div class="flex items-center gap-4">
                    <span>✓</span>
                    <span>Website Builder</span>
                </div>

                <div class="flex items-center gap-4">
                    <span>✓</span>
                    <span>CRM Included</span>
                </div>

                <div class="flex items-center gap-4">
                    <span>✓</span>
                    <span>HR Included</span>
                </div>

                <div class="flex items-center gap-4">
                    <span>✓</span>
                    <span>Finance Included</span>
                </div>

                <div class="flex items-center gap-4">
                    <span>✓</span>
                    <span>AI Ready</span>
                </div>

                <div class="flex items-center gap-4">
                    <span>✓</span>
                    <span>Marketplace Ready</span>
                </div>

                <div class="flex items-center gap-4">
                    <span>✓</span>
                    <span>Deploy in under 2 minutes</span>
                </div>

            </div>

            <div class="mt-auto pt-12">

                <p class="text-slate-400">

                    Powered by Esubiz Website OS

                </p>

            </div>

        </aside>

        <!-- ===================================================== -->
        <!-- RIGHT PANEL -->
        <!-- ===================================================== -->

        <main class="lg:col-span-8 p-12 bg-white overflow-y-auto">

            {{ $slot }}

        </main>

    </div>

</div>

</body>

</html>
