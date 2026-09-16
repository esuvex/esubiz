<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

return new class extends Migration
{
    /*
    |--------------------------------------------------------------------------
    | ESUBIZ_CORE_CANONICAL_BRANDING_NORMALIZATION_V1
    |--------------------------------------------------------------------------
    |
    | Existing-tenant normalization only.
    |
    | Canonical Core branding keys:
    |
    |   site_logo_path
    |   site_logo_white_path
    |   site_favicon_path
    |
    | Legacy theme branding remains an explicit override only when its value
    | differs from the canonical value.
    |
    | This migration never deletes media files and never generates branding
    | files. It only normalizes site_settings records.
    |
    */

    public function up(): void
    {
        if (!Schema::hasTable('site_settings')) {
            return;
        }

        DB::transaction(function (): void {
            $settings = DB::table('site_settings')
                ->whereIn('key', [
                    'site_logo_path',
                    'site_logo_white_path',
                    'site_favicon_path',
                    'theme.corporate.logo_path',
                    'theme.corporate.footer_logo_path',
                    'theme.corporate.favicon_path',
                ])
                ->pluck('value', 'key')
                ->map(
                    static fn ($value) => trim((string) $value)
                )
                ->all();

            $canonicalLogo = $settings['site_logo_path'] ?? '';
            $canonicalWhite = $settings['site_logo_white_path'] ?? '';
            $canonicalFavicon = $settings['site_favicon_path'] ?? '';

            $legacyLogo = $settings['theme.corporate.logo_path'] ?? '';
            $legacyFooter = $settings['theme.corporate.footer_logo_path'] ?? '';
            $legacyFavicon = $settings['theme.corporate.favicon_path'] ?? '';

            /*
             * Backfill a missing canonical main logo from the historical
             * corporate-theme logo. The old value is retained until the
             * duplicate check below proves that it is merely a mirror.
             */
            if ($canonicalLogo === '' && $legacyLogo !== '') {
                $this->putSetting('site_logo_path', $legacyLogo);
                $canonicalLogo = $legacyLogo;
            }

            /*
             * Backfill a missing canonical favicon from the historical
             * corporate-theme favicon.
             */
            if ($canonicalFavicon === '' && $legacyFavicon !== '') {
                $this->putSetting('site_favicon_path', $legacyFavicon);
                $canonicalFavicon = $legacyFavicon;
            }

            /*
             * A theme value equal to the canonical value is not a genuine
             * override. Clear only that proven mirror so future canonical
             * changes automatically flow through.
             */
            /*
             * ESUBIZ_CORE_CANONICAL_BRANDING_NORMALIZATION_V2
             *
             * Clear a theme main-logo value when:
             *
             * 1. it is an exact mirror of the canonical main logo; or
             * 2. a canonical main logo already exists and the theme value
             *    belongs to the historical Site Settings logo namespace.
             *
             * Genuine theme-specific tenant media overrides are preserved.
             */
            if (
                $canonicalLogo !== '' &&
                $legacyLogo !== '' &&
                (
                    $legacyLogo === $canonicalLogo ||
                    str_starts_with(
                        ltrim($legacyLogo, '/'),
                        'core/site-branding/'
                    )
                )
            ) {
                $this->putSetting('theme.corporate.logo_path', '');
            }

            /*
             * ESUBIZ_CORE_CANONICAL_BRANDING_NORMALIZATION_V3
             *
             * Historical Site Settings favicons may still reference the
             * obsolete public core/site-branding namespace.
             *
             * When a canonical tenant branding directory already exists,
             * copy that historical favicon into the canonical private tenant
             * media area and update site_favicon_path accordingly.
             *
             * The existing canonical logo/white-logo path supplies the
             * tenant branding directory, so no website ID is hard-coded.
             */
            $historicalFavicon =
                $canonicalFavicon !== '' &&
                str_starts_with(
                    ltrim($canonicalFavicon, '/'),
                    'core/site-branding/'
                );

            if ($historicalFavicon) {
                $brandingAuthority =
                    $canonicalLogo !== ''
                        ? $canonicalLogo
                        : $canonicalWhite;

                $brandingDirectory =
                    $brandingAuthority !== ''
                        ? trim(
                            str_replace(
                                '\\',
                                '/',
                                dirname($brandingAuthority)
                            ),
                            '/'
                        )
                        : '';

                $sourcePath =
                    ltrim($canonicalFavicon, '/');

                if (
                    $brandingDirectory !== '' &&
                    str_starts_with(
                        $brandingDirectory,
                        'tenant-websites/'
                    ) &&
                    Storage::disk('public')->exists($sourcePath)
                ) {
                    $extension =
                        strtolower(
                            pathinfo(
                                $sourcePath,
                                PATHINFO_EXTENSION
                            )
                        );

                    $extension =
                        $extension !== ''
                            ? $extension
                            : 'png';

                    $fingerprint =
                        substr(
                            sha1(
                                $sourcePath
                                . '|'
                                . Storage::disk('public')
                                    ->size($sourcePath)
                            ),
                            0,
                            12
                        );

                    $newFaviconPath =
                        $brandingDirectory
                        . '/favicon-legacy-'
                        . $fingerprint
                        . '.'
                        . $extension;

                    if (
                        !Storage::disk('local')
                            ->exists($newFaviconPath)
                    ) {
                        $contents =
                            Storage::disk('public')
                                ->get($sourcePath);

                        Storage::disk('local')
                            ->put(
                                $newFaviconPath,
                                $contents
                            );
                    }

                    $canonicalFavicon =
                        $newFaviconPath;

                    $this->putSetting(
                        'site_favicon_path',
                        $canonicalFavicon
                    );
                }
            }

            /*
             * Theme favicon remains an explicit override only.
             *
             * Clear an exact canonical mirror or an obsolete historical
             * Site Settings mirror. Genuine tenant/theme media overrides
             * remain untouched.
             */
            if (
                $canonicalFavicon !== '' &&
                $legacyFavicon !== '' &&
                (
                    $legacyFavicon === $canonicalFavicon ||
                    str_starts_with(
                        ltrim($legacyFavicon, '/'),
                        'core/site-branding/'
                    )
                )
            ) {
                $this->putSetting(
                    'theme.corporate.favicon_path',
                    ''
                );
            }

            /*
             * The generated canonical white logo is the inherited footer /
             * internal logo. A footer value is cleared only when it is an
             * exact mirror of that canonical white value.
             *
             * A differing footer value remains a genuine explicit override.
             */
            if (
                $canonicalWhite !== '' &&
                $legacyFooter !== '' &&
                $legacyFooter === $canonicalWhite
            ) {
                $this->putSetting('theme.corporate.footer_logo_path', '');
            }
        });
    }

    public function down(): void
    {
        /*
         * Intentionally irreversible.
         *
         * The migration only promotes existing branding references to their
         * canonical keys and removes values proven to be exact duplicate
         * mirrors. Recreating historical mirrors would restore the defect.
         *
         * No media files are deleted or modified by up().
         */
    }

    private function putSetting(string $key, string $value): void
    {
        $exists = DB::table('site_settings')
            ->where('key', $key)
            ->exists();

        if ($exists) {
            $record = [
                'value' => $value,
            ];

            if (Schema::hasColumn('site_settings', 'updated_at')) {
                $record['updated_at'] = now();
            }

            DB::table('site_settings')
                ->where('key', $key)
                ->update($record);

            return;
        }

        $record = [
            'key' => $key,
            'value' => $value,
        ];

        if (Schema::hasColumn('site_settings', 'created_at')) {
            $record['created_at'] = now();
        }

        if (Schema::hasColumn('site_settings', 'updated_at')) {
            $record['updated_at'] = now();
        }

        DB::table('site_settings')->insert($record);
    }
};
