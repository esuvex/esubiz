<?php

namespace App\Services\Core\Modules;

use Illuminate\Support\Collection;

/**
 * ESUBIZ_CORE_MODULE_THEME_COMPATIBILITY_V1
 *
 * Central Admin is authoritative for Module <-> Theme compatibility.
 *
 * Example:
 * Ecommerce Module may be configured to work only with selected
 * Ecommerce Themes.
 *
 * Core must never treat every installed or Marketplace Theme as
 * automatically compatible with a Module.
 *
 * Expected Central-owned Module metadata:
 *
 * compatible_themes: [
 *     "theme-slug-one",
 *     "theme-slug-two"
 * ]
 *
 * Theme installation and activation remain owned by the existing
 * Core Theme system.
 */
class CoreModuleThemeCompatibilityService
{
    /**
     * Return normalized Theme slugs configured by Central Admin.
     */
    public function compatibleThemeSlugs(
        array $moduleMetadata
    ): array {
        return collect(
            $moduleMetadata['compatible_themes']
            ?? []
        )
            ->map(
                fn ($slug) =>
                    strtolower(
                        trim((string) $slug)
                    )
            )
            ->filter()
            ->unique()
            ->values()
            ->all();
    }

    /**
     * Determine whether a Theme is compatible with a Module.
     */
    public function isCompatible(
        array $moduleMetadata,
        string $themeSlug
    ): bool {
        $themeSlug = strtolower(
            trim($themeSlug)
        );

        if ($themeSlug === '') {
            return false;
        }

        return in_array(
            $themeSlug,
            $this->compatibleThemeSlugs(
                $moduleMetadata
            ),
            true
        );
    }

    /**
     * Filter any Theme collection to only the Themes explicitly
     * approved for this Module by Central Admin.
     */
    public function filter(
        Collection $themes,
        array $moduleMetadata
    ): Collection {
        $allowed = $this->compatibleThemeSlugs(
            $moduleMetadata
        );

        if ($allowed === []) {
            return collect();
        }

        return $themes
            ->filter(
                function ($theme) use ($allowed) {
                    $slug = is_array($theme)
                        ? ($theme['slug'] ?? null)
                        : ($theme->slug ?? null);

                    return in_array(
                        strtolower(
                            trim((string) $slug)
                        ),
                        $allowed,
                        true
                    );
                }
            )
            ->values();
    }
}
