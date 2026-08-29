<?php

namespace App\Services\SiteAi\Proposals\Destinations;

use App\Models\Ai\AiProposal;
use App\Models\Website;
use App\Services\SiteAi\Proposals\Contracts\AiProposalDestination;
use App\Services\Website\WebsiteTenantDatabaseService;
use RuntimeException;
use Throwable;

class SaasTenantProposalDestination implements AiProposalDestination
{
    public function __construct(
        protected WebsiteTenantDatabaseService $tenantDatabase
    ) {
    }


    public function supports(
        ?Website $website,
        AiProposal $proposal
    ): bool {
        if (!$website) {
            return false;
        }

        return (
            $website->deployment_type
            ?? Website::DEPLOYMENT_SAAS
        ) === Website::DEPLOYMENT_SAAS;
    }


    public function validate(
        ?Website $website,
        AiProposal $proposal
    ): void {
        if (!$website) {
            throw new RuntimeException(
                'SaaS AI proposal requires a website.'
            );
        }

        if (
            (int) $proposal->website_id
            !== (int) $website->id
        ) {
            throw new RuntimeException(
                'AI proposal website destination mismatch.'
            );
        }

        $destination =
            is_array($proposal->destination)
                ? $proposal->destination
                : [];

        if (empty($destination)) {
            throw new RuntimeException(
                'AI proposal has no destination.'
            );
        }

        /*
         * Confirm the tenant database is reachable.
         *
         * No content is changed here.
         */
        try {
            $this->tenantDatabase
                ->connect($website);

            $db =
                $this->tenantDatabase
                    ->connection();

            if (
                !$db->getSchemaBuilder()
                    ->hasTable('site_settings')
            ) {
                throw new RuntimeException(
                    'Tenant site_settings table is unavailable.'
                );
            }
        } finally {
            $this->tenantDatabase
                ->disconnect();
        }
    }


    public function saveValues(
        ?Website $website,
        AiProposal $proposal,
        array $values
    ): array {
        if (!$website) {
            throw new RuntimeException(
                'SaaS AI proposal requires a website.'
            );
        }

        if (empty($values)) {
            return [
                'saved' => [],
            ];
        }

        $destination =
            is_array($proposal->destination)
                ? $proposal->destination
                : [];

        /*
         * The destination must explicitly provide the normal
         * setting namespace used by the existing site editor.
         *
         * Example:
         *
         * setting_prefix = theme.corporate.
         *
         * about_title becomes:
         * theme.corporate.about_title
         *
         * about_text becomes:
         * theme.corporate.about_text
         *
         * This means the ordinary Theme Customizer reads the
         * AI-approved values immediately.
         */
        $settingPrefix =
            trim(
                (string) (
                    $destination[
                        'setting_prefix'
                    ] ?? ''
                )
            );

        if ($settingPrefix === '') {
            throw new RuntimeException(
                'AI proposal destination does not declare a setting prefix.'
            );
        }

        if (
            !preg_match(
                '/^[A-Za-z0-9._:-]+$/',
                $settingPrefix
            )
        ) {
            throw new RuntimeException(
                'AI proposal setting prefix is invalid.'
            );
        }

        $allowedTargets =
            $this->manifestTargets(
                is_array(
                    $proposal->manifest_snapshot
                )
                    ? $proposal->manifest_snapshot
                    : []
            );

        $saved = [];

        try {
            $this->tenantDatabase
                ->connect($website);

            $db =
                $this->tenantDatabase
                    ->connection();

            $db->transaction(
                function () use (
                    $db,
                    $values,
                    $allowedTargets,
                    $settingPrefix,
                    &$saved
                ): void {
                    foreach (
                        $values
                        as $targetKey => $value
                    ) {
                        $targetKey =
                            trim(
                                (string) $targetKey
                            );

                        if ($targetKey === '') {
                            throw new RuntimeException(
                                'AI proposal contains an empty target.'
                            );
                        }

                        /*
                         * Revalidate at apply-time against the
                         * manifest snapshot.
                         */
                        if (
                            !empty($allowedTargets)
                            && !in_array(
                                $targetKey,
                                $allowedTargets,
                                true
                            )
                        ) {
                            throw new RuntimeException(
                                'AI proposal target is outside its original scope: '
                                . $targetKey
                            );
                        }

                        if (
                            !preg_match(
                                '/^[A-Za-z0-9._:-]+$/',
                                $targetKey
                            )
                        ) {
                            throw new RuntimeException(
                                'AI proposal target is invalid: '
                                . $targetKey
                            );
                        }

                        $settingKey =
                            $settingPrefix
                            . $targetKey;

                        /*
                         * site_settings values are stored in the
                         * same normal format used by the existing
                         * theme editor.
                         */
                        $storedValue =
                            $this->encodeSettingValue(
                                $value
                            );

                        $db->table(
                            'site_settings'
                        )->updateOrInsert(
                            [
                                'key' =>
                                    $settingKey,
                            ],
                            [
                                'value' =>
                                    $storedValue,

                                'updated_at' =>
                                    now(),

                                'created_at' =>
                                    now(),
                            ]
                        );

                        $saved[] = [
                            'target_key' =>
                                $targetKey,

                            'setting_key' =>
                                $settingKey,
                        ];
                    }
                }
            );
        } catch (Throwable $e) {
            throw $e;
        } finally {
            $this->tenantDatabase
                ->disconnect();
        }

        return [
            'storage' =>
                'tenant_database',

            'table' =>
                'site_settings',

            'saved' =>
                $saved,
        ];
    }


