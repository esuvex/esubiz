<?php

namespace App\Services\Auth;

/**
 * ESUBIZ_REGISTRATION_EXTENSION_REGISTRY_V1
 *
 * Lightweight plug-and-play extension point for Auth Forms.
 *
 * A module can register callbacks/configuration for registration without
 * editing TenantAuthFormService or the public registration controller.
 *
 * Runtime execution will be connected when the multi-form controller
 * pipeline is introduced.
 */
class RegistrationExtensionRegistry
{
    protected array $extensions = [];


    public function register(
        string $key,
        array $definition
    ): void {
        $key = trim($key);

        if ($key === '') {
            throw new \InvalidArgumentException(
                'Registration extension key cannot be empty.'
            );
        }

        $this->extensions[$key] =
            $definition;
    }


    public function all(): array
    {
        return $this->extensions;
    }


    public function get(
        string $key
    ): ?array {
        return $this->extensions[$key]
            ?? null;
    }
}
