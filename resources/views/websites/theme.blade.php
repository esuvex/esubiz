@extends('layouts.app')

@section('title', 'Choose Theme')

@section('content')

<div class="max-w-7xl mx-auto px-6 py-10">

    <div class="mb-10">

        <span class="text-sm font-semibold text-blue-600 uppercase">
            Step 2 of 7
        </span>

        <h1 class="mt-2 text-4xl font-bold text-slate-900">
            Choose a Theme
        </h1>

        <p class="mt-3 text-lg text-slate-500">
            Every website type comes with three professionally designed layouts.
            You can always change your theme later or install additional themes
            from the Marketplace.
        </p>

    </div>

    <div class="mb-8 rounded-2xl border bg-slate-50 p-6">

        <h2 class="text-xl font-semibold">
            Website Details
        </h2>

        <div class="mt-4 grid md:grid-cols-2 gap-6">

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

        </div>

    </div>

    <form method="GET" action="#">

        <div class="grid lg:grid-cols-3 gap-8">

            @foreach($themes as $theme)

                <label
                    class="cursor-pointer rounded-3xl border-2 border-slate-200 hover:border-blue-500 transition bg-white overflow-hidden">

                    <div class="aspect-[16/10] bg-slate-100 flex items-center justify-center">

                        <span class="text-slate-400 text-lg">
                            Theme Preview
                        </span>

                    </div>

                    <div class="p-6">

                        <input
                            type="radio"
                            name="theme"
                            value="{{ $theme['id'] }}"
                            class="mb-5"
                            required>

                        <h3 class="text-2xl font-bold">
                            {{ $theme['name'] }}
                        </h3>

                        <p class="mt-3 text-slate-500">
                            {{ $theme['description'] }}
                        </p>

                    </div>

                </label>

            @endforeach

        </div>

        <div class="mt-10 flex justify-between">

            <a href="{{ route('websites.create') }}"
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
