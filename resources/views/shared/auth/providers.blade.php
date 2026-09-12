{{-- ESUBIZ_SHARED_AUTH_PROVIDERS_V1 --}}
@if(!empty($authProviders ?? []))
    <div class="esubiz-auth-provider-grid">
        @foreach($authProviders as $provider)
            @php
                $providerKey =
                    $provider['key']
                    ?? '';

                $providerLabel =
                    $provider['label']
                    ?? (
                        'Continue with '
                        . ucfirst($providerKey)
                    );

                $providerUrl =
                    $provider['url']
                    ?? (
                        $authProviderUrlResolver
                            ? $authProviderUrlResolver(
                                $providerKey
                            )
                            : '#'
                    );
            @endphp

            <a
                class="esubiz-auth-provider"
                href="{{ $providerUrl }}"
            >
                {{ $providerLabel }}
            </a>
        @endforeach
    </div>

    <div class="esubiz-auth-divider">
        {{ $authProviderDividerText ?? 'or continue with email' }}
    </div>
@endif
