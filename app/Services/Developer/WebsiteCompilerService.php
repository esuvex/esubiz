<?php

namespace App\Services\Developer;

use App\Models\DeveloperBuild;
use App\Models\WebsiteType;
use App\Services\ProductStorageService;
use App\Services\Website\WebsiteTypeManifestService;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;

class WebsiteCompilerService
{
    /**
     * Create an isolated build workspace for a developer project.
     */
    public function createWorkspace(string $projectName): array
    {
        $slug = Str::slug($projectName);

        if ($slug === '') {
            throw new RuntimeException('A valid project name is required.');
        }

        $buildId = $slug . '-' . Str::lower(Str::random(8));

        $root = storage_path('app/developer-builds/' . $buildId);

        File::makeDirectory($root, 0755, true);

        foreach ([
            'src',
            'public',
            'resources',
            'resources/css',
            'resources/js',
            'resources/images',
            'modules',
            'dist',
        ] as $directory) {
            File::makeDirectory($root . '/' . $directory, 0755, true);
        }

        File::put(
            $root . '/project.json',
            json_encode([
                'build_id' => $buildId,
                'project_name' => $projectName,
                'version' => '1.0.0',
                'type' => 'developer-website',
                'entry' => 'src/index.html',
                'output' => 'dist',
            ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . PHP_EOL
        );

        File::put(
            $root . '/src/index.html',
            '<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>' . e($projectName) . '</title>
</head>
<body>
    <main>
        <h1>' . e($projectName) . '</h1>
        <p>Developer website build.</p>
    </main>
</body>
</html>
'
        );

        return [
            'build_id' => $buildId,
            'project_name' => $projectName,
            'path' => $root,
            'dist' => $root . '/dist',
        ];
    }

    /**
     * Compile a developer website workspace into a distributable package.
     */
    public function compile(string $buildId): array
    {
        try {
            return $this->performCompile($buildId);
        } catch (\Throwable $e) {
            $build = DeveloperBuild::where('build_id', $buildId)->first();

            if ($build) {
                $build->update([
                    'status' => 'failed',
                    'error_message' => $e->getMessage(),
                    'completed_at' => now(),
                ]);
            }

            throw $e;
        }
    }

    /**
     * Perform the actual compilation.
     */
    /*
     * ESUBIZ_DEVELOPER_REAL_PROGRESS_V1
     *
     * Persist the REAL compiler checkpoint in the build's existing
     * configuration JSON. The frontend must read this value; it must
     * never manufacture compilation progress.
     */
    protected function updateBuildProgress(
        DeveloperBuild $build,
        int $percent,
        string $stage
    ): void {
        $percent = max(0, min(100, $percent));

        $configuration =
            is_array($build->configuration)
                ? $build->configuration
                : [];

        $configuration['progress'] = [
            'percent' => $percent,
            'stage' => $stage,
            'updated_at' => now()->toIso8601String(),
        ];

        $build->update([
            'configuration' => $configuration,
        ]);

        $build->refresh();
    }

    protected function performCompile(string $buildId): array
    {
        $root = storage_path('app/developer-builds/' . $buildId);
        $source = $root . '/src';
        $dist = $root . '/dist';

        $build = DeveloperBuild::where('build_id', $buildId)->first();

        // ESUBIZ_DEVELOPER_BUILD_POST_PAYMENT_LICENSE_V2
        //
        // Developer compilation is intentionally licence-free.
        //
        // A Website Type is compiled into its private distributable package
        // before checkout so the buyer can proceed to payment only after a
        // successful build.
        //
        // The authoritative Marketplace entitlement and licence are created
        // only after successful payment. The compiled ZIP therefore does not
        // create, bind or reserve a licence merely because a Developer Build
        // exists.
        //
        // The Website Type is the commercial product identity. Its prepared
        // distribution contains Core plus its configured theme/modules and
        // any genuine optional products assembled into this build.

        if ($build) {
            $build->update([
                'status' => 'building',
                'started_at' => now(),
            ]);

            $this->updateBuildProgress(
                $build,
                5,
                'starting'
            );
        }

        // ESUBIZ_DEVELOPER_WORKSPACE_RECOVERY_V1
        //
        // Live Developer compilation may enter performCompile()
        // independently of the original synchronous preparation request.
        // Ensure the private build workspace exists before assembly.
        //
        // This is also safe for Recompile when an earlier temporary
        // workspace has already been removed.
        if (!File::isDirectory($root)) {
            File::makeDirectory(
                $root,
                0755,
                true
            );
        }

        foreach ([
            'source',
            'dist',
            'temp',
            'logs',
        ] as $directory) {
            $directoryPath =
                $root
                . DIRECTORY_SEPARATOR
                . $directory;

            if (!File::isDirectory($directoryPath)) {
                File::makeDirectory(
                    $directoryPath,
                    0755,
                    true
                );
            }
        }

        if (!File::isDirectory($root)) {
            throw new RuntimeException(
                'Unable to prepare Developer Build workspace.'
            );
        }

        // ESUBIZ_DEVELOPER_WEBSITE_TYPE_BASE_PACKAGE_V1
        //
        // A Website Type is already a prepared Core distribution:
        // Core + its configured default theme/modules/add-ons.
        // It is therefore the base of every Developer compilation.
        $websiteType = WebsiteType::query()
            ->where('slug', $build->website_type)
            ->where('is_active', true)
            ->where('show_in_developer_wizard', true)
            ->first();

        if (!$websiteType) {
            throw new RuntimeException(
                'Selected Website Type is unavailable for Developer compilation.'
            );
        }

        $websiteTypeManifest = app(
            WebsiteTypeManifestService::class
        )->get($websiteType);

        $websiteTypeRoot = app(
            ProductStorageService::class
        )->path(
            'packages',
            'website-types/'
            . $websiteType->package_key
            . '/'
            . $websiteType->package_version
        );

        if (!File::isDirectory($websiteTypeRoot)) {
            throw new RuntimeException(
                'Prepared Website Type package directory was not found.'
            );
        }

        $this->updateBuildProgress(
            $build,
            20,
            'website_type_resolved'
        );

        File::deleteDirectory($dist);
        File::makeDirectory($dist, 0755, true);

        /*
         * Copy the prepared Website Type distribution as the base.
         * manifest.json remains in the package so the installer can
         * identify the Website Type and its built-in components.
         */
        if (!File::copyDirectory($websiteTypeRoot, $dist)) {
            throw new RuntimeException(
                'Unable to assemble the prepared Website Type package.'
            );
        }

        $this->updateBuildProgress(
            $build,
            40,
            'website_type_assembled'
        );

        /*
         * ESUBIZ_DEVELOPER_OPTIONAL_PRODUCTS_ASSEMBLY_V1
         *
         * The prepared Website Type above remains the compilation base.
         *
         * Developer-selected Marketplace products are attached to that
         * prepared distribution as validated Esubiz product packages.
         *
         * We do NOT reconstruct the Website Type and we do NOT invent
         * package locations. package_path from the authoritative
         * Marketplace staging/package record is required.
         *
         * The final installer can therefore install these genuine
         * attached products under the ONE primary Developer Build
         * licence generated for this compilation.
         */
        $configuration =
            is_array($build->configuration)
                ? $build->configuration
                : [];

        $selections =
            is_array($configuration['selections'] ?? null)
                ? $configuration['selections']
                : [];

        $pricing =
            is_array($configuration['pricing'] ?? null)
                ? $configuration['pricing']
                : [];

        $attachedRoot =
            $dist . DIRECTORY_SEPARATOR . 'esubiz-products';

        File::ensureDirectoryExists(
            $attachedRoot,
            0755,
            true
        );

        $attachedProducts = [
            'bundle' => null,
            'theme' => null,
            'modules' => [],
        ];

        /*
         * Resolve one Marketplace package from the canonical staging
         * record belonging to the selected listing/catalog product.
         *
         * package_path itself remains authoritative. Absolute paths are
         * accepted only when they point to a real file; relative paths
         * are resolved beneath the validator's protected storage root.
         */
        $resolveMarketplacePackage = function (
            ?int $listingId,
            ?int $catalogProductId,
            string $protectedRoot,
            string $label
        ): array {
            $query = DB::table('marketplace_product_stagings')
                ->whereNotNull('package_path');

            // ESUBIZ_DEVELOPER_STAGING_PRODUCT_ID_FIX_V1
            //
            // marketplace_product_stagings stores:
            // - marketplace_listing_id
            // - product_id
            //
            // It does NOT have catalog_product_id.
            if ($listingId) {
                $query->where(
                    'marketplace_listing_id',
                    $listingId
                );
            } elseif ($catalogProductId) {
                $query->where(
                    'product_id',
                    $catalogProductId
                );
            } else {
                throw new RuntimeException(
                    $label . ' has no authoritative product reference.'
                );
            }

            $record = $query
                ->orderByDesc('id')
                ->first();

            if (
                !$record
                || empty($record->package_path)
            ) {
                throw new RuntimeException(
                    $label
                    . ' does not have an available Marketplace package.'
                );
            }

            $recordedPath =
                trim((string) $record->package_path);

            $absolutePath =
                str_starts_with($recordedPath, '/')
                    ? $recordedPath
                    : rtrim(
                        $protectedRoot,
                        DIRECTORY_SEPARATOR
                    )
                    . DIRECTORY_SEPARATOR
                    . ltrim(
                        $recordedPath,
                        DIRECTORY_SEPARATOR
                    );

            if (!is_file($absolutePath)) {
                throw new RuntimeException(
                    $label
                    . ' package file was not found.'
                );
            }

            return [
                'record' => $record,
                'path' => $absolutePath,
            ];
        };

        /*
         * Optional Add-on Bundle.
         *
         * ESUBIZ_DEVELOPER_CANONICAL_BUNDLE_PACKAGE_V1
         *
         * Bundles are canonical Esubiz bundle packages and do not depend
         * on marketplace_product_stagings.
         */
        $bundleSelection =
            is_array($pricing['addon_bundle'] ?? null)
                ? $pricing['addon_bundle']
                : null;

        if (!empty($selections['addon_bundle'])) {
            if (
                !$bundleSelection
                || empty($bundleSelection['id'])
            ) {
                throw new RuntimeException(
                    'Selected add-on bundle pricing record is unavailable.'
                );
            }

            $bundleService = app(
                \App\Services\Marketplace\BundlePackageService::class
            );

            $bundleRoot =
                rtrim(
                    $bundleService->protectedRoot(),
                    DIRECTORY_SEPARATOR
                );

            if (!File::isDirectory($bundleRoot)) {
                throw new RuntimeException(
                    'Canonical add-on bundle storage is unavailable.'
                );
            }

            /*
             * Find the canonical package by validating the packages
             * already stored in the dedicated protected bundle root.
             *
             * No Marketplace staging record or invented package path
             * is used here.
             */
            $matchingBundlePackages = [];

            foreach (
                File::files($bundleRoot)
                as $bundleFile
            ) {
                if (
                    strtolower(
                        $bundleFile->getExtension()
                    ) !== 'zip'
                ) {
                    continue;
                }

                try {
                    $candidateManifest =
                        $bundleService->validatePackage(
                            $bundleFile->getPathname()
                        );
                } catch (\Throwable $e) {
                    continue;
                }

                $candidateId =
                    $candidateManifest['id']
                    ?? $candidateManifest['bundle_id']
                    ?? null;

                $candidateName =
                    $candidateManifest['name']
                    ?? $candidateManifest['bundle_name']
                    ?? null;

                $idMatches =
                    $candidateId !== null
                    && (string) $candidateId
                        === (string) $bundleSelection['id'];

                $nameMatches =
                    $candidateName !== null
                    && strcasecmp(
                        trim((string) $candidateName),
                        trim(
                            (string) (
                                $bundleSelection['name']
                                ?? ''
                            )
                        )
                    ) === 0;

                if ($idMatches || $nameMatches) {
                    $matchingBundlePackages[] = [
                        'path' =>
                            $bundleFile->getPathname(),
                        'manifest' =>
                            $candidateManifest,
                    ];
                }
            }

            if (count($matchingBundlePackages) !== 1) {
                throw new RuntimeException(
                    count($matchingBundlePackages) === 0
                        ? 'Canonical package for the selected add-on bundle was not found.'
                        : 'Multiple canonical packages matched the selected add-on bundle.'
                );
            }

            $bundlePackage =
                $matchingBundlePackages[0];

            $bundleDirectory =
                $attachedRoot
                . DIRECTORY_SEPARATOR
                . 'bundle';

            File::ensureDirectoryExists(
                $bundleDirectory,
                0755,
                true
            );

            $bundleFilename =
                basename($bundlePackage['path']);

            if (
                !File::copy(
                    $bundlePackage['path'],
                    $bundleDirectory
                    . DIRECTORY_SEPARATOR
                    . $bundleFilename
                )
            ) {
                throw new RuntimeException(
                    'Unable to attach the selected add-on bundle.'
                );
            }

            $attachedProducts['bundle'] = [
                'id' =>
                    (int) $bundleSelection['id'],

                'name' =>
                    $bundleSelection['name']
                    ?? 'Add-on Bundle',

                'package' =>
                    'esubiz-products/bundle/'
                    . $bundleFilename,

                'manifest' =>
                    $bundlePackage['manifest'],
            ];
        }

        /*
         * Optional Theme.
         */
        $themeSelection =
            is_array($pricing['theme'] ?? null)
                ? $pricing['theme']
                : null;

        if (!empty($selections['theme'])) {
            if (
                !$themeSelection
                || empty(
                    $themeSelection[
                        'catalog_product_id'
                    ]
                )
                || empty($themeSelection['listing_id'])
            ) {
                throw new RuntimeException(
                    'Selected theme pricing record is unavailable.'
                );
            }

            $themeService = app(
                \App\Services\Marketplace\ThemePackageService::class
            );

            $themePackage =
                $resolveMarketplacePackage(
                    (int) $themeSelection['listing_id'],
                    (int) $themeSelection[
                        'catalog_product_id'
                    ],
                    $themeService->protectedRoot(),
                    'Selected theme'
                );

            $themeManifest =
                $themeService->validatePackage(
                    $themePackage['path']
                );

            $themeDirectory =
                $attachedRoot
                . DIRECTORY_SEPARATOR
                . 'theme';

            File::ensureDirectoryExists(
                $themeDirectory,
                0755,
                true
            );

            $themeFilename =
                basename($themePackage['path']);

            if (
                !File::copy(
                    $themePackage['path'],
                    $themeDirectory
                    . DIRECTORY_SEPARATOR
                    . $themeFilename
                )
            ) {
                throw new RuntimeException(
                    'Unable to attach the selected theme.'
                );
            }

            $attachedProducts['theme'] = [
                'catalog_product_id' =>
                    (int) $themeSelection[
                        'catalog_product_id'
                    ],
                'listing_id' =>
                    (int) $themeSelection['listing_id'],
                'name' =>
                    $themeSelection['name']
                    ?? 'Theme',
                'package' =>
                    'esubiz-products/theme/'
                    . $themeFilename,
                'manifest' => $themeManifest,
            ];
        }

        /*
         * Optional Modules.
         *
         * Modules use the same genuine Marketplace package pipeline as
         * add-ons. Each selected module already has its authoritative
         * listing/catalog identity in the pricing manifest.
         */
        $moduleSelections =
            is_array($pricing['modules'] ?? null)
                ? $pricing['modules']
                : [];

        if (!empty($selections['modules'])) {
            $addonService = app(
                \App\Services\Marketplace\AddonPackageService::class
            );

            $moduleDirectory =
                $attachedRoot
                . DIRECTORY_SEPARATOR
                . 'modules';

            File::ensureDirectoryExists(
                $moduleDirectory,
                0755,
                true
            );

            foreach ($moduleSelections as $moduleSelection) {
                if (
                    !is_array($moduleSelection)
                    || empty(
                        $moduleSelection[
                            'catalog_product_id'
                        ]
                    )
                    || empty($moduleSelection['listing_id'])
                ) {
                    throw new RuntimeException(
                        'Selected module pricing record is unavailable.'
                    );
                }

                $modulePackage =
                    $resolveMarketplacePackage(
                        (int) $moduleSelection[
                            'listing_id'
                        ],
                        (int) $moduleSelection[
                            'catalog_product_id'
                        ],
                        $addonService->protectedRoot(),
                        'Selected module'
                    );

                $moduleManifest =
                    $addonService->validatePackage(
                        $modulePackage['path']
                    );

                $moduleFilename =
                    basename($modulePackage['path']);

                if (
                    !File::copy(
                        $modulePackage['path'],
                        $moduleDirectory
                        . DIRECTORY_SEPARATOR
                        . $moduleFilename
                    )
                ) {
                    throw new RuntimeException(
                        'Unable to attach selected module.'
                    );
                }

                $attachedProducts['modules'][] = [
                    'catalog_product_id' =>
                        (int) $moduleSelection[
                            'catalog_product_id'
                        ],
                    'listing_id' =>
                        (int) $moduleSelection[
                            'listing_id'
                        ],
                    'name' =>
                        $moduleSelection['name']
                        ?? 'Module',
                    'package' =>
                        'esubiz-products/modules/'
                        . $moduleFilename,
                    'manifest' => $moduleManifest,
                ];
            }

            if (
                count($attachedProducts['modules'])
                !== count($selections['modules'])
            ) {
                throw new RuntimeException(
                    'Not all selected modules were assembled.'
                );
            }
        }

        /*
         * Store an installer-readable attachment manifest inside the
         * private compiled distribution.
         */
        $attachmentManifest = [
            'schema' => '1.0',
            'type' => 'developer-build-products',
            'build_id' => $build->build_id,
            'website_type' => $websiteType->slug,
            'products' => $attachedProducts,
        ];

        File::put(
            $attachedRoot
            . DIRECTORY_SEPARATOR
            . 'manifest.json',
            json_encode(
                $attachmentManifest,
                JSON_PRETTY_PRINT
                | JSON_UNESCAPED_SLASHES
            )
        );

        $configuration['attached_products'] =
            $attachedProducts;

        $build->update([
            'configuration' => $configuration,
        ]);

        $build->refresh();

        $this->updateBuildProgress(
            $build,
            55,
            'optional_products_assembled'
        );

        /*
         * Record the canonical prepared Website Type manifest in the
         * Developer Build configuration without replacing the existing
         * pricing/selections configuration.
         */
        $configuration =
            is_array($build->configuration)
                ? $build->configuration
                : [];

        $configuration['website_type_package'] = [
            'slug' => $websiteType->slug,
            'package_key' => $websiteType->package_key,
            'package_version' => $websiteType->package_version,
            'manifest' => $websiteTypeManifest,
        ];

        $build->update([
            'configuration' => $configuration,
        ]);

        $build->refresh();

        $this->updateBuildProgress(
            $build,
            60,
            'configuration_ready'
        );

        // ESUBIZ_DEVELOPER_BUILD_PACKAGE_IDENTITY_V3
        //
        // Compilation produces a private, licence-free Website Type
        // distribution. No licence is created, reserved or embedded here.
        //
        // Website Type is the commercial identity selected in Developer
        // Mode. Its prepared distribution contains Core and its configured
        // components. Genuine optional products selected for this build are
        // recorded as coverage metadata.
        //
        // After successful Marketplace payment, Central creates the
        // authoritative entitlement and human-readable licence separately.
        // The customer enters that licence during first-run installation.
        $build->refresh();

        $configuration =
            is_array($build->configuration)
                ? $build->configuration
                : [];

        $selections =
            is_array($configuration['selections'] ?? null)
                ? $configuration['selections']
                : [];

        $coveredProducts = [
            'website_type' =>
                $selections['website_type']
                ?? $build->website_type,

            'addon_bundle' =>
                $selections['addon_bundle']
                ?? $build->addon_bundle,

            'theme' =>
                $selections['theme']
                ?? $build->theme,

            'modules' =>
                $selections['modules']
                ?? (
                    is_array($build->modules)
                        ? $build->modules
                        : []
                ),
        ];

        $packageIdentity = [
            'schema' => '1.0',
            'type' => 'esubiz-developer-website',
            'build_id' => $build->build_id,
            'project_name' => $build->project_name,
            'website_type' => $build->website_type,
            'deployment' => 'off_server',
            'covered_products' => $coveredProducts,
            'licensing' => [
                'authority' => 'Esubiz Central',
                'mode' => 'post_payment',
                'requires_license_at_install' => true,
                'license_embedded' => false,
            ],
        ];

        File::put(
            $dist . DIRECTORY_SEPARATOR . 'esubiz-package.json',
            json_encode(
                $packageIdentity,
                JSON_PRETTY_PRINT
                | JSON_UNESCAPED_SLASHES
                | JSON_UNESCAPED_UNICODE
            )
        );

        $configuration['package_identity'] = $packageIdentity;

        $build->update([
            'configuration' => $configuration,
        ]);

        $build->refresh();

        $documentationDirectory = $dist . '/documentation';

        File::makeDirectory(
            $documentationDirectory,
            0755,
            true,
            true
        );

        $documentation = implode(PHP_EOL, [
            'ESUBIZ COMPILED WEBSITE PACKAGE',
            '===============================',
            '',
            'Project: ' . $build->project_name,
            'Build ID: ' . $build->build_id,
            'Website Type: ' . $build->website_type,
            '',
            'INSTALLATION',
            '------------',
            '1. Complete payment for this Developer Build in Esubiz.',
            '2. Obtain the Website Type licence issued after payment.',
            '3. Download this package from your Developer Library.',
            '4. Upload and unzip the package on the target server.',
            '5. Visit the website root domain to start first-run setup.',
            '6. Enter the issued licence when requested by the installer.',
            '7. Central validates the entitlement and binds the successful',
            '   installation to the permitted off-server Core instance.',
            '',
            'LICENSING',
            '---------',
            'No licence key is embedded in this ZIP.',
            'The authoritative licence is issued separately by Esubiz',
            'after successful payment.',
            '',
            'The Website Type is the primary commercial entitlement for',
            'this initial installation. Genuine products compiled into the',
            'Website Type/build remain recorded in package coverage metadata.',
            '',
            'See esubiz-package.json for non-secret package identity.',
        ]);

        File::put(
            $documentationDirectory . '/README.txt',
            $documentation . PHP_EOL
        );

        // ESUBIZ_DEVELOPER_BROWSER_DOCUMENTATION_V2
        $documentationProject = htmlspecialchars(
            (string) $build->project_name,
            ENT_QUOTES,
            'UTF-8'
        );

        $documentationBuildId = htmlspecialchars(
            (string) $build->build_id,
            ENT_QUOTES,
            'UTF-8'
        );

        /*
         * Browser documentation must never expose a licence secret.
         * The real licence is issued separately after payment.
         */
        $documentationLicenseKey =
            'Issued separately by Esubiz after successful payment';

        // ESUBIZ_DEVELOPER_REAL_INSTALLATION_REQUIREMENTS_V1
        $phpRequirement = 'Defined by the packaged Core composer.json';

        $composerJsonPath = $dist . '/composer.json';

        if (is_file($composerJsonPath)) {
            $composerJson = json_decode(
                (string) File::get($composerJsonPath),
                true
            );

            if (
                is_array($composerJson)
                && isset($composerJson['require']['php'])
                && is_scalar($composerJson['require']['php'])
            ) {
                $phpRequirement =
                    (string) $composerJson['require']['php'];
            }
        }

        $documentationHtml = <<<'HTML'
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Esubiz Website Documentation</title>
<style>
*{box-sizing:border-box}
body{margin:0;font-family:Inter,Arial,sans-serif;background:#f8fafc;color:#0f172a}
.shell{min-height:100vh;display:flex}
aside{width:290px;background:#081a33;color:#fff;padding:28px 18px;position:fixed;inset:0 auto 0 0;overflow:auto}
.brand{font-size:24px;font-weight:900;letter-spacing:-.03em}
.brand span{color:#f59e0b}
.meta{margin:10px 0 24px;color:#cbd5e1;font-size:12px;line-height:1.6}
.nav button{width:100%;border:0;background:transparent;color:#cbd5e1;text-align:left;padding:11px 13px;border-radius:10px;font-weight:700;cursor:pointer;margin:2px 0}
.nav button:hover,.nav button.active{background:#17345d;color:#fff}
main{margin-left:290px;width:calc(100% - 290px);padding:42px}
.panel{display:none;max-width:950px;background:#fff;border:1px solid #e2e8f0;border-radius:20px;padding:32px;box-shadow:0 10px 35px rgba(15,23,42,.05)}
.panel.active{display:block}
h1{font-size:30px;margin:0 0 10px}
h2{font-size:22px;margin-top:0}
h3{margin-top:28px}
p,li{line-height:1.75;color:#475569}
code{background:#f1f5f9;padding:2px 6px;border-radius:6px}
.note{background:#eff6ff;border-left:4px solid #2563eb;padding:14px 16px;border-radius:8px;margin:18px 0}
.warn{background:#fff7ed;border-left:4px solid #f97316;padding:14px 16px;border-radius:8px;margin:18px 0}
table{border-collapse:collapse;width:100%;margin:18px 0}
td,th{border:1px solid #e2e8f0;padding:10px;text-align:left}
@media(max-width:850px){
 aside{position:relative;width:100%;height:auto}
 .shell{display:block}
 main{margin:0;width:100%;padding:20px}
}
</style>
</head>
<body>
<div class="shell">
<aside>
    <div class="brand">ESU<span>BIZ</span></div>
    <div class="meta">
        Compiled Website Documentation<br>
        Project: {{PROJECT}}<br>
        Build: {{BUILD}}
    </div>

    <div class="nav">
        <button class="active" data-tab="welcome">Overview</button>
        <button data-tab="requirements">Server Requirements</button>
        <button data-tab="upload">Upload & Extract</button>
        <button data-tab="database">Database</button>
        <button data-tab="environment">Environment / .env</button>
        <button data-tab="install">Installation</button>
        <button data-tab="domain">Domain & SSL</button>
        <button data-tab="license">Licence Activation</button>
        <button data-tab="admin">Administrator Login</button>
        <button data-tab="settings">Website Settings</button>
        <button data-tab="pages">Pages & Content</button>
        <button data-tab="media">Media</button>
        <button data-tab="forms">Forms</button>
        <button data-tab="users">Users, Roles & Permissions</button>
        <button data-tab="themes">Themes</button>
        <button data-tab="modules">Modules</button>
        <button data-tab="addons">Add-ons</button>
        <button data-tab="payments">Payments</button>
        <button data-tab="email">Email</button>
        <button data-tab="deployment">Production Deployment</button>
        <button data-tab="security">Security</button>
        <button data-tab="updates">Updates</button>
        <button data-tab="backup">Backup & Restore</button>
        <button data-tab="troubleshooting">Troubleshooting</button>
    </div>
</aside>

<main>
<section class="panel active" id="welcome">
<h1>Esubiz Website Installation Guide</h1>
<p>This package contains a prepared Esubiz Website/Core distribution compiled for <strong>{{PROJECT}}</strong>.</p>
<div class="note">Keep the entire package intact during installation. The licence supplied with this build covers the compiled Website/Core and the genuine Esubiz products included in the build.</div>
<table>
<tr><th>Project</th><td>{{PROJECT}}</td></tr>
<tr><th>Build ID</th><td>{{BUILD}}</td></tr>
<tr><th>Licence</th><td>{{LICENSE}}</td></tr>
<tr><th>Licence Scope</th><td>Compiled Website/Core</td></tr>
<tr><th>Domains</th><td>1</td></tr>
</table>
</section>

<section class="panel" id="requirements">
<h2>Server Requirements</h2>

<table>
<tr>
    <th>PHP</th>
    <td>{{PHP_REQUIREMENT}}</td>
</tr>
<tr>
    <th>Database</th>
    <td>MySQL or MariaDB with UTF8MB4 support</td>
</tr>
<tr>
    <th>Web Server</th>
    <td>Apache or Nginx with HTTPS support</td>
</tr>
<tr>
    <th>PHP Extensions</th>
    <td>
        PDO / PDO MySQL, OpenSSL, Mbstring, Tokenizer,
        XML, Ctype, Fileinfo, cURL and ZIP, plus any
        additional extensions required by composer.json.
    </td>
</tr>
<tr>
    <th>Composer</th>
    <td>Composer 2.x where dependency installation is required</td>
</tr>
<tr>
    <th>SSL</th>
    <td>Required for production websites</td>
</tr>
</table>

<div class="note">
The PHP version displayed above is read directly from the
<code>composer.json</code> supplied with this compiled Esubiz Core.
</div>

<h3>Required Writable Directories</h3>
<p>
The web-server/PHP process must be able to write to
<code>storage/</code> and <code>bootstrap/cache/</code>.
Do not make the complete application directory globally writable.
</p>

<h3>Document Root</h3>
<p>
Configure the domain or virtual host document root to the
<code>public/</code> directory inside the extracted Esubiz Core.
The application root itself must not be publicly exposed.
</p>
</section>

<section class="panel" id="upload">
<h2>Upload & Extract</h2>
<ol>
<li>Upload the downloaded Esubiz ZIP to the target server.</li>
<li>Extract it into the intended application directory.</li>
<li>Do not expose the <code>license</code>, environment or storage files as public downloads.</li>
<li>Set the website document root to the application's public web directory.</li>
</ol>
</section>

<section class="panel" id="database">
<h2>Database Setup</h2>

<p>
Create one dedicated MySQL/MariaDB database and one database user
for the website. UTF8MB4 should be used so the website can safely
store full Unicode content.
</p>

<h3>Example SQL</h3>
<pre><code>CREATE DATABASE esubiz_site
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci;

CREATE USER 'esubiz_user'@'localhost'
IDENTIFIED BY 'USE-A-STRONG-PASSWORD';

GRANT ALL PRIVILEGES
ON esubiz_site.*
TO 'esubiz_user'@'localhost';

FLUSH PRIVILEGES;</code></pre>

<p>
Use your hosting provider's actual database name, username,
password and host where they differ from the example.
</p>

<h3>.env Database Values</h3>
<pre><code>DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=esubiz_site
DB_USERNAME=esubiz_user
DB_PASSWORD=your-secure-password</code></pre>

<div class="warn">
Do not use the database root account as the production website's
application database user.
</div>
</section>

<section class="panel" id="environment">
<h2>Environment Configuration</h2>

<p>
Extract <code>esubizcore.zip</code> first. From the Core application
root, create the environment file from <code>.env.example</code>
where supplied.
</p>

<pre><code>cp .env.example .env</code></pre>

<p>At minimum configure:</p>

<pre><code>APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-domain.com

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=your_database
DB_USERNAME=your_database_user
DB_PASSWORD=your_database_password</code></pre>

<div class="warn">
Keep <code>.env</code> private. Never place it inside the public
document root, commit it to a public repository, or send production
credentials through an unsecured channel.
</div>
</section>

<section class="panel" id="install">
<h2>Installation</h2>

<ol>
<li>Download and extract <code>esubiz.zip</code>.</li>
<li>Extract <code>esubizcore.zip</code> into the website application directory.</li>
<li>Point the domain document root to the extracted Core <code>public/</code> directory.</li>
<li>Create the MySQL/MariaDB database and database user.</li>
<li>Create and configure <code>.env</code>.</li>
<li>Install Composer dependencies if the package requires them.</li>
<li>Generate the Laravel application key where one has not already been supplied for installation.</li>
<li>Run the required database migrations.</li>
<li>Create the public storage link where required.</li>
<li>Clear and rebuild application caches.</li>
<li>Open the website over HTTPS and complete Esubiz licence activation.</li>
</ol>

<h3>Typical Laravel Commands</h3>

<pre><code>composer install --no-dev --optimize-autoloader

php artisan key:generate
php artisan migrate --force
php artisan storage:link
php artisan optimize:clear</code></pre>

<div class="note">
Run commands from the extracted Esubiz Core application root.
If your hosting panel provides its own Laravel deployment tools,
the equivalent panel operations may be used instead.
</div>
</section>

<section class="panel" id="domain">
<h2>Domain & SSL</h2>
<p>Point the intended domain to the server and configure the web server for the Esubiz installation.</p>
<p>Install a valid SSL certificate and use HTTPS for production traffic.</p>
<div class="note">The primary Esubiz licence is permanently associated with the first legitimate domain activation allowed by the licence.</div>
</section>

<section class="panel" id="license">
<h2>Licence Activation</h2>
<p>Your build licence key is:</p>
<p><code>{{LICENSE}}</code></p>
<p>The licence starts in a pending state and is intended for one legitimate domain. On first valid activation, Central licensing associates the installation with that domain.</p>
<p>Your licence is issued separately by Esubiz after successful payment and is not embedded in this package. See <code>esubiz-package.json</code> for non-secret package identity.</p>
</section>

<section class="panel" id="admin">
<h2>Administrator Login</h2>
<p>After installation, open the website administration login area and sign in using the administrator account configured for the installed website.</p>
<p>Immediately confirm the administrator name, email, phone and password/security settings.</p>
</section>

<section class="panel" id="settings">
<h2>Website Settings</h2>
<p>Use Website Settings to configure branding, website identity, contact information, locale, timezone and the available Core configuration for the installed Website Type.</p>
</section>

<section class="panel" id="pages">
<h2>Pages & Content</h2>
<p>Create, edit, publish and manage website pages from the internal page tools. Keep slugs unique and review public visibility before publishing.</p>
</section>

<section class="panel" id="media">
<h2>Media</h2>
<p>Use the Media area for website images and files. Optimise large images before upload and maintain meaningful filenames and alternative text where applicable.</p>
</section>

<section class="panel" id="forms">
<h2>Forms</h2>
<p>The Core Basic Form Builder can be used for supported website forms and submissions. Configure form fields, publication and submission handling from the internal administration area.</p>
</section>

<section class="panel" id="users">
<h2>Users, Roles & Permissions</h2>
<p>Create staff/users only as required. Assign the least privilege necessary for each account and review administrator access carefully.</p>
</section>

<section class="panel" id="themes">
<h2>Themes</h2>
<p>The prepared Website Type includes its configured theme foundation. Genuine additional themes included in the compiled build remain covered by the primary build licence.</p>
</section>

<section class="panel" id="modules">
<h2>Modules</h2>
<p>Modules selected for this build are recorded in the compiled configuration and licence scope. Manage available module features from the website administration area.</p>
</section>

<section class="panel" id="addons">
<h2>Add-ons</h2>
<p>Genuine Esubiz add-ons included during compilation inherit this build's primary domain licence. A separately purchased standalone product follows its own applicable licence rules.</p>
</section>

<section class="panel" id="payments">
<h2>Payments</h2>
<p>Configure only the payment gateways available to the installed Core and purchased products. Store gateway credentials securely and test in the provider's supported test mode before accepting live payments.</p>
</section>

<section class="panel" id="email">
<h2>Email</h2>
<p>Configure the website's outbound mail settings using valid server/provider credentials. Send a test message after configuration.</p>
</section>

<section class="panel" id="deployment">
<h2>Production Deployment</h2>

<h3>Permissions</h3>
<p>
The application owner should own the Core files. Only
<code>storage/</code> and <code>bootstrap/cache/</code>
need runtime write access.
</p>

<h3>Scheduler</h3>
<p>
If scheduled Core features are enabled, configure the Laravel
scheduler to run every minute:
</p>

<pre><code>* * * * * cd /path/to/esubizcore &amp;&amp; php artisan schedule:run &gt;&gt; /dev/null 2&gt;&amp;1</code></pre>

<h3>Queue Worker</h3>
<p>
If a feature uses queued jobs, run a persistent Laravel queue
worker using Supervisor, systemd or the hosting provider's process
manager.
</p>

<pre><code>php artisan queue:work --sleep=3 --tries=3</code></pre>

<h3>Production Mode</h3>
<pre><code>APP_ENV=production
APP_DEBUG=false</code></pre>

<p>
Never leave debug mode enabled on a live public website.
</p>
</section>

<section class="panel" id="security">
<h2>Security</h2>
<ul>
<li>Use HTTPS.</li>
<li>Use strong administrator credentials.</li>
<li>Keep environment and licence secrets private.</li>
<li>Use supported genuine Esubiz packages.</li>
<li>Maintain server, PHP and database security updates.</li>
<li>Back up the website and database regularly.</li>
</ul>
</section>

<section class="panel" id="updates">
<h2>Updates</h2>
<p>Genuine Esubiz products retain their applicable managed update entitlement while they remain genuine and validly licensed.</p>
<p>A modified or nulled product does not invalidate unrelated genuine Core components, but that modified product loses its applicable Esubiz-managed update entitlement until restored and validated.</p>
</section>

<section class="panel" id="backup">
<h2>Backup & Restore</h2>
<p>Back up both application files and the database. Keep at least one recent backup outside the production server and test restoration procedures periodically.</p>
</section>

<section class="panel" id="troubleshooting">
<h2>Troubleshooting</h2>
<p>When diagnosing an installation, check the application environment, database connectivity, server permissions, domain/SSL configuration and application logs first.</p>
<p>Do not replace the licence or regenerate the build merely to solve an ordinary server configuration problem.</p>
</section>
</main>
</div>

<script>
document.querySelectorAll('.nav button').forEach(function(button){
    button.addEventListener('click', function(){
        document.querySelectorAll('.nav button').forEach(function(b){
            b.classList.remove('active');
        });
        document.querySelectorAll('.panel').forEach(function(panel){
            panel.classList.remove('active');
        });
        button.classList.add('active');
        document.getElementById(button.dataset.tab).classList.add('active');
    });
});
</script>
</body>
</html>
HTML;

        $documentationHtml = str_replace(
            [
                '{{PROJECT}}',
                '{{BUILD}}',
                '{{LICENSE}}',
                '{{PHP_REQUIREMENT}}',
            ],
            [
                $documentationProject,
                $documentationBuildId,
                $documentationLicenseKey,
                htmlspecialchars(
                    $phpRequirement,
                    ENT_QUOTES,
                    'UTF-8'
                ),
            ],
            $documentationHtml
        );

        File::put(
            $documentationDirectory . '/index.html',
            $documentationHtml
        );

        /*
         * Dependency-free PDF so compilation does not depend on a
         * separate PDF package being installed on the Central server.
         */
        $pdfLines = [
            'ESUBIZ',
            'OFF-SERVER WEBSITE LICENSE CERTIFICATE',
            '',
            'Project: ' . $build->project_name,
            'Build ID: ' . $build->build_id,
            'Registration: ' . $licenseRegistration->uuid,
            'License: Issued separately by Esubiz after successful payment',
            'License Type: Developer Build',
            'License Scope: Compiled Website/Core',
            'Website Type: ' . (
                is_scalar($coveredProducts['website_type'])
                    ? (string) $coveredProducts['website_type']
                    : json_encode($coveredProducts['website_type'])
            ),
            'Domains Per License: 1',
            'Status: ' . strtoupper((string) $licenseRegistration->status),
            'Issued: ' . $licenseRegistration->created_at,
            'Registered Domain: ' . (
                $licenseRegistration->registered_domain ?: 'Pending first activation'
            ),
            '',
            'VALIDITY AND USAGE',
            'This license authorizes one genuine Esubiz compiled Website/Core',
            'installation for one domain. The first legitimate activation',
            'binds the primary license to that domain.',
            '',
            'The primary license also covers genuine Esubiz products included',
            'in this Developer Build. It may not be reused for another domain.',
            '',
            'Modified or nulled products do not invalidate unrelated genuine',
            'Core components, but the modified product loses its applicable',
            'Esubiz-managed update entitlement until restored and validated.',
            '',
            'Generated by Esubiz Developer Builder.',
        ];

        $escapePdf = static function (string $value): string {
            return str_replace(
                ['\\', '(', ')'],
                ['\\\\', '\\(', '\\)'],
                $value
            );
        };

        $stream = "BT\n/F1 11 Tf\n50 790 Td\n";

        foreach ($pdfLines as $index => $line) {
            if ($index > 0) {
                $stream .= "0 -18 Td\n";
            }

            $stream .= '(' . $escapePdf($line) . ") Tj\n";
        }

        $stream .= "ET\n";

        $objects = [];

        $objects[] =
            "<< /Type /Catalog /Pages 2 0 R >>";

        $objects[] =
            "<< /Type /Pages /Kids [3 0 R] /Count 1 >>";

        $objects[] =
            "<< /Type /Page /Parent 2 0 R "
            . "/MediaBox [0 0 595 842] "
            . "/Resources << /Font << /F1 5 0 R >> >> "
            . "/Contents 4 0 R >>";

        $objects[] =
            "<< /Length " . strlen($stream) . " >>\n"
            . "stream\n"
            . $stream
            . "endstream";

        $objects[] =
            "<< /Type /Font /Subtype /Type1 "
            . "/BaseFont /Helvetica >>";

        $pdf = "%PDF-1.4\n";
        $offsets = [0];

        foreach ($objects as $number => $object) {
            $offsets[] = strlen($pdf);

            $pdf .= ($number + 1)
                . " 0 obj\n"
                . $object
                . "\nendobj\n";
        }

        $xref = strlen($pdf);

        $pdf .= "xref\n";
        $pdf .= "0 " . (count($objects) + 1) . "\n";
        $pdf .= "0000000000 65535 f \n";

        for ($i = 1; $i <= count($objects); $i++) {
            $pdf .= sprintf(
                "%010d 00000 n \n",
                $offsets[$i]
            );
        }

        $pdf .= "trailer\n";
        $pdf .= "<< /Size "
            . (count($objects) + 1)
            . " /Root 1 0 R >>\n";
        $pdf .= "startxref\n";
        $pdf .= $xref . "\n";
        $pdf .= "%%EOF\n";

        File::put(
            $licenseDirectory . '/Esubiz-License.pdf',
            $pdf
        );

        $this->updateBuildProgress(
            $build,
            80,
            'license_documentation_ready'
        );

        $manifest = $root . '/project.json';

        if (!File::exists($manifest)) {
            // ESUBIZ_DEVELOPER_CANONICAL_PROJECT_MANIFEST_V1
            //
            // Developer compilation now starts from the canonical
            // prepared Website Type distribution. Its manifest.json is
            // therefore authoritative when the legacy temporary project
            // manifest is absent.
            //
            // Do not manufacture a second project manifest here.
            $preparedManifestPath =
                $dist
                . DIRECTORY_SEPARATOR
                . 'manifest.json';

            if (!is_file($preparedManifestPath)) {
                throw new RuntimeException(
                    'Prepared Website Type manifest not found.'
                );
            }

            $manifest = $preparedManifestPath;
        }

        $zipDirectory = storage_path('app/developer-builds/packages');

        /*
         * ESUBIZ_DEVELOPER_PACKAGE_DIRECTORY_SAFE_CREATE_V1
         *
         * This is a persistent shared directory across Developer Builds.
         * Do not call mkdir() again when it already exists.
         */
        if (!File::isDirectory($zipDirectory)) {
            File::makeDirectory(
                $zipDirectory,
                0755,
                true
            );
        }

        if (!File::isDirectory($zipDirectory)) {
            throw new RuntimeException(
                'Unable to prepare Developer Build package directory.'
            );
        }

        $zipPath = $zipDirectory . '/' . $buildId . '.zip';

        if (File::exists($zipPath)) {
            File::delete($zipPath);
        }

        /*
         * ESUBIZ_DEVELOPER_NESTED_CORE_PACKAGE_V1
         *
         * Commercial delivery package:
         *
         * esubiz.zip
         *   ├── esubizcore.zip
         *   ├── documentation/
         *   ├── license/
         *   ├── esubiz-products/
         *   └── manifest.json
         *
         * The nested esubizcore.zip is the actual installable
         * prepared Website Type/Core distribution.
         */

        $coreZipPath =
            $root . DIRECTORY_SEPARATOR . 'esubizcore.zip';

        if (File::exists($coreZipPath)) {
            File::delete($coreZipPath);
        }

        $coreZip = new \ZipArchive();

        if (
            $coreZip->open(
                $coreZipPath,
                \ZipArchive::CREATE | \ZipArchive::OVERWRITE
            ) !== true
        ) {
            throw new RuntimeException(
                'Unable to create installable Esubiz Core package.'
            );
        }

        $distFiles = File::allFiles($dist);
        $coreFilesCount = 0;

        foreach ($distFiles as $file) {
            $relativePath = str_replace(
                $dist . DIRECTORY_SEPARATOR,
                '',
                $file->getPathname()
            );

            $normalizedPath = str_replace(
                DIRECTORY_SEPARATOR,
                '/',
                $relativePath
            );

            /*
             * Commercial wrapper assets belong in esubiz.zip,
             * not inside esubizcore.zip.
             */
            if (
                str_starts_with($normalizedPath, 'documentation/')
                || str_starts_with($normalizedPath, 'license/')
                || str_starts_with($normalizedPath, 'esubiz-products/')
            ) {
                continue;
            }

            $coreZip->addFile(
                $file->getPathname(),
                $normalizedPath
            );

            $coreFilesCount++;
        }

        if ($coreFilesCount < 1) {
            $coreZip->close();

            throw new RuntimeException(
                'Prepared Website Type contains no installable Core files.'
            );
        }

        $coreZip->close();

        if (
            !File::exists($coreZipPath)
            || File::size($coreZipPath) < 1
        ) {
            throw new RuntimeException(
                'Installable Esubiz Core ZIP was not created.'
            );
        }

        $zip = new \ZipArchive();

        if (
            $zip->open(
                $zipPath,
                \ZipArchive::CREATE | \ZipArchive::OVERWRITE
            ) !== true
        ) {
            throw new RuntimeException(
                'Unable to create Esubiz delivery package.'
            );
        }

        $zip->addFile(
            $coreZipPath,
            'esubizcore.zip'
        );

        $wrapperFilesCount = 1;

        foreach ($distFiles as $file) {
            $relativePath = str_replace(
                $dist . DIRECTORY_SEPARATOR,
                '',
                $file->getPathname()
            );

            $normalizedPath = str_replace(
                DIRECTORY_SEPARATOR,
                '/',
                $relativePath
            );

            if (
                str_starts_with($normalizedPath, 'documentation/')
                || str_starts_with($normalizedPath, 'license/')
                || str_starts_with($normalizedPath, 'esubiz-products/')
                || $normalizedPath === 'manifest.json'
            ) {
                $zip->addFile(
                    $file->getPathname(),
                    $normalizedPath
                );

                $wrapperFilesCount++;
            }
        }

        $zip->close();

        $files = $distFiles;

        $build = DeveloperBuild::where('build_id', $buildId)->first();

        if ($build) {
            $build->update([
                'status' => 'success',
                'files_count' => count($files),
                'package_size' => File::size($zipPath),
                'package_reference' => $zipPath,
                'completed_at' => now(),
            ]);

            $this->updateBuildProgress(
                $build,
                100,
                'completed'
            );
        }

        return [
            'build_id' => $buildId,
            'dist' => $dist,
            'package' => $zipPath,
            'files' => count($files),
            'size' => File::size($zipPath),
        ];
    }
}
