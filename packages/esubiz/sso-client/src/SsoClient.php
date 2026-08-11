<?php

namespace Esubiz\SsoClient;

use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use RuntimeException;

class SsoClient
{
    protected array $config;

    public function __construct(array $config = [])
    {
        $this->config = $config;
    }

    public function issuer(): string
    {
        return rtrim((string) ($this->config['issuer'] ?? ''), '/');
    }

    public function clientId(): string
    {
        return (string) ($this->config['client_id'] ?? '');
    }

    public function clientSecret(): string
    {
        return (string) ($this->config['client_secret'] ?? '');
    }

    public function redirectUri(): string
    {
        return (string) ($this->config['redirect_uri'] ?? '');
    }

    public function scopes(): array
    {
        return array_values(
            array_filter(
                $this->config['scopes'] ?? ['identity.read']
            )
        );
    }

    public function authorizationEndpoint(): string
    {
        return $this->issuer() . '/oauth/authorize';
    }

    public function tokenEndpoint(): string
    {
        return $this->issuer() . '/oauth/token';
    }

    public function userEndpoint(): string
    {
        return $this->issuer() . '/oauth/user';
    }

    public function authorizationUrl(?string $state = null): string
    {
        $state ??= Str::random(64);

        Session::put('esubiz_sso_state', $state);

        $query = [
            'client_id' => $this->clientId(),
            'redirect_uri' => $this->redirectUri(),
            'scope' => implode(' ', $this->scopes()),
            'response_type' => 'code',
            'state' => $state,
        ];

        return $this->authorizationEndpoint()
            . '?'
            . http_build_query($query);
    }

    public function login(?string $state = null): \Symfony\Component\HttpFoundation\RedirectResponse
    {
        if (!$this->config['enabled'] ?? true) {
            throw new RuntimeException('Esubiz SSO is disabled.');
        }

        return redirect()->away(
            $this->authorizationUrl($state)
        );
    }

    public function exchangeCode(string $code): array
    {
        $response = Http::asForm()
            ->acceptJson()
            ->post($this->tokenEndpoint(), [
                'client_id' => $this->clientId(),
                'client_secret' => $this->clientSecret(),
                'code' => $code,
                'redirect_uri' => $this->redirectUri(),
                'grant_type' => 'authorization_code',
            ]);

        return $this->decodeResponse($response);
    }

    public function user(string $accessToken): array
    {
        $response = Http::acceptJson()
            ->withToken($accessToken)
            ->get($this->userEndpoint());

        return $this->decodeResponse($response);
    }

    public function callback(string $code, ?string $state = null): array
    {
        if ($state !== null) {
            $expectedState = Session::pull('esubiz_sso_state');

            if (!$expectedState || !hash_equals($expectedState, $state)) {
                throw new RuntimeException('Invalid SSO state.');
            }
        }

        return $this->exchangeCode($code);
    }

    public function logout(): void
    {
        Session::forget([
            'esubiz_sso_state',
            'esubiz_sso_access_token',
            'esubiz_sso_user',
        ]);
    }

    protected function decodeResponse(Response $response): array
    {
        if ($response->failed()) {
            throw new RuntimeException(
                'Esubiz SSO request failed: '
                . $response->status()
                . ' '
                . $response->body()
            );
        }

        $data = $response->json();

        if (!is_array($data)) {
            throw new RuntimeException(
                'Esubiz SSO returned an invalid response.'
            );
        }

        return $data;
    }
}