    public function saveAsset(
        ?Website $website,
        AiProposal $proposal,
        array $asset,
        array $target = []
    ): array {
        /*
         * ESUBIZ_SAAS_AI_ASSET_PERSISTENCE_V1
         *
         * Approved AI images enter the SAME canonical website
         * media bucket used by normal tenant-owned media:
         *
         * tenant-websites/{website_id}/media/theme/ai/...
         *
         * Proposal generation never writes here.
         * This method runs only during approved application.
         */
        if (!$website) {
            throw new RuntimeException(
                'SaaS AI asset requires a website.'
            );
        }


        $mime =
            strtolower(
                trim(
                    (string) (
                        $asset['mime_type']
                        ?? 'image/jpeg'
                    )
                )
            );


        $allowedMimeTypes = [
            'image/jpeg' =>
                'jpg',

            'image/png' =>
                'png',

            'image/webp' =>
                'webp',
        ];


        if (
            !isset(
                $allowedMimeTypes[$mime]
            )
        ) {
            throw new RuntimeException(
                'Unsupported AI image format.'
            );
        }


        $encoded =
            trim(
                (string) (
                    $asset['data']
                    ?? ''
                )
            );


        if (
            $encoded === ''
            && !empty(
                $asset['data_url']
            )
        ) {
            $dataUrl =
                (string) $asset['data_url'];

            $comma =
                strpos(
                    $dataUrl,
                    ','
                );

            if ($comma !== false) {
                $encoded =
                    substr(
                        $dataUrl,
                        $comma + 1
                    );
            }
        }


        if ($encoded === '') {
            throw new RuntimeException(
                'Approved AI image contains no image data.'
            );
        }


        $binary =
            base64_decode(
                $encoded,
                true
            );


        if ($binary === false) {
            throw new RuntimeException(
                'Approved AI image data is invalid.'
            );
        }


        /*
         * Protect tenant storage from unexpectedly large
         * generated payloads.
         */
        if (
            strlen($binary)
            > 20 * 1024 * 1024
        ) {
            throw new RuntimeException(
                'Approved AI image exceeds the allowed size.'
            );
        }


        $targetKey =
            trim(
                (string) (
                    $target['target_key']
                    ?? 'generated-image'
                )
            );


        $folder =
            preg_replace(
                '/[^A-Za-z0-9_-]+/',
                '-',
                $targetKey
            );


        $folder =
            trim(
                (string) $folder,
                '-'
            );


        if ($folder === '') {
            $folder =
                'generated-image';
        }


        $extension =
            $allowedMimeTypes[
                $mime
            ];


        $filename =
            now()->format(
                'YmdHis'
            )
            . '-'
            . \Illuminate\Support\Str::lower(
                \Illuminate\Support\Str::random(
                    16
                )
            )
            . '.'
            . $extension;


        $directory =
            'tenant-websites/'
            . $website->id
            . '/media/theme/ai/'
            . $folder;


        /*
         * ESUBIZ_SAAS_AI_CANONICAL_MEDIA_REFERENCE_V2
         *
         * Store the canonical tenant media path in theme settings.
         *
         * This matches TenantMediaController and normal theme uploads:
         *
         * tenant-websites/{website}/media/theme/ai/...
         */
        $relativePath =
            'tenant-websites/'
            . $website->id
            . '/media/theme/ai/'
            . $folder
            . '/'
            . $filename;

        /*
         * ESUBIZ_SAAS_AI_PHYSICAL_MEDIA_PATH_FIX_V2
         *
         * The local disk APIs use a disk-relative storage path.
         * Keep it aligned with the canonical tenant media path.
         */
        $path = $relativePath;


        /*
         * ESUBIZ_SAAS_AI_BINARY_MEDIA_STORAGE_FIX_V3
         *
         * Track successful persistence for both optimized
         * and original-image fallback storage paths.
         */
        $stored = false;

        $optimizedPath = app(
            \App\Services\Media\CentralMediaService::class
        )->storeBinaryImageToDisk(
            $binary,
            'local',
            dirname($path),
            [
                'maximum_edge' => 1920,
                'image_quality' => 82,
            ]
        );

        if ($optimizedPath) {
            $path = $optimizedPath;

            $stored =
                \Illuminate\Support\Facades\Storage::disk('local')
                    ->exists($path);
        } else {
            $stored =
                \Illuminate\Support\Facades\Storage::disk('local')->put(
                    $path,
                    $binary
                );
        }

        if (!$stored) {
            throw new RuntimeException(
                'Approved AI image could not be stored.'
            );
        }

        /*
         * ESUBIZ_SAAS_AI_STORED_MEDIA_REFERENCE_V3
         *
         * CentralMediaService may optimize/convert the generated
         * image and therefore return a different filename or
         * extension. The theme must receive the actual stored path,
         * otherwise its image reference points at a file that does
         * not exist.
         */
        $storedRelativePath =
            ltrim(
                str_replace(
                    '\\',
                    '/',
                    (string) $path
                ),
                '/'
            );


        return [
            'storage' =>
                'local',

            'disk' =>
                'local',

            /*
             * Keep the actual stored location available for
             * auditing/debugging.
             */
            'storage_path' =>
                $storedRelativePath,

            /*
             * ThemeHomepageProposalApplier saves the actual
             * persisted media reference into site_settings.
             */
            'path' =>
                $storedRelativePath,

            'value' =>
                $storedRelativePath,

            'reference' =>
                $storedRelativePath,

            'mime_type' =>
                $mime,

            'target_key' =>
                $targetKey,

            'bytes' =>
                strlen($binary),
        ];
    }


