@extends('admin.layouts.app')

@section('title', 'Website Types')

@section('content')

<div class="mb-8 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
    <div>
        <h1 class="text-3xl font-bold tracking-tight text-slate-900">
            Website Types
        </h1>
        <p class="mt-2 text-slate-500">
            Configure the website types available in the Esubiz website builder.
        </p>
    </div>

    <a
        href="{{ route('admin.website-types.create') }}"
        class="inline-flex items-center justify-center rounded-2xl bg-slate-900 px-5 py-3 text-sm font-bold text-white shadow-sm transition hover:bg-slate-800">
        + Add Website Type
    </a>
</div>

<section class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">

    <div class="border-b border-slate-200 px-6 py-5">
        <h2 class="font-bold text-slate-900">
            Configured Website Types
        </h2>
        <p class="mt-1 text-sm text-slate-500">
            These website types will be used by the user wizard and developer builder.
        </p>
    </div>

    @if($websiteTypes->isEmpty())

        <div class="px-6 py-16 text-center">
            <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-slate-100 text-2xl text-slate-400">
                +
            </div>

            <h3 class="mt-4 text-lg font-bold text-slate-900">
                No website types yet
            </h3>

            <p class="mx-auto mt-2 max-w-md text-sm leading-6 text-slate-500">
                Create your first website type and configure the products,
                themes and add-on bundles available for it.
            </p>

            <a
                href="{{ route('admin.website-types.create') }}"
                class="mt-6 inline-flex items-center justify-center rounded-xl bg-slate-900 px-5 py-3 text-sm font-bold text-white hover:bg-slate-800">
                Add Website Type
            </a>
        </div>

    @else

        <div class="divide-y divide-slate-100">
            @foreach($websiteTypes as $websiteType)
                <div class="flex flex-col gap-4 px-6 py-5 md:flex-row md:items-center md:justify-between">

                    <div class="flex items-center gap-4">
                        <div class="flex h-12 w-12 shrink-0 items-center justify-center overflow-hidden rounded-2xl bg-slate-100">
                            @if($websiteType->image)
                                <img
                                    src="{{ asset('storage/' . $websiteType->image) }}"
                                    alt="{{ $websiteType->name }}"
                                    class="h-full w-full object-cover">
                            @elseif($websiteType->icon)
                                <span class="text-xl">
                                    {{ config('website_type_icons.' . $websiteType->icon, '🌐') }}
                                </span>
                            @else
                                <span class="text-sm font-bold text-slate-400">
                                    {{ strtoupper(substr($websiteType->name, 0, 1)) }}
                                </span>
                            @endif
                        </div>

                        <div>
                            <h3 class="font-bold text-slate-900">
                                {{ $websiteType->name }}
                            </h3>

                            @if($websiteType->description)
                                <p class="mt-1 text-sm text-slate-500">
                                    {{ $websiteType->description }}
                                </p>
                            @endif
                        </div>
                    </div>

                    <div class="flex items-center gap-3">
                        @if($websiteType->is_active)
                            <span class="rounded-full bg-emerald-50 px-3 py-1 text-xs font-bold text-emerald-700">
                                Active
                            </span>
                        @else
                            <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-bold text-slate-500">
                                Inactive
                            </span>
                        @endif

                        <a
                            href="{{ route('admin.website-types.edit', $websiteType) }}"
                            class="rounded-xl border border-slate-200 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">
                            Edit
                        </a>
                    </div>

                </div>
            @endforeach
        </div>

    @endif

</section>

@endsection
