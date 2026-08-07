@extends('admin.layouts.app')

@section('title', 'Review & Deploy')

@section('content')

@php
$step = 8;
$steps = 9;
@endphp

<form method="POST" action="{{ route('websites.deploy', $website) }}">

    @csrf

    @foreach(request()->all() as $key => $value)
        @if(!is_array($value))
            <input type="hidden" name="{{ $key }}" value="{{ $value }}">
        @endif
    @endforeach

    <div class="rounded-3xl bg-slate-100 p-8">

        <div class="flex flex-col gap-6 lg:flex-row lg:items-start lg:justify-between">

            <div>

                <h1 class="text-4xl font-bold text-slate-900">

                    Review Your Website

                </h1>

                <p class="mt-3 text-slate-500">

                    Confirm everything before deploying your website.

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

        <div class="mt-10 grid gap-6 lg:grid-cols-2">

            <div class="rounded-2xl bg-white p-6 shadow-sm">

                <h3 class="mb-6 text-xl font-bold text-slate-900">

                    Website Information

                </h3>

                <div class="space-y-4">

                    <div class="flex justify-between">

                        <span class="text-slate-500">Website Name</span>

                        <span class="font-semibold">{{ request('name') }}</span>

                    </div>

                    <div class="flex justify-between">

                        <span class="text-slate-500">Industry</span>

                        <span class="font-semibold">{{ request('industry') }}</span>

                    </div>

                    <div class="flex justify-between">

                        <span class="text-slate-500">Theme</span>

                        <span class="font-semibold">{{ request('theme') }}</span>

                    </div>

                    <div class="flex justify-between">

                        <span class="text-slate-500">Plan</span>

                        <span class="font-semibold">{{ request('plan') }}</span>

                    </div>

                </div>

            </div>

            <div class="rounded-2xl bg-white p-6 shadow-sm">

                <h3 class="mb-6 text-xl font-bold text-slate-900">

                    Administrator

                </h3>

                <div class="space-y-4">

                    <div class="flex justify-between">

                        <span class="text-slate-500">Name</span>

                        <span class="font-semibold">{{ request('admin_name') }}</span>

                    </div>

                    <div class="flex justify-between">

                        <span class="text-slate-500">Email</span>

                        <span class="font-semibold">{{ request('admin_email') }}</span>

                    </div>

                    <div class="flex justify-between">

                        <span class="text-slate-500">Phone</span>

                        <span class="font-semibold">{{ request('admin_phone') }}</span>

                    </div>

                </div>

            </div>

        </div>

        <div class="mt-10 rounded-2xl border border-blue-200 bg-blue-50 p-6">

            <h3 class="text-lg font-bold text-blue-700">

                Ready to Deploy

            </h3>

            <p class="mt-2 text-slate-600">

                Your website configuration is complete. Click the button below to generate and deploy your website.

            </p>

        </div>

        <div class="mt-10 flex flex-col gap-4 border-t border-slate-200 pt-8 sm:flex-row sm:items-center sm:justify-between">

            <a
                href="{{ route('websites.administrator', $website) }}"
                class="inline-flex items-center justify-center rounded-xl border border-slate-300 bg-white px-6 py-3 font-medium">

                ← Back

            </a>

            <button
                type="submit"
                class="inline-flex items-center justify-center rounded-xl bg-blue-600 px-8 py-3 font-semibold text-white hover:bg-blue-700">

                🚀 Deploy Website

            </button>

        </div>

    </div>

</form>

@endsection
