{{-- ESUBIZ_SHARED_AUTH_LOGO_V15 --}}
@php
    $resolvedAuthLogo =
        $authLogo
        ?: (
            $defaultLogo
            ?: null
        );

    $resolvedAuthName =
        $authSiteName
        ?? 'Esubiz';
@endphp

<div class="esubiz-auth-brand">
    @if($resolvedAuthLogo)
        <img
            class="esubiz-auth-brand-logo"
            src="{{ $resolvedAuthLogo }}"
            alt="{{ $resolvedAuthName }}"
        >
    @else
        <div class="esubiz-auth-brand-text">
            {{ $resolvedAuthName }}
        </div>
    @endif
</div>
