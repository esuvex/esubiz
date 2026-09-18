<?php

namespace App\Services\Core\Modules\Registries;

use InvalidArgumentException;

/**
 * ESUBIZ_CORE_MODULE_WIDGET_REGISTRY_V1
 *
 * Runtime registry for Page Builder widgets supplied by enabled modules.
 *
 * Core widgets remain Core-owned.
 * Module widgets are namespaced by module slug and disappear from the
 * active builder catalogue when their owning module is disabled.
 *
 * Saved page content is not deleted when a module is disabled.
 */
class CoreModuleWidgetRegistry
{
    protected array $widgets = [];

    public function register(
        string $moduleSlug,
        string $widgetType,
        array $definition = []
    ): void {
        $moduleSlug = trim($moduleSlug);
        $widgetType = trim($widgetType);

        if ($moduleSlug === '' || $widgetType === '') {
            throw new InvalidArgumentException(
                'Module slug and widget type are required.'
            );
        }

        $key = $moduleSlug . ':' . $widgetType;

        $this->widgets[$key] = array_merge(
            $definition,
            [
                'key' => $key,
                'type' => $widgetType,
                'source_type' => 'module',
                'source_key' => $moduleSlug,
            ]
        );
    }

    public function all(): array
    {
        return array_values($this->widgets);
    }

    public function forModule(string $moduleSlug): array
    {
        return array_values(array_filter(
            $this->widgets,
            static fn (array $widget): bool =>
                ($widget['source_key'] ?? null) === $moduleSlug
        ));
    }

    public function get(string $moduleSlug, string $widgetType): ?array
    {
        return $this->widgets[$moduleSlug . ':' . $widgetType] ?? null;
    }

    public function has(string $moduleSlug, string $widgetType): bool
    {
        return $this->get($moduleSlug, $widgetType) !== null;
    }
}
