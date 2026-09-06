<?php

namespace App\Services\Core\Themes;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class InstalledThemeRegistry
{
    /*
     * ESUBIZ_CORE_INSTALLED_THEME_REGISTRY_V2
     *
     * Universal Core installed-theme registry.
     *
     * Installed state belongs to each Core website database.
     * No theme names are hardcoded here.
     */

    public function all(
        string $activeTheme = ''
    ): array {
        if (
            !Schema::hasTable(
                'core_installed_themes'
            )
        ) {
            return [];
        }

        return DB::table(
            'core_installed_themes'
        )
            ->orderByDesc('is_active')
            ->orderBy('name')
            ->get()
            ->map(
                function ($theme) use ($activeTheme) {
                    $slug =
                        strtolower(
                            trim(
                                (string) $theme->theme_slug
                            )
                        );

                    $version =
                        $this->normalizeVersion(
                            $theme->theme_version
                        );

                    $active =
                        (bool) $theme->is_active;

                    if (
                        !$active
                        && $activeTheme !== ''
                    ) {
                        $active =
                            $slug
                            === strtolower(
                                trim(
                                    $activeTheme
                                )
                            );
                    }

                    $metadata =
                        $this->decodeMetadata(
                            $theme->metadata
                        );

                    /*
                     * ESUBIZ_CORE_INSTALLED_THEME_PRESENTATION_METADATA_V1
                     *
                     * Universal presentation metadata for installed Themes.
                     *
                     * Theme Hub, preview resolver and other Core surfaces
                     * should consume these normalized fields rather than
                     * hardcoding Theme-specific definitions.
                     */
                    $description =
                        (string) (
                            $metadata['description']
                            ?? ''
                        );

                    $aliases =
                        $metadata['aliases']
                        ?? $metadata['legacy_aliases']
                        ?? [];

                    if (!is_array($aliases)) {
                        $aliases = [];
                    }

                    $aliases =
                        collect($aliases)
                            ->filter(
                                fn ($alias) =>
                                    is_string($alias)
                                    && trim($alias) !== ''
                            )
                            ->map(
                                fn ($alias) =>
                                    strtolower(
                                        trim($alias)
                                    )
                            )
                            ->unique()
                            ->values()
                            ->all();

                    return [
                        'id' => (int) $theme->id,
                        'slug' => $slug,
                        'name' =>
                            $theme->name
                            ?: $slug,
                        'version' => $version,

                        'description' =>
                            $description,

                        'aliases' =>
                            $aliases,

                        'preview_path' =>
                            $theme->preview_path,

                        'install_path' =>
                            $theme->install_path,

                        'marketplace_theme_package_id' =>
                            $theme->marketplace_theme_package_id
                                ? (int) $theme->marketplace_theme_package_id
                                : null,

                        'marketplace_theme_uuid' =>
                            $theme->marketplace_theme_uuid,

                        'checksum_sha256' =>
                            $theme->checksum_sha256,

                        'active' => $active,

                        'installed_at' =>
                            $theme->installed_at,

                        'activated_at' =>
                            $theme->activated_at,

                        'metadata' =>
                            $metadata,
                    ];
                }
            )
            ->values()
            ->all();
    }


    public function collection(
        string $activeTheme = ''
    ): Collection {
        return collect(
            $this->all(
                $activeTheme
            )
        );
    }


    public function keys(): Collection
    {
        return $this->collection()
            ->map(
                fn (array $theme) =>
                    $this->key(
                        $theme['slug'] ?? null,
                        $theme['version'] ?? null
                    )
            )
            ->filter()
            ->unique()
            ->values();
    }


    public function isInstalled(
        ?string $slug,
        ?string $version
    ): bool {
        $key =
            $this->key(
                $slug,
                $version
            );

        if ($key === null) {
            return false;
        }

        return $this->keys()
            ->contains(
                $key
            );
    }


    public function find(
        ?string $slug,
        ?string $version
    ): ?array {
        $key =
            $this->key(
                $slug,
                $version
            );

        if ($key === null) {
            return null;
        }

        return $this->collection()
            ->first(
                fn (array $theme) =>
                    $this->key(
                        $theme['slug'] ?? null,
                        $theme['version'] ?? null
                    ) === $key
            );
    }


    protected function key(
        mixed $slug,
        mixed $version
    ): ?string {
        $slug =
            strtolower(
                trim(
                    (string) $slug
                )
            );

        $version =
            $this->normalizeVersion(
                $version
            );

        if (
            $slug === ''
            || $version === ''
        ) {
            return null;
        }

        return $slug
            . '@'
            . $version;
    }


    protected function normalizeVersion(
        mixed $version
    ): string {
        $version =
            strtolower(
                trim(
                    (string) $version
                )
            );

        return preg_replace(
            '/^v(?=\d)/',
            '',
            $version
        ) ?: '';
    }


    protected function decodeMetadata(
        mixed $metadata
    ): array {
        if (is_array($metadata)) {
            return $metadata;
        }

        if (
            !is_string($metadata)
            || trim($metadata) === ''
        ) {
            return [];
        }

        $decoded =
            json_decode(
                $metadata,
                true
            );

        return is_array($decoded)
            ? $decoded
            : [];
    }
}