    public function installPackage(
        ?Website $website,
        AiProposal $proposal,
        array $package,
        array $target = []
    ): array {
        /*
         * Generated themes/packages will be connected to the
         * normal website Installed Themes/package installer.
         */
        throw new RuntimeException(
            'SaaS AI package installation is not registered yet.'
        );
    }


    protected function manifestTargets(
        array $manifest
    ): array {
        $targets = [];

        foreach (
            (array) (
                $manifest['targets']
                ?? []
            )
            as $target
        ) {
            $target =
                trim(
                    (string) $target
                );

            if ($target !== '') {
                $targets[] =
                    $target;
            }
        }

        foreach (
            (array) (
                $manifest['functions']
                ?? []
            )
            as $function
        ) {
            if (!is_array($function)) {
                continue;
            }

            foreach (
                (array) (
                    $function['targets']
                    ?? []
                )
                as $target
            ) {
                $target =
                    trim(
                        (string) $target
                    );

                if ($target !== '') {
                    $targets[] =
                        $target;
                }
            }
        }

        return array_values(
            array_unique(
                $targets
            )
        );
    }


    protected function encodeSettingValue(
        mixed $value
    ): string {
        if (is_array($value)) {
            return json_encode(
                $value,
                JSON_UNESCAPED_SLASHES
                | JSON_UNESCAPED_UNICODE
            ) ?: '[]';
        }

        if (is_bool($value)) {
            return $value
                ? '1'
                : '0';
        }

        if ($value === null) {
            return '';
        }

        return (string) $value;
    }
}
