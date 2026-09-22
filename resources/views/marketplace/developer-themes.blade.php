@extends('admin.layouts.app')

@section('title', 'Themes')

@section('content')
<div class="mx-auto w-full max-w-7xl space-y-6 px-4 py-6 sm:px-6 lg:px-8">

    <div>
        <div class="text-xs font-black uppercase tracking-[.16em] text-blue-600">
            Developer Marketplace
        </div>

        <h1 class="mt-2 text-3xl font-black tracking-tight text-slate-950">
            Themes
        </h1>

        <p class="mt-2 max-w-3xl text-sm leading-6 text-slate-500">
            Choose the Esubiz Theme Marketplace for your deployment.
        </p>
    </div>

    <div class="grid gap-5 md:grid-cols-2">

        <a
            href="{{ route('marketplace.themes') }}"
            class="group rounded-3xl border border-slate-200 bg-white p-7 shadow-sm transition hover:-translate-y-0.5 hover:border-blue-200 hover:shadow-md"
        >
            <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-blue-50 text-xl font-black text-blue-600">
                ◈
            </div>

            <h2 class="mt-6 text-xl font-black text-slate-950">
                SaaS Themes
            </h2>

            <p class="mt-2 text-sm leading-6 text-slate-500">
                Browse Themes for websites hosted on the Esubiz SaaS platform.
            </p>

            <div class="mt-6 text-sm font-black text-blue-600">
                Browse SaaS Themes →
            </div>
        </a>

        <a
            href="{{ route('developer.marketplace.themes.off-server') }}"
            class="group rounded-3xl border border-slate-200 bg-white p-7 shadow-sm transition hover:-translate-y-0.5 hover:border-blue-200 hover:shadow-md"
        >
            <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-slate-100 text-xl font-black text-slate-700">
                ◇
            </div>

            <h2 class="mt-6 text-xl font-black text-slate-950">
                Off-server Themes
            </h2>

            <p class="mt-2 text-sm leading-6 text-slate-500">
                Browse Theme licenses for Esubiz Core installations hosted outside Esubiz servers.
            </p>

            <div class="mt-6 text-sm font-black text-blue-600">
                Browse Off-server Themes →
            </div>
        </a>

    </div>
</div>
@endsection
