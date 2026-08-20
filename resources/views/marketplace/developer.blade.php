@extends('layouts.app')

@section('content')

<div class="min-h-screen bg-slate-50 px-6 py-8">
    <div class="mx-auto max-w-7xl">

        <div class="mb-8">
            <div class="text-xs font-black uppercase tracking-widest text-blue-600">
                Developer Marketplace
                    <a href="{{ route('marketplace.developer.addons') }}"
                       class="block text-sm font-semibold text-slate-600 hover:text-slate-900">
                        Developer Add-ons
                    </a>
            </div>

            <h1 class="mt-2 text-3xl font-black text-slate-900">
                Build and publish marketplace products
            </h1>

            <p class="mt-2 max-w-2xl text-sm text-slate-500">
                Create marketplace products, connect modules, and prepare
                your products for publication through the developer tools.
            </p>
        </div>

        <div class="grid gap-6 md:grid-cols-2">

            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <div class="text-2xl">🧩</div>

                <h2 class="mt-4 text-xl font-black text-slate-900">
                    Marketplace Compiler Wizard
                </h2>

                <p class="mt-2 text-sm leading-6 text-slate-500">
                    Build a marketplace product, configure its modules,
                    deployment options and product information.
                </p>

                <a href="{{ route('developer.builder') }}"
                   class="mt-6 inline-flex rounded-xl bg-slate-900 px-5 py-3 text-sm font-bold text-white">
                    Open Compiler Wizard
                </a>
            </div>

            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <div class="text-2xl">🛍️</div>

                <h2 class="mt-4 text-xl font-black text-slate-900">
                    Published Products
                </h2>

                <p class="mt-2 text-sm leading-6 text-slate-500">
                    Your marketplace products will appear here as the
                    developer marketplace system is expanded.
                </p>

                <div class="mt-6 rounded-xl bg-slate-50 px-4 py-3 text-xs font-semibold text-slate-500">
                    {{ $addons->count() }} Core add-ons currently available platform-wide.
                </div>
            </div>

        </div>

    </div>
</div>

@endsection
