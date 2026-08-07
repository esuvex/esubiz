@extends('admin.layouts.app')

@section('title', 'Website Address')

@section('content')

@php
$step = 5;
$steps = 9;

$websiteName = request('name', '');

$subdomain = request(
    'subdomain',
    \Illuminate\Support\Str::slug($websiteName)
);

$preview = $subdomain
    ? "https://{$subdomain}.esubiz.com"
    : "https://yourbusiness.esubiz.com";
@endphp

<form method="GET" action="{{ route('websites.address', $website) }}">

    @foreach(request()->except([
        'subdomain',
        'domain',
    ]) as $key => $value)

        <input
            type="hidden"
            name="{{ $key }}"
            value="{{ $value }}">

    @endforeach

    <div class="rounded-3xl bg-slate-100 p-8">

        <div class="flex flex-col gap-6 lg:flex-row lg:items-start lg:justify-between">

            <div>

                <h1 class="text-4xl font-bold text-slate-900">

                    Website Address

                </h1>

                <p class="mt-3 text-slate-500">

                    Choose the address people will use to access your website.

                </p>

            </div>

            <div class="rounded-2xl bg-white px-6 py-5 shadow-sm">

                <div class="text-sm text-slate-500">

                    Step

                </div>

                <div class="text-3xl font-bold text-blue-600">

                    {{ $step }} / {{ $steps }}

                </div>

            </div>

        </div>

        <div class="mt-10 grid grid-cols-4 gap-4 md:flex md:items-center">

            @for($i = 1; $i <= $steps; $i++)

                <div class="flex justify-center md:flex-1">

                    <div class="flex h-12 w-12 items-center justify-center rounded-full border-2 text-sm font-bold {{ $i == $step ? 'border-blue-600 bg-blue-600 text-white' : 'border-slate-300 bg-white text-slate-500' }}">

                        {{ $i }}

                    </div>

                </div>

            @endfor

        </div>

        <div class="mt-10 space-y-8">

            <div class="rounded-3xl bg-white p-8 shadow-sm">

                <label class="mb-3 block text-lg font-semibold text-slate-800">

                    Esubiz Subdomain

                </label>

                <div class="flex overflow-hidden rounded-2xl border border-slate-300">

                    <input
                        type="text"
                        name="subdomain"
                        required
                        value="{{ $subdomain }}"
                        class="flex-1 border-0 px-5 py-4 focus:ring-0"
                        placeholder="yourbusiness">

                    <div class="flex items-center bg-slate-100 px-6 text-slate-600">

                        .esubiz.com

                    </div>

                </div>

                <div class="mt-4 flex items-center justify-between">

                    <p class="text-sm text-slate-500">

                        This is your free website address.

                    </p>

                    <span class="rounded-full bg-green-100 px-4 py-2 text-sm font-semibold text-green-700">

                        ✓ Available

                    </span>

                </div>

            </div>

            <div class="rounded-3xl bg-white p-8 shadow-sm">

                <label class="mb-3 block text-lg font-semibold text-slate-800">

                    Custom Domain <span class="text-slate-400">(Optional)</span>

                </label>

                <input
                    type="text"
                    name="domain"
                    value="{{ request('domain') }}"
                    placeholder="www.yourcompany.com"
                    class="w-full rounded-2xl border border-slate-300 px-5 py-4">

                <p class="mt-4 text-sm text-slate-500">

                    You can connect your own domain now or later from your dashboard.

                </p>

            </div>

            <div class="rounded-3xl border border-blue-200 bg-blue-50 p-8">

                <h3 class="text-xl font-bold text-blue-700">

                    Website Preview

                </h3>

                <p class="mt-6 text-sm uppercase tracking-wider text-slate-500">

                    Your website will be available at

                </p>

                <div class="mt-3 break-all rounded-2xl bg-white px-6 py-5 text-2xl font-bold text-blue-700">

                    {{ $preview }}

                </div>

            </div>

        </div>

        <div class="mt-10 flex flex-col gap-4 border-t border-slate-200 pt-8 sm:flex-row sm:items-center sm:justify-between">

            <a
                href="{{ route('websites.plan', $website) }}"
                class="inline-flex items-center justify-center rounded-xl border border-slate-300 bg-white px-6 py-3 font-medium">

                ← Back

            </a>

            <button
                type="submit"
                class="inline-flex items-center justify-center rounded-xl bg-blue-600 px-8 py-3 font-semibold text-white hover:bg-blue-700">

                Continue →

            </button>

        </div>

    </div>

</form>

@endsection
