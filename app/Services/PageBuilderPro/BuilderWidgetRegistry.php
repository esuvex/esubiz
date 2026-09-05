<?php

namespace App\Services\PageBuilderPro;

use App\Services\Core\PageBuilder\ThemeWidgetRegistry;

/**
 * ESUBIZ_PAGE_BUILDER_WIDGET_OWNERSHIP_REGISTRY_V2
 *
 * One ownership map for the builder.
 *
 * IMPORTANT:
 * - Core continues to own its existing 22 Basic widgets.
 * - Modules own the widgets they register.
 * - Page Builder Pro owns only Pro-only widgets.
 * - This registry does NOT copy, rewrite or own Core widget data.
 */
class BuilderWidgetRegistry
{
    protected array $modules = [];

    public function __construct(
        protected ThemeWidgetRegistry $core,
        protected ProWidgetRegistry $pro
    ) {
        $this->assertNoCoreProCollisions();
    }

    public function coreWidgets(): array
    {
        return $this->core->coreWidgets();
    }

    public function proWidgets(): array
    {
        return $this->pro->all();
    }

    public function moduleWidgets(): array
    {
        return $this->modules;
    }

    public function has(string $type): bool
    {
        return $this->core->isCoreWidget($type)
            || isset($this->modules[$type])
            || $this->pro->has($type);
    }

    public function source(string $type): ?string
    {
        if ($this->core->isCoreWidget($type)) {
            return 'core';
        }

        if (isset($this->modules[$type])) {
            return 'module';
        }

        if ($this->pro->has($type)) {
            return 'pro';
        }

        return null;
    }

    /**
     * Modules register their widgets here as Basic-tier widgets.
     *
     * Their functionality/data remains owned by the module.
     */
    public function registerModuleWidget(
        string $type,
        array $definition,
        ?string $module = null
    ): void {
        $type = trim($type);

        if ($type === '') {
            throw new \InvalidArgumentException(
                'Module widget type cannot be empty.'
            );
        }

        if ($this->has($type)) {
            throw new \InvalidArgumentException(
                "Page Builder widget [{$type}] is already owned by "
                . ($this->source($type) ?? 'another source')
                . '.'
            );
        }

        $definition['source'] = $module ?: 'module';
        $definition['tier'] = 'basic';

        $this->modules[$type] = $definition;
    }

    public function registerModuleWidgets(
        array $widgets,
        ?string $module = null
    ): void {
        foreach ($widgets as $type => $definition) {
            if (!is_string($type) || !is_array($definition)) {
                throw new \InvalidArgumentException(
                    'Module widgets must use type => definition arrays.'
                );
            }

            $this->registerModuleWidget(
                $type,
                $definition,
                $module
            );
        }
    }

    protected function assertNoCoreProCollisions(): void
    {
        $coreTypes = array_flip(
            $this->core->coreWidgets()
        );

        $collisions = array_intersect_key(
            $this->pro->all(),
            $coreTypes
        );

        if ($collisions !== []) {
            throw new \LogicException(
                'Core/Pro widget collision: '
                . implode(', ', array_keys($collisions))
            );
        }
    }
}
