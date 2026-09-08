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


    /*
     * ESUBIZ_CORE_INSTALLED_THEME_REGISTRY_WRITE_V3
     *
     * Canonical installed-theme persistence.
     *
     * Theme installers must write through this registry instead of
     * manipulating core_installed_themes independently.
     */

    public function registerInstalled(
        string $slug,
        string $version,
        array $data = []
    ): array {
        $slug = strtolower(trim($slug));
        $version = $this->normalizeVersion($version);

        if ($slug === '' || $version === '') {
            throw new \InvalidArgumentException(
                'Theme slug and version are required.'
            );
        }

        if (!Schema::hasTable('core_installed_themes')) {
            throw new \RuntimeException(
                'The Core installed-theme registry table is not available.'
            );
        }

        $existing = DB::table('core_installed_themes')
            ->where('theme_slug', $slug)
            ->where('theme_version', $version)
            ->first();

        $metadata = $data['metadata'] ?? [];

        if (!is_array($metadata)) {
            $metadata = [];
        }

        $values = [
            'name' =>
                trim((string) ($data['name'] ?? '')) !== ''
                    ? trim((string) $data['name'])
                    : $slug,

            'preview_path' =>
                $data['preview_path'] ?? null,

            'install_path' =>
                $data['install_path'] ?? null,

            'marketplace_theme_package_id' =>
                $data['marketplace_theme_package_id'] ?? null,

            'marketplace_theme_uuid' =>
                $data['marketplace_theme_uuid'] ?? null,

            'checksum_sha256' =>
                $data['checksum_sha256'] ?? null,

            'metadata' =>
                json_encode(
                    $metadata,
                    JSON_UNESCAPED_SLASHES
                    | JSON_UNESCAPED_UNICODE
                ),

            'installed_at' =>
                $existing->installed_at ?? now(),

            'updated_at' => now(),
        ];

        if ($existing) {
            DB::table('core_installed_themes')
                ->where('id', $existing->id)
                ->update($values);
        } else {
            $values['theme_slug'] = $slug;
            $values['theme_version'] = $version;
            $values['is_active'] = false;
            $values['activated_at'] = null;
            $values['created_at'] = now();

            DB::table('core_installed_themes')
                ->insert($values);
        }

        $theme = $this->find($slug, $version);

        if (!$theme) {
            throw new \RuntimeException(
                "Installed Theme [{$slug}@{$version}] could not be registered."
            );
        }

        return $theme;
    }


    public function activate(
        string $slug,
        string $version
    ): array {
        $slug = strtolower(trim($slug));
        $version = $this->normalizeVersion($version);

        $theme = $this->find($slug, $version);

        if (!$theme) {
            throw new \RuntimeException(
                "Theme [{$slug}@{$version}] is not installed."
            );
        }

        DB::transaction(function () use ($theme) {
            DB::table('core_installed_themes')
                ->where('is_active', true)
                ->update([
                    'is_active' => false,
                    'updated_at' => now(),
                ]);

            DB::table('core_installed_themes')
                ->where('id', (int) $theme['id'])
                ->update([
                    'is_active' => true,
                    'activated_at' => now(),
                    'updated_at' => now(),
                ]);
        });

        $active = $this->find($slug, $version);

        if (!$active || empty($active['active'])) {
            throw new \RuntimeException(
                "Theme [{$slug}@{$version}] could not be enabled."
            );
        }

        return $active;
    }


    public function remove(
        string $slug,
        string $version
    ): bool {
        $slug = strtolower(trim($slug));
        $version = $this->normalizeVersion($version);

        if ($slug === '' || $version === '') {
            return false;
        }

        $theme = $this->find($slug, $version);

        if (!$theme) {
            return false;
        }

        if (!empty($theme['active'])) {
            throw new \RuntimeException(
                'The active Theme cannot be removed until another Theme is enabled.'
            );
        }

        return DB::table('core_installed_themes')
            ->where('id', (int) $theme['id'])
            ->delete() > 0;
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
