<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>

    <meta charset="utf-8">

    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>{{ $title ?? 'Create Website' }} | Esubiz</title>

    @vite(['resources/css/app.css','resources/js/app.js'])

</head>

<body class="min-h-screen bg-slate-100">

<div class="px-6 py-10 lg:px-10">

    <div class="mx-auto w-full max-w-7xl">

        <div class="overflow-hidden rounded-3xl bg-white shadow-2xl ring-1 ring-slate-200">

            <div class="grid lg:grid-cols-12">

                <!-- LEFT PANEL -->

                <aside class="lg:col-span-4 bg-gradient-to-br from-slate-950 via-slate-900 to-blue-900 text-white">

                    <div class="flex h-full flex-col p-10 xl:p-12">

                        <div class="flex items-center gap-4">

                            <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-blue-600 text-2xl font-bold">

                                E

                            </div>

                            <div>

                                <p class="text-xs uppercase tracking-[0.35em] text-blue-300">

                                    ESUBIZ

                                </p>

                                <h2 class="text-3xl font-bold">

                                    Website Builder

                                </h2>

                            </div>

                        </div>

                        <div class="mt-14">

                            <span class="text-sm font-semibold uppercase tracking-[0.3em] text-blue-300">

                                Step {{ $step ?? 1 }} of {{ $steps ?? 8 }}

                            </span>

                            <h1 class="mt-5 text-4xl font-bold leading-tight xl:text-5xl">

                                {{ $heading ?? 'Create your website' }}

                            </h1>

                            <p class="mt-6 text-lg leading-8 text-slate-300">

                                {{ $description ?? 'Create a complete business website with CRM, Finance, HR, AI and Marketplace built in.' }}

                            </p>

                        </div>

                        <div class="mt-10">

                            <div class="h-3 overflow-hidden rounded-full bg-slate-700">

                                <div
                                    class="h-full rounded-full bg-blue-500 transition-all duration-500"
                                    style="width: {{ (($step ?? 1) / ($steps ?? 8)) * 100 }}%">
                                </div>

                            </div>

                        </div>

                        <div class="mt-12 space-y-5 text-base">

                            <div class="flex items-center gap-3">
                                <span>✓</span>
                                <span>Website Builder</span>
                            </div>

                            <div class="flex items-center gap-3">
                                <span>✓</span>
                                <span>CRM Included</span>
                            </div>

                            <div class="flex items-center gap-3">
                                <span>✓</span>
                                <span>Finance Included</span>
                            </div>

                            <div class="flex items-center gap-3">
                                <span>✓</span>
                                <span>HR Included</span>
                            </div>

                            <div class="flex items-center gap-3">
                                <span>✓</span>
                                <span>AI Assistant</span>
                            </div>

                            <div class="flex items-center gap-3">
                                <span>✓</span>
                                <span>Marketplace Ready</span>
                            </div>

                            <div class="flex items-center gap-3">
                                <span>✓</span>
                                <span>Provisioning in under 2 minutes</span>
                            </div>

                        </div>

                        <div class="mt-auto pt-12">

                            <div class="rounded-2xl border border-white/10 bg-white/5 p-5">

                                <p class="text-sm text-slate-300">

                                    Your website will be automatically deployed with SSL, CRM, Finance, HR, AI and Marketplace enabled.

                                </p>

                            </div>

                            <p class="mt-6 text-sm text-slate-400">

                                Powered by <strong>Esubiz Website OS</strong>

                            </p>

                        </div>

                    </div>

                </aside>

                <!-- RIGHT PANEL -->

                <main class="lg:col-span-8 bg-white">

                    <div class="mx-auto w-full max-w-5xl p-8 lg:p-12 xl:p-14">

                        {{ $slot }}

                    </div>

                </main>

            </div>

        </div>

    </div>

</div>

</body>

</html>
