@extends('admin.layouts.app')

@section('title', 'My Websites')

@section('content')

<div class="space-y-8">

    <div class="rounded-3xl bg-gradient-to-br from-slate-950 via-slate-900 to-blue-900 p-8 text-white shadow-2xl">

        <div class="flex flex-col gap-6 lg:flex-row lg:items-center lg:justify-between">

            <div>
                <p class="text-sm font-semibold uppercase tracking-[0.3em] text-blue-300">
                    ESUBIZ
                </p>

                <h1 class="mt-3 text-4xl font-bold">
                    My Websites
                </h1>

                <p class="mt-3 max-w-2xl text-slate-300">
                    View and manage all your websites from one place.
                </p>
            </div>



        </div>

    </div>


    @if($websites->count())

        <div class="grid gap-6 md:grid-cols-2 xl:grid-cols-3">

            @foreach($websites as $website)

                <div class="rounded-3xl bg-white p-7 shadow-sm ring-1 ring-slate-200">

                    <div class="flex items-start justify-between gap-4">

                        <div class="min-w-0">

                            <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">
                                Website Identifier
                            </p>

                            <h2 class="mt-2 truncate text-2xl font-bold text-slate-900">
                                {{ $website->name }}
                            </h2>

                        </div>

                        <span class="shrink-0 rounded-full bg-green-100 px-3 py-1 text-xs font-semibold text-green-700">
                            {{ ucfirst($website->status) }}
                        </span>

                    </div>

                    <div class="mt-6 space-y-3 text-sm">

                        <div class="flex justify-between gap-4">
                            <span class="text-slate-500">Website Type</span>
                            <span class="font-semibold text-slate-800">
                                {{ ucfirst($website->type ?? 'Business') }}
                            </span>
                        </div>

                        @if($website->subdomain)

                            <div>
                                <span class="text-slate-500">Esubiz Address</span>

                                <p class="mt-1 break-all font-medium text-blue-600">
                                    https://{{ $website->subdomain }}.esubiz.com
                                </p>
                            </div>

                        @endif

                        @if($website->domain)

                            <div>
                                <span class="text-slate-500">Custom Domain</span>

                                <p class="mt-1 break-all font-medium text-slate-800">
                                    {{ $website->domain }}
                                </p>
                            </div>

                        @endif

                    </div>

                    <div class="mt-7 grid grid-cols-2 gap-3 border-t border-slate-100 pt-6">

                        @if($website->subdomain)

                            <a
                                href="https://{{ $website->subdomain }}.esubiz.com"
                                target="_blank"
                                rel="noopener"
                                class="inline-flex items-center justify-center rounded-xl bg-blue-600 px-4 py-3 text-sm font-semibold text-white hover:bg-blue-700"
                            >
                                Visit Website
                            </a>

                            <a
                                href="#"
                                class="inline-flex items-center justify-center rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm font-semibold text-slate-700 hover:bg-slate-50"
                            >
                                Website Dashboard
                            </a>

                        @endif

                        <a
                            href="{{ route('user.websites.edit', $website) }}"
                            class="inline-flex items-center justify-center rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm font-semibold text-slate-700 hover:bg-slate-50"
                        >
                            Edit Website
                        </a>

                        <a
                            href="#"
                            class="inline-flex items-center justify-center rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm font-semibold text-slate-700 hover:bg-slate-50"
                        >
                            Add Features
                        </a>

                    </div>

                </div>

            @endforeach

        </div>

    @else

        <div class="rounded-3xl bg-white p-12 text-center shadow-sm ring-1 ring-slate-200">

            <h2 class="text-2xl font-bold text-slate-900">
                You don't have a website yet
            </h2>

            <p class="mx-auto mt-3 max-w-lg text-slate-500">
                Create your first Esubiz website and manage it from this page.
            </p>

            <a
                href="{{ route('websites.create') }}"
                class="mt-7 inline-flex items-center rounded-2xl bg-blue-600 px-7 py-4 font-semibold text-white hover:bg-blue-700"
            >
                Create Your First Website
            </a>

        </div>

    @endif

</div>

@endsection
