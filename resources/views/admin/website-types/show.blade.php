{{-- ESUBIZ_WEBSITE_TYPE_SHOW_V14 --}}
@extends('admin.layouts.app')

@section('title', $websiteType->name)

@section('content')

<div class="mb-8 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
    <div>
        <div class="mb-2">
            <a
                href="{{ route('admin.website-types.index') }}"
                class="text-sm font-semibold text-slate-500 hover:text-slate-900">
                ← Website Types
            </a>
        </div>

        <h1 class="text-3xl font-bold tracking-tight text-slate-900">
            {{ $websiteType->name }}
        </h1>

        <p class="mt-2 text-slate-500">
            Website Type details and deployment availability.
        </p>
    </div>

    <div class="flex items-center gap-3">
        @if($websiteType->is_active)
            <span class="rounded-full bg-emerald-50 px-4 py-2 text-sm font-bold text-emerald-700">
                Active
            </span>
        @else
            <span class="rounded-full bg-slate-100 px-4 py-2 text-sm font-bold text-slate-500">
                Disabled
            </span>
        @endif

        <a
            href="{{ route('admin.website-types.edit', $websiteType) }}"
            class="inline-flex items-center justify-center rounded-xl bg-slate-900 px-5 py-3 text-sm font-bold text-white transition hover:bg-slate-800">
            Edit Website Type
        </a>
    </div>
</div>

<div class="grid gap-6 xl:grid-cols-3">

    <section class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm xl:col-span-2">

        <div class="border-b border-slate-200 px-6 py-5">
            <h2 class="font-bold text-slate-900">
                Website Type Information
            </h2>
        </div>

        <div class="divide-y divide-slate-100">

            <div class="grid gap-2 px-6 py-5 sm:grid-cols-[180px_1fr]">
                <div class="text-sm font-semibold text-slate-500">
                    Name
                </div>

                <div class="text-sm font-bold text-slate-900">
                    {{ $websiteType->name }}
                </div>
            </div>

            <div class="grid gap-2 px-6 py-5 sm:grid-cols-[180px_1fr]">
                <div class="text-sm font-semibold text-slate-500">
                    Slug
                </div>

                <div class="text-sm text-slate-700">
                    {{ $websiteType->slug }}
                </div>
            </div>

            <div class="grid gap-2 px-6 py-5 sm:grid-cols-[180px_1fr]">
                <div class="text-sm font-semibold text-slate-500">
                    Description
                </div>

                <div class="whitespace-pre-line text-sm leading-7 text-slate-700">
                    {{ $websiteType->description ?: 'No description provided.' }}
                </div>
            </div>

            <div class="grid gap-2 px-6 py-5 sm:grid-cols-[180px_1fr]">
                <div class="text-sm font-semibold text-slate-500">
                    Core Engine
                </div>

                <div class="text-sm font-bold text-slate-900">
                    Esubiz Core
                </div>
            </div>

            <div class="grid gap-2 px-6 py-5 sm:grid-cols-[180px_1fr]">
                <div class="text-sm font-semibold text-slate-500">
                    Architecture
                </div>

                <div class="text-sm text-slate-700">
                    Native Core
                </div>
            </div>

        </div>

    </section>

    <div class="space-y-6">

        <section class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">

            <div class="border-b border-slate-200 px-6 py-5">
                <h2 class="font-bold text-slate-900">
                    Availability
                </h2>
            </div>

            <div class="space-y-4 p-6">

                <div class="flex items-center justify-between gap-4">
                    <span class="text-sm font-semibold text-slate-600">
                        User Wizard
                    </span>

                    @if($websiteType->show_in_user_wizard)
                        <span class="rounded-full bg-emerald-50 px-3 py-1 text-xs font-bold text-emerald-700">
                            Available
                        </span>
                    @else
                        <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-bold text-slate-500">
                            Hidden
                        </span>
                    @endif
                </div>

                <div class="flex items-center justify-between gap-4">
                    <span class="text-sm font-semibold text-slate-600">
                        Developer Wizard
                    </span>

                    @if($websiteType->show_in_developer_wizard)
                        <span class="rounded-full bg-emerald-50 px-3 py-1 text-xs font-bold text-emerald-700">
                            Available
                        </span>
                    @else
                        <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-bold text-slate-500">
                            Hidden
                        </span>
                    @endif
                </div>

            </div>

        </section>

        <section class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">

            <div class="border-b border-slate-200 px-6 py-5">
                <h2 class="font-bold text-slate-900">
                    Pricing
                </h2>
            </div>

            <div class="space-y-5 p-6">

                <div>
                    <div class="text-xs font-bold uppercase tracking-wide text-slate-400">
                        SaaS
                    </div>

                    <div class="mt-1 text-lg font-bold text-slate-900">
                        @if(!is_null($websiteType->saas_price))
                            ₦{{ number_format((float) $websiteType->saas_price, 2) }}
                        @else
                            —
                        @endif
                    </div>

                    @if($websiteType->saas_billing_period && $websiteType->saas_billing_interval)
                        <div class="mt-1 text-xs text-slate-500">
                            Every {{ $websiteType->saas_billing_period }}
                            {{ $websiteType->saas_billing_interval }}
                        </div>
                    @endif
                </div>

                <div class="border-t border-slate-100 pt-5">
                    <div class="text-xs font-bold uppercase tracking-wide text-slate-400">
                        Off-server
                    </div>

                    <div class="mt-1 text-lg font-bold text-slate-900">
                        @if(!is_null($websiteType->off_server_price))
                            ₦{{ number_format((float) $websiteType->off_server_price, 2) }}
                        @else
                            —
                        @endif
                    </div>
                </div>

            </div>

        </section>

    </div>

</div>

@endsection
