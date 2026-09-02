@extends('admin.layouts.app')

@section('title', 'Edit Website')

@section('content')

<div class="space-y-8">

    {{-- ================================================================ --}}
    {{-- HEADER --}}
    {{-- ================================================================ --}}

    <div class="rounded-3xl bg-white p-8 shadow">

        <div class="flex flex-col gap-6 lg:flex-row lg:items-center lg:justify-between">

            <div>

                <p class="text-sm font-semibold uppercase tracking-widest text-blue-600">
                    Website Management
                </p>

                <h1 class="mt-3 text-4xl font-bold text-slate-900">
                    {{ $website->name }}
                </h1>

                <p class="mt-3 text-slate-500">
                    Manage your website availability and view your website information.
                </p>

            </div>

            <a
                href="{{ route('user.websites.index') }}"
                class="inline-flex items-center justify-center rounded-xl border border-slate-300 bg-white px-6 py-3 font-semibold text-slate-700 hover:bg-slate-50"
            >
                ← My Websites
            </a>

        </div>

    </div>


    {{-- ================================================================ --}}
    {{-- SUCCESS MESSAGE --}}
    {{-- ================================================================ --}}

    @if(session('success'))

        <div class="rounded-2xl border border-green-200 bg-green-50 px-6 py-4 text-green-800">
            {{ session('success') }}
        </div>

    @endif


    {{-- ================================================================ --}}
    {{-- VALIDATION ERRORS --}}
    {{-- ================================================================ --}}

    @if($errors->any())

        <div class="rounded-2xl border border-red-200 bg-red-50 px-6 py-4 text-red-800">

            <ul class="list-disc space-y-1 pl-5">

                @foreach($errors->all() as $error)

                    <li>{{ $error }}</li>

                @endforeach

            </ul>

        </div>

    @endif


    {{-- ================================================================ --}}
    {{-- WEBSITE NAME --}}
    {{-- ================================================================ --}}

    <div class="rounded-3xl bg-white p-8 shadow">

        <h2 class="text-2xl font-bold text-slate-900">
            Website Name
        </h2>

        <p class="mt-2 text-slate-500">
            Change the name of your website. This name will be updated across your Esubiz website records.
        </p>

        <form
            method="POST"
            action="{{ route('user.websites.update', $website) }}"
            class="mt-8"
        >

            @csrf
            @method('PUT')

            <label
                for="name"
                class="mb-3 block text-lg font-semibold text-slate-800"
            >
                Website Name
            </label>

            <input
                type="text"
                name="name"
                id="name"
                required
                maxlength="255"
                value="{{ old('name', $website->name) }}"
                class="w-full rounded-2xl border border-slate-300 px-5 py-4 focus:border-blue-500 focus:ring-blue-500"
                placeholder="Enter your website name"
            >

            @error('name')
                <p class="mt-2 text-sm font-medium text-red-600">
                    {{ $message }}
                </p>
            @enderror

            <div class="mt-6 flex justify-end">

                <button
                    type="submit"
                    class="rounded-xl bg-blue-600 px-7 py-3 font-semibold text-white shadow-sm transition hover:bg-blue-700"
                >
                    Save Website Name
                </button>

            </div>

        </form>

    </div>


    {{-- ================================================================ --}}
    {{-- WEBSITE AVAILABILITY --}}
    {{-- ================================================================ --}}

    <div class="rounded-3xl bg-white p-8 shadow">

        <h2 class="text-2xl font-bold text-slate-900">
            Website Availability
        </h2>

        <p class="mt-2 text-slate-500">
            Control whether visitors can access this website publicly.
        </p>


        <form
            method="POST"
            action="{{ route('user.websites.update', $website) }}"
            class="mt-8"
        >

            @csrf
            @method('PUT')


            <div class="grid gap-5 md:grid-cols-2">

                {{-- ACTIVE --}}

                <label
                    class="flex cursor-pointer items-start gap-4 rounded-2xl border-2 p-6 transition
                    {{ $website->user_enabled
                        ? 'border-green-500 bg-green-50'
                        : 'border-slate-200 bg-white hover:border-green-300' }}"
                >

                    <input
                        type="radio"
                        name="user_enabled"
                        value="1"
                        class="mt-1 h-5 w-5"
                        {{ $website->user_enabled ? 'checked' : '' }}
                    >

                    <span>

                        <span class="flex items-center gap-2 text-lg font-bold text-slate-900">

                            <span class="h-3 w-3 rounded-full bg-green-500"></span>

                            Active

                        </span>

                        <span class="mt-2 block text-sm leading-6 text-slate-500">

                            Your website is enabled and visitors can access it when
                            Esubiz system controls permit public access.

                        </span>

                    </span>

                </label>


                {{-- INACTIVE --}}

                <label
                    class="flex cursor-pointer items-start gap-4 rounded-2xl border-2 p-6 transition
                    {{ !$website->user_enabled
                        ? 'border-red-500 bg-red-50'
                        : 'border-slate-200 bg-white hover:border-red-300' }}"
                >

                    <input
                        type="radio"
                        name="user_enabled"
                        value="0"
                        class="mt-1 h-5 w-5"
                        {{ !$website->user_enabled ? 'checked' : '' }}
                    >

                    <span>

                        <span class="flex items-center gap-2 text-lg font-bold text-slate-900">

                            <span class="h-3 w-3 rounded-full bg-red-500"></span>

                            Inactive

                        </span>

                        <span class="mt-2 block text-sm leading-6 text-slate-500">

                            Your website is disabled from your Esubiz account and
                            visitors cannot access it.

                        </span>

                    </span>

                </label>

            </div>


            {{-- SYSTEM STATUS NOTICE --}}

            <div class="mt-6 rounded-2xl border border-blue-200 bg-blue-50 p-5">

                <div class="flex items-start gap-3">

                    <span class="text-lg">
                        ℹ️
                    </span>

                    <div>

                        <p class="font-semibold text-blue-900">
                            Esubiz System Status
                        </p>

                        <p class="mt-1 text-sm leading-6 text-blue-800">

                            Your Active/Inactive setting only controls your own website
                            availability. It does not override Esubiz system controls,
                            including payment suspension, security restrictions,
                            unlawful-use suspension, or other platform enforcement.

                        </p>

                    </div>

                </div>

            </div>


            <div class="mt-6 flex justify-end">

                <button
                    type="submit"
                    class="rounded-xl bg-blue-600 px-7 py-3 font-semibold text-white shadow-sm transition hover:bg-blue-700"
                >
                    Save Changes
                </button>

            </div>

        </form>

    </div>


    {{-- ================================================================ --}}
    {{-- WEBSITE INFORMATION --}}
    {{-- ================================================================ --}}

    <div class="rounded-3xl bg-white p-8 shadow">

        <div>

            <h2 class="text-2xl font-bold text-slate-900">
                Website Information
            </h2>

            <p class="mt-2 text-slate-500">
                Basic information about this website.
            </p>

        </div>


        <div class="mt-6 grid gap-6 md:grid-cols-2">

            {{-- WEBSITE TYPE --}}

            <div class="rounded-2xl bg-slate-50 p-5">

                <p class="text-sm text-slate-500">
                    Website Type
                </p>

                <p class="mt-2 font-semibold text-slate-900">
                    {{ ucfirst($website->type) }}
                </p>

            </div>


            {{-- WEBSITE ADDRESS --}}

            <div class="rounded-2xl bg-slate-50 p-5">

                <p class="text-sm text-slate-500">
                    Esubiz Website Address
                </p>

                <p class="mt-2 break-all font-semibold text-slate-900">
                    {{ $website->subdomain }}.esubiz.com
                </p>

            </div>


            {{-- SYSTEM STATUS --}}

            <div class="rounded-2xl bg-slate-50 p-5">

                <p class="text-sm text-slate-500">
                    System Status
                </p>

                <p class="mt-2 font-semibold text-slate-900">
                    {{ ucfirst($website->status) }}
                </p>

            </div>


            {{-- WEBSITE CODE --}}

            <div class="rounded-2xl bg-slate-50 p-5">

                <p class="text-sm text-slate-500">
                    Website Code
                </p>

                <p class="mt-2 break-all font-semibold text-slate-900">
                    {{ $website->website_code }}
                </p>

            </div>


            {{-- CUSTOM DOMAIN --}}

            <div class="rounded-2xl bg-slate-50 p-5 md:col-span-2">

                <p class="text-sm text-slate-500">
                    Custom Domain
                </p>

                <p class="mt-2 break-all font-semibold text-slate-900">

                    {{ $website->domain ?: 'No custom domain connected' }}

                </p>

            </div>

        </div>

    </div>

</div>

@endsection
