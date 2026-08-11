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

    public function findWebsiteApplication(int $websiteId): ?ApiApplication
    {
        return ApiApplication::query()
            ->where('website_id', $websiteId)
            ->where('is_active', true)
            ->first();
    }

    public function registerApplication(
        string $name,
        string $slug,
        ?int $userId = null,
        ?int $workspaceId = null,
        ?int $websiteId = null,
        array $redirectUrls = [],
        string $applicationType = 'external',
        array $scopes = []
    ): ApiApplication {
        return ApiApplication::create([
            'workspace_id' => $workspaceId,
            'website_id' => $websiteId,
            'user_id' => $userId,
            'uuid' => \Illuminate\Support\Str::uuid(),
            'name' => $name,
            'slug' => $slug,
            'description' => null,
            'application_type' => $applicationType,
            'client_id' => 'esubiz_' . \Illuminate\Support\Str::random(32),
            'client_secret' => \Illuminate\Support\Str::random(64),
            'redirect_urls' => array_values($redirectUrls),
            'scopes' => array_values($scopes),
            'is_verified' => false,
            'is_active' => true,
        ]);
    }

    /**
     * Register an Esubiz application by its canonical identity.
     *
     * Existing applications are returned instead of creating duplicates.
     */
    public function registerOrGetApplication(
        string $name,
        string $slug,
        string $applicationType,
        array $redirectUrls = [],
        array $scopes = [],
        ?int $userId = null,
        ?int $workspaceId = null
    ): ApiApplication {
        $application = ApiApplication::query()
            ->where('slug', $slug)
            ->first();

        if ($application) {
            return $application;
        }

        return $this->registerApplication(
            name: $name,
            slug: $slug,
            userId: $userId,
            workspaceId: $workspaceId,
            websiteId: null,
            redirectUrls: $redirectUrls,
            applicationType: $applicationType,
            scopes: $scopes,
        );
    }

    /**
     * Find an active application by application type.
     */
    public function findByType(string $applicationType): ?ApiApplication
    {
        return ApiApplication::query()
            ->where('application_type', $applicationType)
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

    public function findByAccessToken(string $accessToken): ?\App\Models\ApiAuthorization
    {
        return \App\Models\ApiAuthorization::query()
            ->where('access_token_hash', hash('sha256', $accessToken))
            ->where('is_revoked', false)
            ->where('access_token_expires_at', '>', now())
            ->with('user')
            ->first();
    }

    public function consumeAuthorizationCode(
        ApiApplication $application,
        string $authorizationCode
    ): ?\App\Models\ApiAuthorization {
        $authorization = \App\Models\ApiAuthorization::query()
            ->where('api_application_id', $application->id)
            ->where('authorization_code', $authorizationCode)
            ->where('is_revoked', false)
            ->whereNull('code_consumed_at')
            ->whereNotNull('approved_at')
            ->where('expires_at', '>', now())
            ->first();

        if (!$authorization) {
            return null;
        }

        $accessToken = \Illuminate\Support\Str::random(80);

        $authorization->update([
            'code_consumed_at' => now(),
            'access_token_hash' => hash('sha256', $accessToken),
            'access_token_expires_at' => now()->addHours(1),
        ]);

        $authorization->access_token = $accessToken;

        return $authorization;
    }
}
