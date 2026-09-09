@extends('admin.layouts.app')

@section('title', 'Themes')

@section('content')

<div class="min-h-screen bg-slate-100 px-5 py-8 sm:px-6">

    <div class="mx-auto max-w-7xl">

        <div
            class="mb-8 flex flex-wrap items-end justify-between gap-4"
        >

            <div>

                <div
                    class="text-xs font-black uppercase tracking-widest text-blue-600"
                >
                    Marketplace Products
                </div>

                <h1
                    class="mt-2 text-3xl font-black text-slate-900"
                >
                    Themes
                </h1>

                <p
                    class="mt-2 max-w-3xl text-sm leading-6 text-slate-500"
                >
                    Manage theme packages, releases, pricing,
                    deployment availability and Marketplace settings.
                </p>

            </div>

        </div>


        @if(session('success'))

            <div
                class="mb-6 rounded-2xl border border-emerald-200 bg-emerald-50 px-5 py-4 text-sm font-bold text-emerald-700"
            >
                {{ session('success') }}
            </div>

        @endif


        @if($errors->any())

            <div
                class="mb-6 rounded-2xl border border-red-200 bg-red-50 px-5 py-4 text-sm text-red-700"
            >

                <ul class="list-disc pl-5">

                    @foreach($errors->all() as $error)

                        <li>{{ $error }}</li>

                    @endforeach

                </ul>

            </div>

        @endif


        <div class="space-y-6">

            @forelse($themes as $theme)

                <article
                    class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm"
                >

                    <div
                        class="flex flex-wrap items-center justify-between gap-4 border-b border-slate-200 bg-slate-50 px-6 py-5"
                    >

                        <div>

                            <div
                                class="text-xs font-black uppercase tracking-wide text-blue-600"
                            >
                                {{ $theme->publisher_name }}
                            </div>

                            <h2
                                class="mt-1 text-xl font-black text-slate-900"
                            >
                                {{ $theme->name }}
                                <span
                                    class="ml-2 text-sm text-slate-400"
                                >
                                    v{{ $theme->version }}
                                </span>
                            </h2>

                        </div>


                        <div
                            class="flex flex-wrap items-center gap-2"
                        >

                            @if($theme->is_current)
                                <span
                                    class="rounded-full bg-blue-100 px-3 py-1 text-[11px] font-black text-blue-700"
                                >
                                    Current Version
                                </span>
                            @endif

                            <span
                                class="rounded-full px-3 py-1 text-[11px] font-black {{
                                    $theme->release_status === 'published'
                                        ? 'bg-emerald-100 text-emerald-700'
                                        : 'bg-amber-100 text-amber-700'
                                }}"
                            >
                                {{ ucfirst($theme->release_status) }}
                            </span>

                        </div>

                    </div>


                    <div class="grid gap-6 p-6 xl:grid-cols-2">

                        {{-- Commercial configuration --}}
                        <form
                            method="POST"
                            action="{{ route(
                                'admin.themes.update',
                                $theme->id
                            ) }}"
                            class="space-y-5"
                        >

                            @csrf
                            @method('PUT')


                            <div
                                class="rounded-2xl border border-slate-200 p-5"
                            >

                                <h3 class="font-black text-slate-900">
                                    Theme Details
                                </h3>


                                <div class="mt-5 grid gap-4 sm:grid-cols-2">

                                    <div class="sm:col-span-2">

                                        <label class="text-sm font-bold">
                                            Theme Name
                                        </label>

                                        <input
                                            type="text"
                                            name="name"
                                            required
                                            value="{{ $theme->name }}"
                                            class="mt-2 w-full rounded-xl border border-slate-300 px-4 py-3"
                                        >

                                    </div>


                                    <div>

                                        <label class="text-sm font-bold">
                                            Publisher
                                        </label>

                                        <input
                                            type="text"
                                            name="publisher_name"
                                            required
                                            value="{{ $theme->publisher_name }}"
                                            class="mt-2 w-full rounded-xl border border-slate-300 px-4 py-3"
                                        >

                                    </div>


                                    <div>

                                        <label class="text-sm font-bold">
                                            Publisher Type
                                        </label>

                                        <select
                                            name="publisher_type"
                                            class="mt-2 w-full rounded-xl border border-slate-300 px-4 py-3"
                                        >
                                            <option
                                                value="company"
                                                @selected($theme->publisher_type === 'company')
                                            >
                                                Company
                                            </option>

                                            <option
                                                value="developer"
                                                @selected($theme->publisher_type === 'developer')
                                            >
                                                Developer
                                            </option>
                                        </select>

                                    </div>

                                </div>

                            </div>


                            <div
                                class="rounded-2xl border border-slate-200 p-5"
                            >

                                <h3 class="font-black text-slate-900">
                                    SaaS
                                </h3>


                                <label
                                    class="mt-4 flex items-center gap-3"
                                >
                                    <input
                                        type="hidden"
                                        name="saas_available"
                                        value="0"
                                    >

                                    <input
                                        type="checkbox"
                                        name="saas_available"
                                        value="1"
                                        @checked($theme->saas_available)
                                    >

                                    <span class="text-sm font-bold">
                                        Available for SaaS websites
                                    </span>
                                </label>


                                <div class="mt-4 grid gap-4 sm:grid-cols-3">

                                    <div>

                                        <label class="text-sm font-bold">
                                            Price
                                        </label>

                                        <input
                                            type="number"
                                            step="0.01"
                                            min="0"
                                            name="saas_price"
                                            value="{{ $theme->saas_price }}"
                                            class="mt-2 w-full rounded-xl border border-slate-300 px-4 py-3"
                                        >

                                    </div>


                                    <div>

                                        <label class="text-sm font-bold">
                                            Currency
                                        </label>

                                        <input
                                            type="text"
                                            maxlength="3"
                                            name="saas_currency"
                                            value="{{ $theme->saas_currency ?? 'NGN' }}"
                                            class="mt-2 w-full rounded-xl border border-slate-300 px-4 py-3"
                                        >

                                    </div>


                                    <div>

                                        <label class="text-sm font-bold">
                                            Billing
                                        </label>

                                        <select
                                            name="saas_billing_interval"
                                            class="mt-2 w-full rounded-xl border border-slate-300 px-4 py-3"
                                        >
                                            <option value="">
                                                One-time
                                            </option>

                                            <option
                                                value="monthly"
                                                @selected($theme->saas_billing_interval === 'monthly')
                                            >
                                                Monthly
                                            </option>

                                            <option
                                                value="yearly"
                                                @selected($theme->saas_billing_interval === 'yearly')
                                            >
                                                Yearly
                                            </option>
                                        </select>

                                    </div>

                                </div>

                            </div>


                            <div
                                class="rounded-2xl border border-slate-200 p-5"
                            >

                                <h3 class="font-black text-slate-900">
                                    Off-server
                                </h3>


                                <label
                                    class="mt-4 flex items-center gap-3"
                                >
                                    <input
                                        type="hidden"
                                        name="off_server_available"
                                        value="0"
                                    >

                                    <input
                                        type="checkbox"
                                        name="off_server_available"
                                        value="1"
                                        @checked($theme->off_server_available)
                                    >

                                    <span class="text-sm font-bold">
                                        Available for off-server websites
                                    </span>
                                </label>


                                <div class="mt-4 grid gap-4 sm:grid-cols-2">

                                    <div>

                                        <label class="text-sm font-bold">
                                            License Price
                                        </label>

                                        <input
                                            type="number"
                                            step="0.01"
                                            min="0"
                                            name="off_server_price"
                                            value="{{ $theme->off_server_price }}"
                                            class="mt-2 w-full rounded-xl border border-slate-300 px-4 py-3"
                                        >

                                    </div>


                                    <div>

                                        <label class="text-sm font-bold">
                                            Currency
                                        </label>

                                        <input
                                            type="text"
                                            maxlength="3"
                                            name="off_server_currency"
                                            value="{{ $theme->off_server_currency ?? 'NGN' }}"
                                            class="mt-2 w-full rounded-xl border border-slate-300 px-4 py-3"
                                        >

                                    </div>

                                </div>

                            </div>


                            <div
                                class="rounded-2xl border border-slate-200 p-5"
                            >

                                <h3 class="font-black text-slate-900">
                                    Marketplace
                                </h3>


                                <div class="mt-4 grid gap-4 sm:grid-cols-2">

                                    <label class="flex items-center gap-3">
                                        <input
                                            type="hidden"
                                            name="marketplace_enabled"
                                            value="0"
                                        >

                                        <input
                                            type="checkbox"
                                            name="marketplace_enabled"
                                            value="1"
                                            @checked($theme->marketplace_enabled)
                                        >

                                        <span class="text-sm font-bold">
                                            Marketplace enabled
                                        </span>
                                    </label>


                                    <label class="flex items-center gap-3">
                                        <input
                                            type="hidden"
                                            name="marketplace_featured"
                                            value="0"
                                        >

                                        <input
                                            type="checkbox"
                                            name="marketplace_featured"
                                            value="1"
                                            @checked($theme->marketplace_featured)
                                        >

                                        <span class="text-sm font-bold">
                                            Featured
                                        </span>
                                    </label>


                                    <div>

                                        <label class="text-sm font-bold">
                                            Category
                                        </label>

                                        <input
                                            type="text"
                                            name="marketplace_category"
                                            value="{{ $theme->marketplace_category }}"
                                            placeholder="Business"
                                            class="mt-2 w-full rounded-xl border border-slate-300 px-4 py-3"
                                        >

                                    </div>


                                    <div>

                                        <label class="text-sm font-bold">
                                            Platform Share %
                                        </label>

                                        <input
                                            type="number"
                                            step="0.01"
                                            min="0"
                                            max="100"
                                            name="commission_rate"
                                            value="{{ $theme->commission_rate }}"
                                            class="mt-2 w-full rounded-xl border border-slate-300 px-4 py-3"
                                        >

                                    </div>


                                    <div class="sm:col-span-2">

                                        <label class="text-sm font-bold">
                                            Release Notes
                                        </label>

                                        <textarea
                                            name="release_notes"
                                            rows="4"
                                            class="mt-2 w-full rounded-xl border border-slate-300 px-4 py-3"
                                        >{{ $theme->release_notes }}</textarea>

                                    </div>


                                    <label
                                        class="sm:col-span-2 flex items-center gap-3 rounded-xl bg-slate-50 p-4"
                                    >
                                        <input
                                            type="hidden"
                                            name="is_active"
                                            value="0"
                                        >

                                        <input
                                            type="checkbox"
                                            name="is_active"
                                            value="1"
                                            @checked($theme->is_active)
                                        >

                                        <span class="text-sm font-bold">
                                            Theme package active
                                        </span>
                                    </label>

                                </div>

                            </div>


                            <button
                                type="submit"
                                class="rounded-xl bg-blue-600 px-6 py-3 text-sm font-black text-white"
                            >
                                Save Theme Settings
                            </button>

                        </form>



                        {{-- Package / releases --}}
                        <div class="space-y-5">

                            <div
                                class="rounded-2xl border border-slate-200 p-5"
                            >

                                <h3 class="font-black text-slate-900">
                                    Package
                                </h3>


                                <dl class="mt-4 space-y-3 text-sm">

                                    <div>
                                        <dt class="font-bold text-slate-500">
                                            Stored File
                                        </dt>

                                        <dd class="mt-1 break-all font-mono text-xs text-slate-700">
                                            {{ $theme->package_path }}
                                        </dd>
                                    </div>


                                    <div>
                                        <dt class="font-bold text-slate-500">
                                            SHA-256
                                        </dt>

                                        <dd class="mt-1 break-all font-mono text-xs text-slate-700">
                                            {{ $theme->checksum_sha256 ?: 'Not recorded' }}
                                        </dd>
                                    </div>


                                    <div>
                                        <dt class="font-bold text-slate-500">
                                            Size
                                        </dt>

                                        <dd class="mt-1">
                                            {{ number_format(($theme->package_bytes ?? 0) / 1024, 1) }} KB
                                        </dd>
                                    </div>

                                </dl>

                            </div>


                            <form
                                method="POST"
                                enctype="multipart/form-data"
                                action="{{ route(
                                    'admin.themes.versions.store',
                                    $theme->id
                                ) }}"
                                class="rounded-2xl border border-slate-200 p-5"
                            >

                                @csrf


                                <h3 class="font-black text-slate-900">
                                    Create New Version
                                </h3>

                                <p
                                    class="mt-2 text-xs leading-5 text-slate-500"
                                >
                                    Published packages are never overwritten.
                                    Upload an updated package as a new version.
                                </p>


                                <div class="mt-5">

                                    <label class="text-sm font-bold">
                                        New Version
                                    </label>

                                    <input
                                        type="text"
                                        name="version"
                                        required
                                        placeholder="1.1.0"
                                        class="mt-2 w-full rounded-xl border border-slate-300 px-4 py-3"
                                    >

                                </div>


                                <div class="mt-4">

                                    <label class="text-sm font-bold">
                                        Theme Package (.zip)
                                    </label>

                                    <input
                                        type="file"
                                        name="package"
                                        required
                                        accept=".zip,application/zip"
                                        class="mt-2 block w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm"
                                    >

                                </div>


                                <div class="mt-4">

                                    <label class="text-sm font-bold">
                                        Release Notes
                                    </label>

                                    <textarea
                                        name="release_notes"
                                        rows="4"
                                        class="mt-2 w-full rounded-xl border border-slate-300 px-4 py-3"
                                    ></textarea>

                                </div>


                                <button
                                    type="submit"
                                    class="mt-5 rounded-xl bg-slate-950 px-5 py-3 text-sm font-black text-white"
                                >
                                    Validate & Create Version
                                </button>

                            </form>


                            @if(
                                $theme->release_status !== 'published'
                                && $theme->marketplace_ready
                            )

                                <form
                                    method="POST"
                                    action="{{ route(
                                        'admin.themes.publish',
                                        $theme->id
                                    ) }}"
                                    class="rounded-2xl border border-emerald-200 bg-emerald-50 p-5"
                                >

                                    @csrf

                                    <h3
                                        class="font-black text-emerald-900"
                                    >
                                        Publish Release
                                    </h3>

                                    <p
                                        class="mt-2 text-xs leading-5 text-emerald-700"
                                    >
                                        This package has passed the Esubiz
                                        theme validator and can be published.
                                    </p>

                                    <button
                                        type="submit"
                                        class="mt-4 rounded-xl bg-emerald-600 px-5 py-3 text-sm font-black text-white"
                                    >
                                        Publish v{{ $theme->version }}
                                    </button>

                                </form>

                            @endif

                        </div>

                    </div>

                </article>

            @empty

                <div
                    class="rounded-3xl border border-dashed border-slate-300 bg-white p-10 text-center"
                >
                    <div class="font-black text-slate-900">
                        No theme packages registered yet.
                    </div>

                    <p class="mt-2 text-sm text-slate-500">
                        Business v1.0 will be registered in the next setup step.
                    </p>
                </div>

            @endforelse

        </div>

    </div>

</div>

@endsection
