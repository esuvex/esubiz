<?php

namespace App\Services\SiteAi;

use App\Services\SiteAi\Contracts\SiteAiCapability;
use InvalidArgumentException;

/**
 * Central registry for every AI-capable website feature.
 *
 * Features register themselves once.
 *
 * The AI Engine does not need custom logic for every new
 * module, theme or website type.
 */
class SiteAiRegistry
{
    /**
     * @var array<string, SiteAiCapability>
     */
    protected array $capabilities = [];


    public function register(
        SiteAiCapability $capability
    ): void {
        $this->capabilities[
            $capability->key()
        ] = $capability;
    }


    public function has(
        string $key
    ): bool {
        return isset(
            $this->capabilities[$key]
        );
    }


    public function get(
        string $key
    ): SiteAiCapability {
        if (!$this->has($key)) {
            throw new InvalidArgumentException(
                "Unknown Site AI capability [{$key}]."
            );
        }

        return $this->capabilities[$key];
    }


    public function all(): array
    {
        return $this->capabilities;
    }
}
