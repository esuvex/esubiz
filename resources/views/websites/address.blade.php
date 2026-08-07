@extends('admin.layouts.app')

@section('title', 'Business Address')

@section('content')

@php
$step = 6;
$steps = 9;
@endphp

<form method="GET" action="{{ route('websites.administrator', $website) }}">

    @foreach(request()->except([
        'country',
        'state',
        'city',
        'address'
    ]) as $key => $value)
        <input type="hidden" name="{{ $key }}" value="{{ $value }}">
    @endforeach

    <div class="rounded-3xl bg-slate-100 p-8">

        <div class="flex flex-col gap-6 lg:flex-row lg:items-start lg:justify-between">

            <div>

                <h1 class="text-4xl font-bold text-slate-900">
                    Business Address
                </h1>

                <p class="mt-3 text-slate-500">
                    Enter your business location.
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

        <div class="mt-10 grid gap-6 md:grid-cols-2">

            <div>

                <label class="mb-2 block font-medium text-slate-700">
                    Country
                </label>

                <input
                    type="text"
                    name="country"
                    class="w-full rounded-xl border border-slate-300 bg-white px-5 py-4"
                    value="{{ request('country') }}">

            </div>

            <div>

                <label class="mb-2 block font-medium text-slate-700">
                    State
                </label>

                <input
                    type="text"
                    name="state"
                    class="w-full rounded-xl border border-slate-300 bg-white px-5 py-4"
                    value="{{ request('state') }}">

            </div>

            <div>

                <label class="mb-2 block font-medium text-slate-700">
                    City
                </label>

                <input
                    type="text"
                    name="city"
                    class="w-full rounded-xl border border-slate-300 bg-white px-5 py-4"
                    value="{{ request('city') }}">

            </div>

            <div>

                <label class="mb-2 block font-medium text-slate-700">
                    Address
                </label>

                <input
                    type="text"
                    name="address"
                    class="w-full rounded-xl border border-slate-300 bg-white px-5 py-4"
                    value="{{ request('address') }}">

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
                class="inline-flex items-center justify-center rounded-xl bg-blue-600 px-8 py-3 font-semibold text-white">

                Continue →

            </button>

        </div>

    </div>

</form>

@endsection
