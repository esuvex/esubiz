{{-- ESUBIZ_SHARED_AUTH_FAVICON_V15 --}}
@php
    $resolvedAuthFavicon =
        $authFavicon
        ?: (
            $defaultFavicon
            ?: null
        );
@endphp

@if($resolvedAuthFavicon)
    <link
        rel="icon"
        type="image/x-icon"
        href="{{ $resolvedAuthFavicon }}"
    >

    <link
        rel="shortcut icon"
        href="{{ $resolvedAuthFavicon }}"
    >

    <link
        rel="apple-touch-icon"
        href="{{ $resolvedAuthFavicon }}"
    >
@endif
