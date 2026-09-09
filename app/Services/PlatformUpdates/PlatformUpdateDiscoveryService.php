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

            $exists = DB::table('platform_updates')
                ->where('product_type', 'core')
                ->whereNull('product_id')
                ->where('migration_path', $relativePath)
                ->exists();

            if ($exists) {
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
                'manifest' => json_encode([
                    'source' => 'automatic_discovery',
                    'source_type' => 'core_migration',
                    'file' => $relativePath,
                ]),
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
}
