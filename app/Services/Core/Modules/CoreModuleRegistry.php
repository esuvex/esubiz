<?php

namespace App\Services\Core\Modules;

use App\Services\Core\Modules\Contracts\CoreModule;
use InvalidArgumentException;

/**
 * ESUBIZ_CORE_MODULE_RUNTIME_REGISTRY_V1
 *
 * Runtime authority for enabled Esubiz Core modules.
 *
 * Only enabled modules are registered here.
 *
 * An enabled module may then submit:
 * - Core-compatible pages/features
 * - sidebar submenu items
 * - permissions
 * - Page Builder widgets
 * - routes/services/runtime integrations
 *
 * Disabling a module removes it from runtime without deleting
 * the module's stored business data.
 */
class CoreModuleRegistry
{
    /**
     * @var array<string, CoreModule>
     */
    protected array $modules = [];

    public function register(CoreModule $module): void
    {
        $slug = trim($module->slug());

        if ($slug === '') {
            throw new InvalidArgumentException(
                'Core module slug cannot be empty.'
            );
        }

        $this->modules[$slug] = $module;
    }

    public function has(string $slug): bool
    {
        return isset($this->modules[trim($slug)]);
    }

    public function get(string $slug): ?CoreModule
    {
        return $this->modules[trim($slug)] ?? null;
    }

    /**
     * @return array<string, CoreModule>
     */
    public function all(): array
    {
        return $this->modules;
    }

    public function slugs(): array
    {
        return array_keys($this->modules);
    }

    public function registerIntegrations(): void
    {
        foreach ($this->modules as $module) {
            $module->register();
        }
    }

    public function bootModules(): void
    {
        foreach ($this->modules as $module) {
            $module->boot();
        }
    }
}
