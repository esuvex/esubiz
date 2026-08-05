@extends('layouts.app')

@section('title', 'Choose a Plan')

@section('content')

<div class="max-w-7xl mx-auto px-6 py-10">

    <div class="mb-10">

        <span class="text-sm font-semibold text-blue-600 uppercase">
            Step 3 of 7
        </span>

        <h1 class="mt-2 text-4xl font-bold text-slate-900">
            Choose Your Plan
        </h1>

        <p class="mt-3 text-lg text-slate-500">
            Select the plan that best fits your website. You can upgrade or downgrade later at any time.
        </p>

    </div>

    <div class="mb-8 rounded-2xl border bg-slate-50 p-6">

        <h2 class="text-xl font-semibold">
            Website Summary
        </h2>

        <div class="mt-5 grid md:grid-cols-3 gap-6">

            <div>

                <p class="text-sm text-slate-500">
                    Website Name
                </p>

                <p class="mt-1 text-lg font-semibold">
                    {{ $website['name'] }}
                </p>

            </div>

            <div>

                <p class="text-sm text-slate-500">
                    Website Type
                </p>

                <p class="mt-1 text-lg font-semibold capitalize">
                    {{ $website['type'] }}
                </p>

            </div>

            <div>

                <p class="text-sm text-slate-500">
                    Selected Theme
                </p>

                <p class="mt-1 text-lg font-semibold capitalize">
                    {{ $website['theme'] }}
                </p>

            </div>

        </div>

    </div>

    <form method="GET" action="#">

        <div class="grid lg:grid-cols-3 gap-8">

            @foreach($plans as $plan)

                <label class="cursor-pointer rounded-3xl border-2 border-slate-200 hover:border-blue-600 transition bg-white p-8">

                    <input
                        type="radio"
                        name="plan_id"
                        value="{{ $plan['id'] }}"
                        class="mb-6"
                        required>

                    <h2 class="text-3xl font-bold">
                        {{ $plan['name'] }}
                    </h2>

                    <p class="mt-4 text-5xl font-bold text-blue-600">
                        {{ $plan['price'] }}
                    </p>

                    <p class="mt-5 text-slate-500 leading-7">
                        {{ $plan['description'] }}
                    </p>

                </label>

            @endforeach

        </div>

        <div class="mt-10 flex justify-between">

            <a href="{{ route('websites.theme', request()->query()) }}"
               class="px-8 py-4 rounded-2xl border bg-white hover:bg-slate-50">

                Back

            </a>

            <button
                type="submit"
                class="px-10 py-4 rounded-2xl bg-blue-600 text-white font-semibold hover:bg-blue-700">

                Continue

            </button>

        </div>

    </form>

</div>

@endsection
