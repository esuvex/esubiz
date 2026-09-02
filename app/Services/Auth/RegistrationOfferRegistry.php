<?php

namespace App\Services\Auth;

use App\Contracts\Auth\RegistrationOfferProvider;

/**
 * ESUBIZ_REGISTRATION_OFFER_REGISTRY_V1
 *
 * Runtime registry for plug-and-play registration offer providers.
 *
 * Modules/extensions register providers during application boot.
 * Auth Forms discover them dynamically instead of knowing module names.
 */
class RegistrationOfferRegistry
{
    /** @var array<string, RegistrationOfferProvider> */
    protected array $providers = [];


    public function register(
        RegistrationOfferProvider $provider
    ): void {
        $key = trim($provider->key());

        if ($key === '') {
            throw new \InvalidArgumentException(
                'Registration offer provider key cannot be empty.'
            );
        }

        $this->providers[$key] = $provider;
    }


    public function providers(): array
    {
        return array_filter(
            $this->providers,
            fn (RegistrationOfferProvider $provider) =>
                $provider->available()
        );
    }


    public function provider(
        string $key
    ): ?RegistrationOfferProvider {
        $provider =
            $this->providers[$key]
            ?? null;

        if (
            !$provider
            || !$provider->available()
        ) {
            return null;
        }

        return $provider;
    }


    public function offers(): array
    {
        $result = [];

        foreach ($this->providers() as $key => $provider) {
            $result[$key] = [
                'provider' => $key,
                'name' => $provider->name(),
                'items' => array_values(
                    $provider->offers()
                ),
            ];
        }

        return $result;
    }


    /**
     * Resolve provider + offer at runtime.
     *
     * Auth Forms save:
     * [
     *   'provider' => '...',
     *   'offer_id' => '...',
     *   'enabled'  => true,
     * ]
     *
     * They do not copy provider database records.
     */
    public function resolve(
        string $providerKey,
        string $offerId
    ): ?array {
        $provider =
            $this->provider($providerKey);

        if (!$provider) {
            return null;
        }

        $offer =
            $provider->resolve($offerId);

        if (!$offer) {
            return null;
        }

        return array_merge(
            $offer,
            [
                'provider' =>
                    $providerKey,
            ]
        );
    }
}
