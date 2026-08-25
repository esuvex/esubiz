<?php

namespace App\Services\Core\PageBuilder;

/**
 * ThemeWidgetRenderer
 *
 * Esubiz Core Page Builder architectural boundary.
 *
 * RULES
 * ------------------------------------------------------------------
 * 1. Core Page Builder owns page structure and neutral widget data.
 * 2. Themes own presentation only.
 * 3. Modules own business functionality and functional data.
 * 4. Themes may render module widgets, but must not implement the
 *    module's business logic.
 * 5. Builder documents must never contain theme Blade templates,
 *    theme CSS, executable backend logic, or module business logic.
 * 6. Changing a theme must never delete or rewrite page-builder data.
 * 7. Changing/upgrading a module must never require rebuilding pages.
 *
 * Example:
 *
 *   Ecommerce Module
 *      -> products / cart / checkout / orders
 *
 *   Store Theme
 *      -> presentation of those ecommerce capabilities
 *
 *   Basic Page Builder
 *      -> placement/configuration of ecommerce widgets
 *
 * This contract will be used when public page-builder rendering is
 * connected. Until then it acts as the formal boundary for all new
 * builder development.
 */
interface ThemeWidgetRenderer
{
    /**
     * Theme slug handled by this renderer.
     */
    public function theme(): string;

    /**
     * Determine whether this theme explicitly supports a widget.
     */
    public function supports(string $widgetType): bool;

    /**
     * Return the Blade view used to render a supported widget.
     *
     * The view receives neutral widget data only.
     */
    public function view(string $widgetType): ?string;
}
