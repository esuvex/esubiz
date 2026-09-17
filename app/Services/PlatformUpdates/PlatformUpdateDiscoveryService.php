<?php

namespace App\Services\PlatformUpdates;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class PlatformUpdateDiscoveryService
{
    /**
     * Discover update sources known to Central Esubiz.
     *
     * Discovery ONLY registers available updates.
     * It never executes migrations or modifies tenants.
     */
    public function sync(): array
    {
        if (!Schema::hasTable('platform_updates')) {
            return [
                'discovered' => 0,
                'existing' => 0,
            ];
        }

        $stats = [
            'discovered' => 0,
            'existing' => 0,
        ];

        $this->discoverCoreMigrations($stats);

        return $stats;
    }

    /**
     * Automatically discover canonical tenant Core migrations.
     */
    protected function discoverCoreMigrations(array &$stats): void
    {
        $directory = database_path('migrations/tenant/core');

        if (!is_dir($directory)) {
            return;
        }

        $files = glob($directory . '/*.php') ?: [];

        sort($files);

        foreach ($files as $file) {
            $relativePath = str_replace(
                base_path() . DIRECTORY_SEPARATOR,
                '',
                $file
            );

            $relativePath = str_replace('\\', '/', $relativePath);

            $basename = pathinfo($file, PATHINFO_FILENAME);

            $existing = DB::table('platform_updates')
                ->where('product_type', 'core')
                ->whereNull('product_id')
                ->where('migration_path', $relativePath)
                ->first();

            $postMigrationAction =
                $this->postMigrationActionFor(
                    $relativePath
                );

            if ($existing) {
                $this->syncDiscoveredManifest(
                    $existing,
                    $relativePath,
                    $postMigrationAction
                );

                $stats['existing']++;
                continue;
            }

            /*
             * Migration filenames become stable discovery versions.
             *
             * Example:
             * 2026_09_08_181000_add_country_timezone_to_core_users_table
             *
             * becomes:
             * 2026.09.08.181000
             */
            preg_match(
                '/^(\d{4})_(\d{2})_(\d{2})_(\d{6})_(.+)$/',
                $basename,
                $matches
            );

            if (!empty($matches)) {
                $version = sprintf(
                    '%s.%s.%s.%s',
                    $matches[1],
                    $matches[2],
                    $matches[3],
                    $matches[4]
                );

                $labelSource = $matches[5];
            } else {
                $version = $basename;
                $labelSource = $basename;
            }

            $name = Str::of($labelSource)
                ->replace('_', ' ')
                ->title()
                ->toString();

            $manifest = [
                'source' => 'automatic_discovery',
                'source_type' => 'core_migration',
                'file' => $relativePath,
            ];

            if ($postMigrationAction !== null) {
                $manifest['post_migration_action'] =
                    $postMigrationAction;
            }

            DB::table('platform_updates')->insert([
                'product_type' => 'core',
                'product_id' => null,
                'name' => $name,
                'version' => $version,
                'description' =>
                    'Automatically discovered canonical Core tenant migration.',
                'update_type' => 'migration',
                'status' => 'draft',
                'migration_path' => $relativePath,
                'package_path' => null,
                'manifest' => json_encode($manifest),
                'prerequisites' => null,
                'requires_backup' => true,
                'is_destructive' => false,
                'published_at' => null,
                'created_by' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $stats['discovered']++;
        }
    }

    /**
     * ESUBIZ_CORE_MIGRATION_POST_ACTION_DISCOVERY_V1
     *
     * External post-migration actions are declared here by exact
     * canonical migration path. Discovery metadata never supplies
     * arbitrary executable PHP.
     */
    protected function postMigrationActionFor(
        string $relativePath
    ): ?string {
        return match ($relativePath) {
            'database/migrations/tenant/core/'
            . '2026_09_16_123000_backfill_included_saas_mailbox.php'
                => 'provision_included_saas_mailbox',

            default => null,
        };
    }

    /**
     * Keep automatically discovered manifests aligned with the
     * source-controlled action declaration without changing update
     * publication state or executing anything.
     */
    protected function syncDiscoveredManifest(
        object $existing,
        string $relativePath,
        ?string $postMigrationAction
    ): void {
        $manifest = [];

        if (is_string($existing->manifest ?? null)) {
            $decoded = json_decode(
                $existing->manifest,
                true
            );

            if (is_array($decoded)) {
                $manifest = $decoded;
            }
        } elseif (is_array($existing->manifest ?? null)) {
            $manifest = $existing->manifest;
        } elseif (is_object($existing->manifest ?? null)) {
            $manifest = (array) $existing->manifest;
        }

        /*
         * Only reconcile records that belong to automatic Core
         * migration discovery. Never rewrite manually managed
         * platform-update manifests.
         */
        if (
            ($manifest['source'] ?? null) !== 'automatic_discovery'
            || ($manifest['source_type'] ?? null) !== 'core_migration'
        ) {
            return;
        }

        $manifest['file'] = $relativePath;

        if ($postMigrationAction !== null) {
            $manifest['post_migration_action'] =
                $postMigrationAction;
        } else {
            unset($manifest['post_migration_action']);
        }

        $encoded = json_encode($manifest);

        if ($encoded === $existing->manifest) {
            return;
        }

        DB::table('platform_updates')
            ->where('id', $existing->id)
            ->update([
                'manifest' => $encoded,
                'updated_at' => now(),
            ]);
    }
}
