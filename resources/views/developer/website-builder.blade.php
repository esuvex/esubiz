@extends('admin.layouts.app')

@section('title', 'Website Builder')

@section('content')
<div class="mx-auto w-full max-w-7xl space-y-6 px-4 py-6 sm:px-6 lg:px-8">

    <div>
        <div class="text-xs font-black uppercase tracking-[.16em] text-blue-600">
            Developer
        </div>

        <h1 class="mt-2 text-3xl font-black tracking-tight text-slate-950">
            Website Builder
        </h1>

        <p class="mt-2 max-w-3xl text-sm leading-6 text-slate-500">
            Choose how you want to build and deploy your Esubiz website.
        </p>
    </div>

    <div class="grid gap-5 md:grid-cols-2">

        <a
            href="{{ route('websites.create') }}"
            class="group flex min-h-[260px] flex-col rounded-3xl border border-slate-200 bg-white p-7 shadow-sm transition hover:-translate-y-0.5 hover:border-blue-200 hover:shadow-md"
        >
            <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-blue-50 text-xl font-black text-blue-600">
                ◈
            </div>

            <div class="mt-6">
                <div class="text-[10px] font-black uppercase tracking-[.14em] text-blue-600">
                    Esubiz Hosted
                </div>

                <h2 class="mt-2 text-xl font-black text-slate-950">
                    SaaS Website Builder
                </h2>

                <p class="mt-2 text-sm leading-6 text-slate-500">
                    Create a website hosted and managed on the Esubiz SaaS platform using the standard Website Builder wizard.
                </p>
            </div>

            <div class="mt-auto pt-6 text-sm font-black text-blue-600">
                Open SaaS Builder →
            </div>
        </a>

        <a
            href="{{ route('developer.builder') }}"
            class="group flex min-h-[260px] flex-col rounded-3xl border border-slate-200 bg-white p-7 shadow-sm transition hover:-translate-y-0.5 hover:border-blue-200 hover:shadow-md"
        >
            <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-slate-100 text-xl font-black text-slate-700">
                ◇
            </div>

            <div class="mt-6">
                <div class="text-[10px] font-black uppercase tracking-[.14em] text-slate-500">
                    External Hosting
                </div>

                <h2 class="mt-2 text-xl font-black text-slate-950">
                    Off-server Website Builder
                </h2>

                <p class="mt-2 text-sm leading-6 text-slate-500">
                    Build, configure and compile an Esubiz Core website package for deployment on an external server.
                </p>
            </div>

            <div class="mt-auto pt-6 text-sm font-black text-blue-600">
                Open Off-server Builder →
            </div>
        </a>

    </div>

</div>
@endsection
