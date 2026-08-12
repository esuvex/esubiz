<?php

namespace App\Services\Sso;

use App\Models\ApiApplication;

class SsoApplicationRegistry
{
    /**
     * Canonical Esubiz applications.
     *
     * This registry describes applications that can connect to
     * the central Esubiz SSO system. Credentials are never stored here.
     */
    public function definitions(): array
    {
        return [

            'marketplace' => [
                'name' => 'Esubiz Marketplace',
                'slug' => 'marketplace',
                'type' => 'marketplace',
                'url' => 'https://marketplace.esubiz.com',
                'redirect_uri' => 'https://marketplace.esubiz.com/sso/callback',
                'scopes' => ['identity.read'],
            ],

            'api' => [
                'name' => 'Esubiz API',
                'slug' => 'api',
                'type' => 'api',
                'url' => 'https://api.esubiz.com',
                'redirect_uri' => 'https://api.esubiz.com/sso/callback',
                'scopes' => ['identity.read'],
            ],

            'developer' => [
                'name' => 'Esubiz Developer',
                'slug' => 'developer',
                'type' => 'developer',
                'url' => 'https://dev.esubiz.com',
                'redirect_uri' => 'https://dev.esubiz.com/sso/callback',
                'scopes' => ['identity.read'],
            ],

            'partners' => [
                'name' => 'Esubiz Partners',
                'slug' => 'partners',
                'type' => 'partners',
                'url' => 'https://partners.esubiz.com',
                'redirect_uri' => 'https://partners.esubiz.com/sso/callback',
                'scopes' => ['identity.read'],
            ],

        ];
    }


    /**
     * Synchronize all canonical Esubiz applications with the
     * central ApiApplication registry.
     *
     * Existing applications are reused and never duplicated.
     */
    public function synchronize(): array
    {
        $results = [];

        foreach ($this->definitions() as $key => $definition) {
            $application = ApiApplication::query()
                ->where('slug', $definition['slug'])
                ->first();

            if (!$application) {
                $application = app(SsoService::class)->registerApplication(
                    name: $definition['name'],
                    slug: $definition['slug'],
                    redirectUrls: [$definition['redirect_uri']],
                    applicationType: $definition['type'],
                    scopes: $definition['scopes'],
                );
            } else {
                $application->update([
                    'name' => $definition['name'],
                    'application_type' => $definition['type'],
                    'redirect_urls' => [$definition['redirect_uri']],
                    'scopes' => $definition['scopes'],
                    'is_active' => true,
                ]);
            }

            foreach ($definition['scopes'] as $scopeSlug) {
                $scope = \App\Models\ApiScope::query()
                    ->where('slug', $scopeSlug)
                    ->first();

                if ($scope) {
                    \App\Models\ApiApplicationScope::updateOrCreate(
                        [
                            'api_application_id' => $application->id,
                            'api_scope_id' => $scope->id,
                        ],
                        [
                            'is_allowed' => true,
                        ]
                    );
                }
            }

            $results[$key] = $application->fresh();
        }

        return $results;
    }

    /**
     * Get one application definition.
     */
    public function get(string $key): ?array
    {
        return $this->definitions()[$key] ?? null;
    }

    /**
     * Determine whether an application is registered in the central catalog.
     */
    public function has(string $key): bool
    {
        return array_key_exists($key, $this->definitions());
    }
}
