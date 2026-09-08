{{-- ESUBIZ_INTERNAL_MEDIA_ON_DEMAND_V1 --}}
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

<section class="overflow-visible rounded-3xl border border-slate-200 bg-white shadow-sm">

    <div class="border-b border-slate-200 px-6 py-5">
        <h2 class="font-bold text-slate-900">
            Configured Website Types
        </h2>

        <p class="mt-1 text-sm text-slate-500">
            Manage the Website Types available to the user and developer website builders.
        </p>
    </div>

    <div
        id="esubiz-website-type-table"
        class="relative">
        @include('admin.website-types._table', [
            'websiteTypes' => $websiteTypes,
        ])
    </div>

</section>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const container = document.getElementById('esubiz-website-type-table');

    if (!container) {
        return;
    }

    async function loadWebsiteTypePage(url) {
        container.classList.add('opacity-60', 'pointer-events-none');

        try {
            const response = await fetch(url, {
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'text/html',
                },
                credentials: 'same-origin',
            });

            if (!response.ok) {
                throw new Error('Unable to load Website Types.');
            }

            container.innerHTML = await response.text();

            window.history.replaceState(
                {},
                '',
                url
            );
        } catch (error) {
            window.location.href = url;
        } finally {
            container.classList.remove(
                'opacity-60',
                'pointer-events-none'
            );
        }
    }

    container.addEventListener('click', function (event) {
        const link = event.target.closest('[data-website-type-page]');

        if (!link) {
            return;
        }

        event.preventDefault();

        loadWebsiteTypePage(link.href);
    });
});
</script>

@endsection
