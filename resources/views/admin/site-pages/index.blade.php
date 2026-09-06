@extends('admin.layouts.app')

@section('title', 'Site Pages')

@section('content')
<div class="mx-auto w-full max-w-7xl px-4 py-6 sm:px-6 lg:px-8">

    {{-- ESUBIZ_CENTRAL_SITE_PAGES_INDEX_V2 --}}

    <div class="mb-6">
        <h1 class="text-2xl font-black text-slate-950">Site Pages</h1>

        <p class="mt-1 text-sm text-slate-500">
            Manage pages belonging to the main Esubiz website.
        </p>
    </div>

    <div class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">

        <div class="border-b border-slate-200 px-6 py-5">
            <h2 class="font-black text-slate-950">Central Website Pages</h2>

            <p class="mt-1 text-sm text-slate-500">
                These pages are independent from websites created with Esubiz Core.
            </p>
        </div>

        <div class="divide-y divide-slate-100">

            @forelse($pages as $sitePage)

                <div class="flex flex-wrap items-center justify-between gap-4 px-6 py-5">

                    <div>
                        <div class="font-black text-slate-900">
                            {{ $sitePage->title }}
                        </div>

                        <div class="mt-1 text-sm text-slate-500">
                            /{{ $sitePage->slug }}
                        </div>
                    </div>

                    <div class="flex items-center gap-3">

                        <span
                            class="rounded-full px-3 py-1 text-xs font-black
                            {{
                                $sitePage->status === 'published'
                                    ? 'bg-emerald-50 text-emerald-700'
                                    : 'bg-amber-50 text-amber-700'
                            }}"
                        >
                            {{ ucfirst($sitePage->status) }}
                        </span>

                        <span
                            class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-2 text-xs font-black text-slate-500"
                        >
                            Edit Page
                        </span>

                    </div>

                </div>

            @empty

                <div class="px-6 py-10 text-center text-sm text-slate-500">
                    No Central site pages have been created.
                </div>

            @endforelse

        </div>
    </div>
</div>
@endsection
