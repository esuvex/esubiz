<?php

namespace App\Services\Sso;

use App\Models\ApiApplication;

class SsoService
{
    /**
     * Find an active SSO application by client ID.
     */
    public function findApplication(string $clientId): ?ApiApplication
    {
        return ApiApplication::query()
            ->where('client_id', $clientId)
            ->where('is_active', true)
            ->first();
    }

    public function isValidRedirect(
        ApiApplication $application,
        string $redirectUri
    ): bool {
        return in_array(
            $redirectUri,
            $application->redirect_urls ?? [],
            true
        );
    }

    public function validateAuthorizationRequest(
        ApiApplication $application,
        string $redirectUri
    ): bool {
        return $this->isValidRedirect($application, $redirectUri);
    }

    public function validateScopes(
        ApiApplication $application,
        array $requestedScopes
    ): bool {
        $allowedScopes = $application->applicationScopes()
            ->where('is_allowed', true)
            ->with('scope')
            ->get()
            ->pluck('scope.slug')
            ->filter()
            ->values()
            ->all();

        return empty(array_diff($requestedScopes, $allowedScopes));
    }

    public function createAuthorization(
        ApiApplication $application,
        int $userId,
        array $scopes
    ): \App\Models\ApiAuthorization {
        return \App\Models\ApiAuthorization::create([
            'api_application_id' => $application->id,
            'user_id' => $userId,
            'uuid' => (string) \Illuminate\Support\Str::uuid(),
            'authorization_code' => \Illuminate\Support\Str::random(80),
            'scopes' => array_values($scopes),
            'approved_at' => now(),
            'expires_at' => now()->addMinutes(5),
        ]);
    }

    public function validateClientSecret(
        ApiApplication $application,
        string $clientSecret
    ): bool {
        return $application->client_secret !== null
            && hash_equals($application->client_secret, $clientSecret);
    }

    public function consumeAuthorizationCode(
        ApiApplication $application,
        string $authorizationCode
    ): ?\App\Models\ApiAuthorization {
        $authorization = \App\Models\ApiAuthorization::query()
            ->where('api_application_id', $application->id)
            ->where('authorization_code', $authorizationCode)
            ->where('is_revoked', false)
            ->whereNotNull('approved_at')
            ->where('expires_at', '>', now())
            ->first();

        if (!$authorization) {
            return null;
        }

        $accessToken = \Illuminate\Support\Str::random(80);

        $authorization->update([
            'is_revoked' => true,
            'revoked_at' => now(),
            'code_consumed_at' => now(),
            'access_token_hash' => hash('sha256', $accessToken),
            'access_token_expires_at' => now()->addHours(1),
        ]);

        $authorization->access_token = $accessToken;

        return $authorization;
    }
}
