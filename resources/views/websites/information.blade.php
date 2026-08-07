@extends('admin.layouts.app')

@section('title', 'Website Information')

@section('content')

@php
$step = 3;
$steps = 9;
@endphp

<form method="GET" action="{{ route('websites.plan', $website) }}">

    <input type="hidden" name="theme" value="{{ request('theme') }}">

    <div class="rounded-3xl bg-slate-100 p-8">

        <div class="flex flex-col gap-6 lg:flex-row lg:items-start lg:justify-between">

            <div>

                <h1 class="text-4xl font-bold text-slate-900">
                    Website Information
                </h1>

                <p class="mt-3 text-slate-500">
                    Tell us about your website.
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

        <div class="mt-10 space-y-6">

            <div>

                <label class="mb-2 block font-medium text-slate-700">
                    Website Name
                </label>

                <input
                    type="text"
                    name="name"
                    required
                    class="w-full rounded-xl border border-slate-300 bg-white px-5 py-4"
                    value="{{ request('name') }}">

            </div>

            <div class="grid gap-6 md:grid-cols-2">

                <div>

                    <label class="mb-2 block font-medium text-slate-700">
                        Industry
                    </label>

                    <input
                        type="text"
                        name="industry"
                        class="w-full rounded-xl border border-slate-300 bg-white px-5 py-4"
                        value="{{ request('industry') }}">

                </div>

                <div>

                    <label class="mb-2 block font-medium text-slate-700">
                        Business Email
                    </label>

                    <input
                        type="email"
                        name="email"
                        class="w-full rounded-xl border border-slate-300 bg-white px-5 py-4"
                        value="{{ request('email') }}">

                </div>

            </div>

            <div>

                <label class="mb-2 block font-medium text-slate-700">
                    Business Phone
                </label>

                <input
                    type="text"
                    name="phone"
                    class="w-full rounded-xl border border-slate-300 bg-white px-5 py-4"
                    value="{{ request('phone') }}">

            </div>

            <div>

                <label class="mb-2 block font-medium text-slate-700">
                    Description
                </label>

                <textarea
                    name="description"
                    rows="5"
                    class="w-full rounded-xl border border-slate-300 bg-white px-5 py-4">{{ request('description') }}</textarea>

            </div>

        </div>

        <div class="mt-10 flex flex-col gap-4 border-t border-slate-200 pt-8 sm:flex-row sm:items-center sm:justify-between">

            <a
                href="{{ route('websites.theme', $website) }}"
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
