<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="robots" content="noindex,nofollow,noarchive">
    <meta name="referrer" content="no-referrer">
    <title>@yield('title', 'Checkout') | Esubiz</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @include('shared.central.favicon')
    <style>[x-cloak] { display: none !important; }</style>
    @yield('styles')
    @stack('styles')
</head>
<body class="bg-slate-50 text-slate-900">
    <main>
        @yield('content')
    </main>
    @yield('scripts')
    @stack('scripts')
</body>
</html>
