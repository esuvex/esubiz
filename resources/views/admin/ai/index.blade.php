@extends('admin.layouts.app')

@section('title', 'Esubiz AI')

@section('content')

<div class="mx-auto max-w-7xl space-y-8">

    <div
        class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between"
    >

        <div>

            <div
                class="text-xs font-black uppercase tracking-[.16em] text-blue-600"
            >
                Central AI Control
            </div>

            <h1
                class="mt-2 text-3xl font-black text-slate-900"
            >
                Esubiz AI
            </h1>

            <p
                class="mt-2 max-w-4xl text-sm leading-6 text-slate-500"
            >
                Manage the central AI system used by Esubiz Admin,
                Developers, tenant websites and future off-server
                Esubiz products.
            </p>

        </div>

    </div>


    {{-- SUMMARY --}}
    <div
        class="grid gap-5 md:grid-cols-2 xl:grid-cols-4"
    >

        <div
            class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm"
        >
            <div
                class="text-xs font-black uppercase tracking-wide text-slate-400"
            >
                Official AI Avatars
            </div>

            <div
                class="mt-3 text-3xl font-black text-slate-900"
            >
                {{ $personas->count() }}
            </div>
        </div>


        <div
            class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm"
        >
            <div
                class="text-xs font-black uppercase tracking-wide text-slate-400"
            >
                AI Services
            </div>

            <div
                class="mt-3 text-3xl font-black text-slate-900"
            >
                {{ count($services) }}
            </div>
        </div>


        <div
            class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm"
        >
            <div
                class="text-xs font-black uppercase tracking-wide text-slate-400"
            >
                Registered Capabilities
            </div>

            <div
                class="mt-3 text-3xl font-black text-slate-900"
            >
                {{ $capabilities->count() }}
            </div>
        </div>


        <div
            class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm"
        >
            <div
                class="text-xs font-black uppercase tracking-wide text-slate-400"
            >
                AI Engine
            </div>

            <div
                class="mt-3 text-xl font-black text-emerald-700"
            >
                Central
            </div>

            <p
                class="mt-2 text-xs leading-5 text-slate-500"
            >
                One AI engine for central, developers and tenant websites.
            </p>
        </div>

    </div>


    {{-- AI SERVICES --}}
    <section>

        <div
            class="text-xs font-black uppercase tracking-[.16em] text-blue-600"
        >
            AI Services
        </div>

        <h2
            class="mt-2 text-2xl font-black text-slate-900"
        >
            Central Service Library
        </h2>

        <p
            class="mt-2 max-w-4xl text-sm leading-6 text-slate-500"
        >
            These services plug into the same Esubiz AI engine.
            Individual products only register what AI can do.
        </p>


        <div
            class="mt-6 grid grid-cols-1 gap-5 xl:grid-cols-4"
        >

            @foreach($services as $service)

                <article
                    class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm"
                >

                    <div
                        class="text-xs font-black uppercase tracking-wide text-blue-600"
                    >
                        Registered Service
                    </div>

                    <h3
                        class="mt-3 text-lg font-black text-slate-900"
                    >
                        {{ $service['label'] }}
                    </h3>

                    <p
                        class="mt-2 text-sm leading-6 text-slate-500"
                    >
                        {{ $service['description'] }}
                    </p>

                </article>

            @endforeach

        </div>

    </section>


    {{-- CAPABILITY LIBRARY --}}
    <section>

        <div
            class="text-xs font-black uppercase tracking-[.16em] text-blue-600"
        >
            Capability Library
        </div>

        <h2
            class="mt-2 text-2xl font-black text-slate-900"
        >
            Registered AI Functions
        </h2>


        <div
            class="mt-6 grid gap-5 lg:grid-cols-2"
        >

            @forelse($capabilities as $capability)

                <article
                    class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm"
                >

                    <div
                        class="flex items-start justify-between gap-4"
                    >

                        <div>

                            <div
                                class="text-xs font-black uppercase tracking-wide text-slate-400"
                            >
                                {{ $capability['key'] }}
                            </div>

                            <h3
                                class="mt-2 text-lg font-black text-slate-900"
                            >
                                {{ $capability['label'] }}
                            </h3>

                        </div>


                        @if($capability['version'])

                            <span
                                class="rounded-full bg-slate-100 px-3 py-1 text-[10px] font-black text-slate-500"
                            >
                                v{{ $capability['version'] }}
                            </span>

                        @endif

                    </div>


                    @if(!empty($capability['functions']))

                        <div
                            class="mt-5 flex flex-wrap gap-2"
                        >

                            @foreach(
                                $capability['functions']
                                as $function
                            )

                                <span
                                    class="rounded-full bg-blue-50 px-3 py-1.5 text-xs font-bold text-blue-700"
                                >
                                    {{ $function }}
                                </span>

                            @endforeach

                        </div>

                    @endif

                </article>

            @empty

                <div
                    class="rounded-3xl border border-dashed border-slate-300 bg-white p-8 text-sm text-slate-500"
                >
                    No AI capabilities are registered yet.
                </div>

            @endforelse

        </div>

    </section>


    {{-- OFFICIAL AI AVATARS --}}
    <section>

        <div
            class="text-xs font-black uppercase tracking-[.16em] text-blue-600"
        >
            Official AI Avatars
        </div>

        <h2
            class="mt-2 text-2xl font-black text-slate-900"
        >
            Esubiz AI Avatars
        </h2>

        <p
            class="mt-2 max-w-4xl text-sm leading-6 text-slate-500"
        >
            These are the official Esubiz AI identities users and
            developers can choose from. They all use the same
            central AI engine.
        </p>


        <div
            class="mt-6 grid gap-5 md:grid-cols-2 xl:grid-cols-4"
        >

            @forelse($personas as $persona)

                <article
                    class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm"
                >

                    <div
                        class="flex items-center gap-4"
                    >

                        <div
                            class="h-16 w-16 shrink-0 overflow-hidden rounded-full bg-blue-50"
                        >

                            @if($persona->avatarUrl())

                                <img
                                    src="{{ $persona->avatarUrl() }}"
                                    alt="{{ $persona->name }}"
                                    class="h-full w-full object-cover"
                                >

                            @else

                                <div
                                    class="flex h-full w-full items-center justify-center font-black text-blue-600"
                                >
                                    AI
                                </div>

                            @endif

                        </div>


                        <div>

                            <h3
                                class="text-lg font-black text-slate-900"
                            >
                                {{ $persona->name }}
                            </h3>

                            <div
                                class="mt-1 text-xs font-bold text-slate-400"
                            >
                                {{
                                    $persona->is_active
                                        ? 'Active'
                                        : 'Inactive'
                                }}
                            </div>

                        </div>

                    </div>


                    @if($persona->description)

                        <p
                            class="mt-4 text-sm leading-6 text-slate-500"
                        >
                            {{ $persona->description }}
                        </p>

                    @endif


                    @if($persona->is_default)

                        <span
                            class="mt-4 inline-flex rounded-full bg-blue-50 px-3 py-1 text-[10px] font-black uppercase text-blue-600"
                        >
                            Default Avatar
                        </span>

                    @endif

                </article>

            @empty

                <div
                    class="col-span-full rounded-3xl border border-dashed border-slate-300 bg-white p-8 text-center"
                >

                    <strong
                        class="text-slate-900"
                    >
                        No official AI avatars yet.
                    </strong>

                    <p
                        class="mt-2 text-sm text-slate-500"
                    >
                        Avatar creation controls will be added next.
                    </p>

                </div>

            @endforelse

        </div>

    </section>


    {{-- CENTRAL ENGINE --}}
    <section
        class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm"
    >

        <div
            class="text-xs font-black uppercase tracking-[.16em] text-blue-600"
        >
            Central Engine
        </div>

        <h2
            class="mt-2 text-xl font-black text-slate-900"
        >
            Provider, Credits & Routing
        </h2>

        <p
            class="mt-2 max-w-4xl text-sm leading-6 text-slate-500"
        >
            Model routing, AI credit pricing, provider credentials
            and usage accounting remain centralized here. Generated
            user/developer media stays in the requesting workspace.
        </p>

    </section>

</div>

@endsection
