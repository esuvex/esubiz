@extends('admin.layouts.app')

@section('title', 'Choose Plan')

@section('content')

@php
$step = 4;
$steps = 9;

$plans = [
    [
        'id' => 'starter',
        'name' => 'Starter',
        'price' => '₦4,000 / month',
        'description' => 'Perfect for small businesses.',
    ],
    [
        'id' => 'business',
        'name' => 'Business',
        'price' => '₦8,000 / month',
        'description' => 'Best for growing companies.',
    ],
    [
        'id' => 'enterprise',
        'name' => 'Enterprise',
        'price' => 'Custom',
        'description' => 'Advanced features for large organizations.',
    ],
];
@endphp

<form method="GET" action="{{ route('websites.domain', $website) }}">

    @foreach(request()->except('plan') as $key => $value)
        <input type="hidden" name="{{ $key }}" value="{{ $value }}">
    @endforeach

    <div class="rounded-3xl bg-slate-100 p-8">

        <div class="flex flex-col gap-6 lg:flex-row lg:items-start lg:justify-between">

            <div>

                <h1 class="text-4xl font-bold text-slate-900">
                    Choose Your Plan
                </h1>

                <p class="mt-3 text-slate-500">
                    Select the subscription plan for your website.
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

        <div class="mt-10 grid gap-6 lg:grid-cols-3">

            @foreach($plans as $plan)

                <label class="cursor-pointer">

                    <input
                        type="radio"
                        name="plan"
                        value="{{ $plan['id'] }}"
                        class="peer hidden"
                        required>

                    <div class="rounded-3xl border-2 border-slate-200 bg-white p-8 transition-all duration-300 hover:-translate-y-1 hover:border-blue-500 hover:shadow-xl peer-checked:border-blue-600 peer-checked:bg-blue-50">

                        <h3 class="text-3xl font-bold text-slate-900">
                            {{ $plan['name'] }}
                        </h3>

                        <div class="mt-6 text-2xl font-bold text-blue-600">
                            {{ $plan['price'] }}
                        </div>

                        <p class="mt-4 text-slate-500">
                            {{ $plan['description'] }}
                        </p>

                    </div>

                </label>

            @endforeach

        </div>

        <div class="mt-10 flex flex-col gap-4 border-t border-slate-200 pt-8 sm:flex-row sm:items-center sm:justify-between">

            <a
                href="{{ route('websites.information', $website) }}"
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
