@props([
    'title',
    'heading',
    'description' => null,
    'step' => 1,
    'steps' => 8,
])

@extends('layouts.dashboard')

@section('content')

<div class="mx-auto max-w-7xl">

    <div class="mb-8 flex items-start justify-between">

        <div>

            <h1 class="text-4xl font-bold text-slate-900">
                {{ $heading }}
            </h1>

            @if($description)
                <p class="mt-3 text-lg text-slate-500">
                    {{ $description }}
                </p>
            @endif

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

    <div class="mb-10 grid grid-cols-4 gap-4 md:flex md:items-center">

        @for($i = 1; $i <= $steps; $i++)

            <div class="flex justify-center md:flex-1">

                <div class="flex h-12 w-12 items-center justify-center rounded-full border-2 text-sm font-bold {{ $i == $step ? 'border-blue-600 bg-blue-600 text-white' : 'border-slate-300 bg-white text-slate-500' }}">

                    {{ $i }}

                </div>

            </div>

        @endfor

    </div>

    <div class="rounded-3xl bg-white p-8 shadow-sm">

        {{ $slot }}

    </div>

</div>

@endsection
