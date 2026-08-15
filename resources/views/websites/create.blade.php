@extends('admin.layouts.app')

@section('title', 'Create Website')

@section('content')

@php

$step = 1;
$steps = 5;

@endphp

<form method="POST" action="{{ route('websites.store') }}">

    @csrf

    <div class="rounded-3xl bg-slate-100 p-8">

        <div class="flex flex-col gap-6 lg:flex-row lg:items-start lg:justify-between">

            <div>

                <h1 class="text-4xl font-bold text-slate-900">

                    Create Website

                </h1>

                <p class="mt-3 text-slate-500">

                    Choose the type of website you want to create.

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

                <div class="flex shrink-0 justify-center">

                    <div
                        class="flex h-12 w-12 items-center justify-center rounded-full border-2 text-sm font-bold
                        {{ $i == $step
                            ? 'border-blue-600 bg-blue-600 text-white'
                            : 'border-slate-300 bg-white text-slate-500' }}">

                        {{ $i }}

                    </div>

                </div>

            @endfor

        </div>

        <div class="mt-10 grid gap-6 md:grid-cols-2 xl:grid-cols-3">

            @foreach($types as $type)

                <label class="group cursor-pointer">

                    <input
                        type="radio"
                        name="type"
                        value="{{ $type->slug }}"
                        class="peer hidden"
                        required>

                    <div class="rounded-3xl border-2 border-slate-200 bg-white p-8 transition-all duration-300 hover:-translate-y-1 hover:border-blue-500 hover:shadow-xl peer-checked:border-blue-600 peer-checked:bg-blue-50">

                        <div class="flex items-start justify-between">

                            <div class="text-6xl">

                                {{ config('website_type_icons.' . $type->icon, '🌐') }}

                            </div>

                            <span class="rounded-full bg-slate-100 px-4 py-2 text-xs font-semibold text-slate-500">

                                Website Type

                            </span>

                        </div>

                        <h3 class="mt-8 text-3xl font-bold text-slate-900">

                            {{ $type->name }}

                        </h3>

                        <p class="mt-4 leading-7 text-slate-500">

                            {{ $type->description ?: 'Create a professional website for your business or organization.' }}

                        </p>

                    </div>

                </label>

            @endforeach

        </div>
        <div class="mt-10 flex flex-col gap-4 border-t border-slate-200 pt-8 sm:flex-row sm:items-center sm:justify-between">

            <a
                href="{{ route('user.dashboard') }}"
                class="inline-flex items-center justify-center rounded-xl border border-slate-300 bg-white px-6 py-3 font-medium text-slate-700 transition hover:bg-slate-100">

                ← Back to Dashboard

            </a>

            <button
                type="submit"
                class="inline-flex items-center justify-center rounded-xl bg-blue-600 px-8 py-3 font-semibold text-white transition hover:bg-blue-700">

                Continue →

            </button>

        </div>

    </div>

</form>

@endsection
