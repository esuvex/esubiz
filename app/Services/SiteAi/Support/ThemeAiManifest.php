<?php

namespace App\Services\SiteAi\Support;

/**
 * Loads the Site AI manifest supplied by a theme.
 *
 * Themes own their AI-editable feature declarations.
 * Central ESUBIZ only discovers and validates them.
 *
 * Convention:
 *
 * resources/views/tenant/themes/{theme}/ai-manifest.php
 */
class ThemeAiManifest
{
    public function load(
        ?string $themeKey
    ): array {
        $themeKey =
            $this->safeThemeKey(
                $themeKey
            );

        if ($themeKey === null) {
            return [];
        }

        /*
         * ESUBIZ_THEME_AI_MANIFEST_ALIAS_V1
         *
         * theme.active stores the installed/runtime theme key.
         *
         * A theme package may use a different directory key.
         * Current built-in Business compatibility:
         *
         * business -> corporate-default
         *
         * Future marketplace package resolution can replace
         * or extend this map without changing Site AI.
         */
        $packageThemeKey =
            $this->packageThemeKey(
                $themeKey
            );

        $path =
            resource_path(
                'views/tenant/themes/'
                . $packageThemeKey
                . '/ai-manifest.php'
            );

        if (!is_file($path)) {
            return [];
        }

        /*
         * Theme manifest files are application/theme code,
         * not tenant supplied runtime PHP.
         */
        $manifest =
            require $path;

        if (!is_array($manifest)) {
            return [];
        }

        /*
         * The requested active theme remains authoritative.
         * A manifest cannot impersonate another theme.
         */
        /*
         * Preserve both identities.
         *
         * theme_key:
         *     actual active/runtime theme.
         *
         * package_theme_key:
         *     package/directory providing the manifest.
         */
        $manifest['theme_key'] =
            $themeKey;

        $manifest['package_theme_key'] =
            $packageThemeKey;

        if (
            !isset($manifest['functions'])
            || !is_array(
                $manifest['functions']
            )
        ) {
            $manifest['functions'] =
                [];
        }

        return $manifest;
    }


    public function exists(
        ?string $themeKey
    ): bool {
        $themeKey =
            $this->safeThemeKey(
                $themeKey
            );

        if ($themeKey === null) {
            return false;
        }

        return is_file(
            resource_path(
                'views/tenant/themes/'
                . $themeKey
                . '/ai-manifest.php'
            )
        );
    }


    protected function packageThemeKey(
        string $themeKey
    ): string {
        /*
         * Temporary built-in compatibility mapping.
         *
         * Marketplace theme package resolution can later
         * provide this relationship dynamically.
         */
        return match ($themeKey) {
            'business' =>
                'corporate-default',

            default =>
                $themeKey,
        };
    }


    protected function safeThemeKey(
        ?string $themeKey
    ): ?string {
        $themeKey =
            trim(
                (string) $themeKey
            );

        if (
            $themeKey === ''
            || !preg_match(
                '/^[A-Za-z0-9_-]+$/',
                $themeKey
            )
        ) {
            return null;
        }

        return mb_substr(
            $themeKey,
            0,
            150
        );
    }
}
