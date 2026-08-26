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
        if ($this->has($key)) {
            return $this->capabilities[$key];
        }

        /*
         * Site AI UI capabilities such as:
         *
         * features
         * testimonials
         * hero
         * about
         * services
         * products
         *
         * are dynamic website editing targets, not separate
         * AI engines.
         *
         * When no capability-specific implementation has been
         * registered, resolve the generic Site AI capability.
         *
         * This keeps Site AI plug-and-play: modules, themes and
         * future website types can expose new AI functions without
         * requiring a new central registry entry for every function.
         */
        if (
            $key !== 'site'
            && $this->has('site')
        ) {
            return $this->capabilities['site'];
        }

        throw new InvalidArgumentException(
            "Unknown Site AI capability [{$key}]."
        );
    }


    public function all(): array
    {
        return $this->capabilities;
    }
}
