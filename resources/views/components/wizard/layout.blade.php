<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>

    <meta charset="utf-8">

    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>{{ $title ?? 'Website Builder' }} | Esubiz</title>

    @vite([
        'resources/css/app.css',
        'resources/js/app.js'
    ])

</head>

<body class="bg-slate-100">

<div class="min-h-screen px-4 py-8 lg:px-8">

    <div class="mx-auto max-w-7xl">

        <div class="overflow-hidden rounded-3xl bg-white shadow-2xl">

            <div class="grid min-h-[860px] lg:grid-cols-12">

                <!-- ========================================= -->
                <!-- LEFT -->
                <!-- ========================================= -->

                <aside class="lg:col-span-3 bg-gradient-to-br from-slate-950 via-slate-900 to-blue-900 text-white">

                    <div class="flex h-full flex-col p-10">

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

                        <div class="mt-16">

                            <span class="inline-flex rounded-full bg-blue-500/20 px-4 py-2 text-xs font-semibold uppercase tracking-[0.35em] text-blue-200">

                                Step {{ $step }} of {{ $steps }}

                            </span>

                            <h1 class="mt-8 text-4xl font-bold leading-tight">

                                {{ $heading }}

                            </h1>

                            @if(($step ?? 1) == 1)

                                <p class="mt-6 text-lg leading-8 text-slate-300">

                                    {{ $description ?? 'Create your website in minutes.' }}

                                </p>

                            @endif

                        </div>

                        <div class="mt-auto">

                            <p class="text-sm text-slate-400">

                                Powered by <strong>Esubiz Website OS</strong>

                            </p>

                        </div>

                    </div>

                </aside>

                <!-- ========================================= -->
                <!-- RIGHT -->
                <!-- ========================================= -->

                <main class="lg:col-span-9 bg-slate-50">

                    <div class="p-10 lg:p-12">

                        <!-- Progress -->
<div class="mb-10">

    <div class="flex items-center justify-between">

        @for($i = 1; $i <= $steps; $i++)

            <div class="flex flex-1 items-center">

                <div
                    class="flex h-11 w-11 items-center justify-center rounded-full border-2 text-sm font-bold transition-all
                    {{ $i < $step
                        ? 'border-blue-600 bg-blue-600 text-white'
                        : ($i == $step
                            ? 'border-blue-600 bg-white text-blue-600'
                            : 'border-slate-300 bg-white text-slate-400') }}">

                    @if($i < $step)

                        ✓

                    @else

                        {{ $i }}

                    @endif

                </div>

                @if($i < $steps)

                    <div
                        class="mx-3 h-1 flex-1 rounded-full
                        {{ $i < $step ? 'bg-blue-600' : 'bg-slate-200' }}">
                    </div>

                @endif

            </div>

        @endfor

    </div>

</div>

<div class="rounded-3xl bg-white p-8 shadow-sm ring-1 ring-slate-200">

    {{ $slot }}

</div>

</div>

</main>
            </div>

        </div>

    </div>

</div>

</body>

</html>
