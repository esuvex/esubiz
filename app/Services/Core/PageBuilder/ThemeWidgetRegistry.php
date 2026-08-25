<?php

namespace App\Services\Core\PageBuilder;

/**
 * Central registry for theme-aware Page Builder rendering.
 *
 * IMPORTANT:
 * This registry contains presentation mappings only.
 *
 * Widget definitions and saved page documents belong to Core.
 * Module functionality belongs to modules.
 * Theme-specific rendering belongs to themes.
 */
class ThemeWidgetRegistry
{
    /**
     * Built-in theme aliases.
     *
     * Business is the public name/slug.
     * corporate-default remains as a compatibility alias for the
     * original built-in theme directory.
     */
    protected array $themeAliases = [
        'business' => 'business',
        'corporate-default' => 'business',
    ];

    /**
     * Theme view namespaces.
     *
     * Each theme can progressively add:
     *
     * resources/views/tenant/themes/<theme>/builder/widgets/
     *
     * No page-builder data is stored in these views.
     */
    protected array $themeNamespaces = [
        'business' =>
            'tenant.themes.corporate-default.builder.widgets',
    ];

    /**
     * Widgets owned by Esubiz Core.
     *
     * Modules can register additional widget types later without
     * changing this saved document structure.
     */
    protected array $coreWidgets = [
        'hero',
        'heading',
        'text',
        'image',
        'gallery',
        'button',
        'cta',
        'features',
        'cards',
        'testimonials',
        'faq',
        'stats',
        'section',
        'columns',
        'divider',
        'spacer',
        'video',
        'map',
        'form',
        'social',
        'panorama',
        'html',
    ];

    public function normalizeTheme(
        ?string $theme
    ): string {
        $theme = trim(
            (string) $theme
        );

        if ($theme === '') {
            return 'business';
        }

        return $this->themeAliases[$theme]
            ?? $theme;
    }

    public function namespaceFor(
        ?string $theme
    ): ?string {
        $theme =
            $this->normalizeTheme(
                $theme
            );

        return $this->themeNamespaces[$theme]
            ?? null;
    }

    public function coreWidgets(): array
    {
        return $this->coreWidgets;
    }

    public function isCoreWidget(
        string $widgetType
    ): bool {
        return in_array(
            $widgetType,
            $this->coreWidgets,
            true
        );
    }

    /**
     * Resolve a theme-specific widget view.
     *
     * Returns null when the active theme does not provide its own
     * representation. Core can then use its neutral fallback renderer.
     */
    public function resolveView(
        ?string $theme,
        string $widgetType
    ): ?string {
        $namespace =
            $this->namespaceFor(
                $theme
            );

        if (!$namespace) {
            return null;
        }

        $view =
            $namespace
            . '.'
            . $widgetType;

        return view()->exists($view)
            ? $view
            : null;
    }
}
