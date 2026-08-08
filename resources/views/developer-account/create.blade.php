@extends('admin.layouts.app')

@section('title', 'Developer Account')

@section('content')

<div class="max-w-3xl mx-auto">

    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-8">

        <div class="mb-8">

            <h1 class="text-2xl font-bold text-slate-800">
                Switch to Developer Account
            </h1>

            <p class="mt-2 text-slate-500">
                Become a developer on Esubiz and access developer tools,
                website compilation, APIs, modules and developer resources.
            </p>

        </div>

        <div class="bg-slate-50 rounded-xl p-5 mb-8">

            <h2 class="font-semibold text-slate-800 mb-3">
                Developer Access
            </h2>

            <ul class="space-y-2 text-sm text-slate-600">
                <li>• Developer Dashboard</li>
                <li>• Website Builder & Compiler</li>
                <li>• API development tools</li>
                <li>• Theme and module development</li>
                <li>• Developer revenue and earnings</li>
            </ul>

        </div>

        <form method="POST" action="#">

            @csrf

            <button
                type="submit"
                class="w-full rounded-xl bg-blue-600 px-5 py-3 font-semibold text-white hover:bg-blue-700">
                Continue Developer Registration
            </button>

        </form>

    </div>

</div>

@endsection
