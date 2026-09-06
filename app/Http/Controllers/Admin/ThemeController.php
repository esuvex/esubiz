<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\WebsiteType;
use App\Services\Marketplace\ThemePackageService;
use App\Services\Marketplace\ThemePackageStorageService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use RuntimeException;

class ThemeController extends Controller
{
    public function __construct(
        protected ThemePackageService $themePackageService,
        protected ThemePackageStorageService $themePackageStorage
    ) {
    }


    /**
     * Admin theme registry.
     *
     * Published releases are immutable.
     * New development creates a new version.
     */
    public function index()
    {
        $themes = DB::table('theme_packages')
            ->whereNull('theme_packages.deleted_at')
            ->leftJoin(
                'catalog_products',
                'catalog_products.id',
                '=',
                'theme_packages.catalog_product_id'
            )
            ->select([
                'theme_packages.*',
                'catalog_products.name as catalog_name',
                'catalog_products.audience as catalog_audience',
                'catalog_products.is_active as catalog_active',
            ])
            ->orderBy('theme_packages.name')
            ->orderByDesc('theme_packages.created_at')
            ->paginate(10);

        $websiteTypes = WebsiteType::query()
            ->whereNull('deleted_at')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        $themeWebsiteTypes =
            DB::table(
                'theme_package_website_type'
            )
                ->get()
                ->groupBy('theme_package_id')
                ->map(
                    fn ($rows) => $rows
                        ->pluck('website_type_id')
                        ->map(fn ($id) => (int) $id)
                        ->values()
                        ->all()
                );


        $storageThemePackages =
            $this->themePackageStorage
                ->packages();


        return view(
            'admin.themes.index',
            compact(
                'themes',
                'websiteTypes',
                'themeWebsiteTypes',
                'storageThemePackages'
            )
        );
    }


    /**
     * Update commercial/listing configuration.
     *
     * This does NOT rewrite a published package ZIP.
     */
    /*
     * ESUBIZ_ADMIN_THEME_PACKAGE_INGESTION_V1
     *
     * Admin Theme package sources:
     *
     * 1. Upload Theme Package
     * 2. Select Theme Package from protected product storage
     *
     * Both sources converge into the existing ThemePackageService.
     *
     * The package format is universal. SaaS/off-server availability,
     * pricing, duration, Marketplace visibility and wizard visibility
     * remain database/commercial configuration and do not create
     * separate ZIP formats.
     */
    /**
     * Dedicated Theme package setup page.
     */
    public function create()
    {
        $storageThemePackages =
            $this->themePackageStorage
                ->packages();

        return view(
            'admin.themes.create',
            compact(
                'storageThemePackages'
            )
        );
    }


    public function storePackage(
        Request $request
    ) {
        $data = $request->validate([
            'package_source' => [
                'required',
                Rule::in([
                    'upload',
                    'storage',
                ]),
            ],

            'theme_package' => [
                'nullable',
                'file',
                'mimes:zip',
                'max:102400',
                'required_if:package_source,upload',
            ],

            'storage_package' => [
                'nullable',
                'string',
                'max:255',
                'required_if:package_source,storage',
            ],
        ]);


        $source =
            $data['package_source'];


        /*
         * Resolve the source to one local ZIP path.
         */
        if ($source === 'storage') {

            $packagePath =
                $this->themePackageStorage
                    ->resolve(
                        $data['storage_package']
                    );

        } else {

            $uploaded =
                $request->file(
                    'theme_package'
                );

            abort_unless(
                $uploaded
                && $uploaded->isValid(),
                422,
                'The uploaded Theme package is invalid.'
            );


            /*
             * Uploaded packages first enter protected product storage.
             * This keeps Admin and future Developer package ingestion
             * on the same storage boundary.
             */
            $filename =
                now()->format(
                    'YmdHis'
                )
                . '-'
                . Str::slug(
                    pathinfo(
                        $uploaded->getClientOriginalName(),
                        PATHINFO_FILENAME
                    )
                )
                . '.zip';


            $destination =
                $this->themePackageStorage
                    ->root()
                . DIRECTORY_SEPARATOR
                . $filename;


            if (
                !is_dir(
                    dirname($destination)
                )
            ) {
                mkdir(
                    dirname($destination),
                    0755,
                    true
                );
            }


            if (
                !$uploaded->move(
                    dirname($destination),
                    basename($destination)
                )
            ) {
                throw new RuntimeException(
                    'Unable to store the uploaded Theme package.'
                );
            }


            $packagePath =
                $destination;
        }


        /*
         * ESUBIZ_GENERIC_THEME_PACKAGE_REGISTRATION_V1
         *
         * ThemePackageService is the authoritative package validator.
         *
         * This registration flow is intentionally generic:
         *
         * - Business
         * - Ecommerce
         * - Hotel
         * - School
         * - third-party Developer themes
         * - all future Core-compatible Theme packages
         *
         * all use this exact same manifest-driven pipeline.
         *
         * No Theme slug/name is hard-coded here.
         */
        /*
         * ESUBIZ_THEME_PACKAGE_VALIDATION_FEEDBACK_V1
         *
         * Invalid ZIPs must return to the Theme setup page with a
         * user-facing validation message instead of exposing Laravel's
         * exception screen.
         *
         * ThemePackageService remains the authoritative validator.
         */
        try {

            $manifest =
                $this->themePackageService
                    ->validatePackage(
                        $packagePath
                    );

        } catch (\Throwable $e) {

            return redirect()
                ->route(
                    'admin.themes.create'
                )
                ->withInput(
                    $request->except(
                        'theme_package'
                    )
                )
                ->with(
                    'error',
                    'Invalid Theme Package: '
                    . $e->getMessage()
                );
        }


        $theme =
            $manifest['theme'];

        $publisher =
            $manifest['publisher'];

        $compatibility =
            $manifest['compatibility']
            ?? [];

        $files =
            $manifest['files']
            ?? [];


        $slug =
            trim(
                (string) $theme['slug']
            );

        $version =
            trim(
                (string) $theme['version']
            );


        /*
         * Package identity and integrity facts.
         */
        $packageBytes =
            filesize(
                $packagePath
            );

        if ($packageBytes === false) {
            throw new RuntimeException(
                'Unable to determine Theme package size.'
            );
        }


        $checksum =
            hash_file(
                'sha256',
                $packagePath
            );

        if ($checksum === false) {
            throw new RuntimeException(
                'Unable to calculate Theme package checksum.'
            );
        }


        /*
         * Store a protected-root-relative package reference whenever
         * possible instead of coupling the database to the server's
         * absolute filesystem path.
         */
        $protectedRoot =
            rtrim(
                $this->themePackageService
                    ->protectedRoot(),
                DIRECTORY_SEPARATOR
            );

        $normalizedPackagePath =
            realpath(
                $packagePath
            )
            ?: $packagePath;

        $normalizedRoot =
            realpath(
                $protectedRoot
            )
            ?: $protectedRoot;


        if (
            str_starts_with(
                $normalizedPackagePath,
                $normalizedRoot
                . DIRECTORY_SEPARATOR
            )
        ) {
            $storedPackagePath =
                ltrim(
                    substr(
                        $normalizedPackagePath,
                        strlen(
                            $normalizedRoot
                        )
                    ),
                    DIRECTORY_SEPARATOR
                );
        } else {
            /*
             * This should normally never happen because Admin uploads
             * are first moved into protected Theme storage.
             */
            $storedPackagePath =
                basename(
                    $normalizedPackagePath
                );
        }


        $manifestPath =
            'theme.json';

        $previewPath =
            isset($files['preview'])
                ? (string) $files['preview']
                : null;


        /*
         * Package compatibility is a package fact.
         *
         * SaaS/off-server SALE AVAILABILITY is deliberately NOT copied
         * from this value. Admin controls commercial availability
         * independently in the Theme Marketplace settings.
         */
        $deploymentCompatibility =
            array_values(
                array_unique(
                    array_map(
                        'strval',
                        $compatibility['deployment']
                        ?? []
                    )
                )
            );


        $packageFacts = [
            'name' =>
                (string) $theme['name'],

            'publisher_name' =>
                (string) $publisher['display_name'],

            'publisher_type' =>
                (string) (
                    $publisher['type']
                    ?? 'developer'
                ),

            'package_path' =>
                $storedPackagePath,

            'manifest_path' =>
                $manifestPath,

            'preview_path' =>
                $previewPath,

            'package_bytes' =>
                (int) $packageBytes,

            'checksum_sha256' =>
                $checksum,

            /*
             * A package that passes the authoritative validator is
             * structurally Marketplace-ready. Public purchasing still
             * separately requires:
             *
             * is_active
             * marketplace_enabled
             * deployment availability
             */
            'marketplace_ready' =>
                true,

            'metadata' =>
                json_encode(
                    [
                        'schema' =>
                            $manifest['schema']
                            ?? null,

                        'schema_version' =>
                            $manifest['schema_version']
                            ?? null,

                        'description' =>
                            $theme['description']
                            ?? null,

                        'compatibility' =>
                            [
                                'deployment' =>
                                    $deploymentCompatibility,
                            ],

                        'integration' =>
                            $manifest['integration']
                            ?? [],

                        'files' =>
                            $files,
                    ],
                    JSON_UNESCAPED_SLASHES
                    | JSON_UNESCAPED_UNICODE
                ),

            'updated_at' =>
                now(),
        ];


        DB::transaction(
            function () use (
                $slug,
                $version,
                $packageFacts
            ) {

                $existing =
                    DB::table(
                        'theme_packages'
                    )
                        ->where(
                            'slug',
                            $slug
                        )
                        ->where(
                            'version',
                            $version
                        )
                        ->whereNull(
                            'deleted_at'
                        )
                        ->first();


                if ($existing) {

                    /*
                     * Re-registering a package refreshes ONLY package
                     * facts.
                     *
                     * It must NOT reset:
                     * - SaaS/off-server prices
                     * - SaaS duration
                     * - deployment sales availability
                     * - active state
                     * - Marketplace enabled/featured state
                     * - wizard visibility
                     * - Website Type assignment
                     * - release configuration
                     * - commissions
                     */
                    DB::table(
                        'theme_packages'
                    )
                        ->where(
                            'id',
                            $existing->id
                        )
                        ->update(
                            $packageFacts
                        );

                    return;
                }


                /*
                 * A new Theme package gets neutral/default commercial
                 * configuration. Admin configures sales after package
                 * registration.
                 */
                DB::table(
                    'theme_packages'
                )
                    ->insert(
                        array_merge(
                            [
                                'uuid' =>
                                    (string) Str::uuid(),

                                'slug' =>
                                    $slug,

                                'version' =>
                                    $version,

                                /*
                                 * Commercial availability remains
                                 * explicitly controlled by Admin.
                                 */
                                'saas_available' =>
                                    false,

                                'off_server_available' =>
                                    false,

                                'marketplace_enabled' =>
                                    false,

                                'marketplace_featured' =>
                                    false,

                                'is_active' =>
                                    true,

                                'show_in_user_wizard' =>
                                    true,

                                'show_in_developer_wizard' =>
                                    true,

                                'created_at' =>
                                    now(),
                            ],
                            $packageFacts
                        )
                    );
            }
        );


        return redirect()
            ->route(
                'admin.themes.index'
            )
            ->with(
                'success',
                'Theme package processed successfully.'
            );
    }


    /**
     * ESUBIZ_THEME_EDIT_PAGE_V1
     *
     * Dedicated Theme commercial / Marketplace setup page.
     */
    /**
     * ESUBIZ_THEME_SHOW_PAGE_V1
     *
     * Read-only Theme package details page.
     */
    public function show(
        int $theme
    ) {
        $themePackage =
            DB::table('theme_packages')
                ->where('id', $theme)
                ->whereNull('deleted_at')
                ->first();

        abort_unless(
            $themePackage,
            404
        );


        $websiteTypes =
            DB::table('website_types')
                ->join(
                    'theme_package_website_type',
                    'theme_package_website_type.website_type_id',
                    '=',
                    'website_types.id'
                )
                ->where(
                    'theme_package_website_type.theme_package_id',
                    $theme
                )
                ->whereNull(
                    'website_types.deleted_at'
                )
                ->orderBy(
                    'website_types.sort_order'
                )
                ->orderBy(
                    'website_types.name'
                )
                ->select(
                    'website_types.id',
                    'website_types.name',
                    'website_types.slug'
                )
                ->get();


        return view(
            'admin.themes.show',
            [
                'theme' =>
                    $themePackage,

                'websiteTypes' =>
                    $websiteTypes,
            ]
        );
    }


    public function edit(
        int $theme
    ) {
        $themePackage =
            DB::table('theme_packages')
                ->where('id', $theme)
                ->whereNull('deleted_at')
                ->first();

        abort_unless(
            $themePackage,
            404
        );


        $websiteTypes =
            WebsiteType::query()
                ->whereNull('deleted_at')
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->orderBy('name')
                ->get();


        $selectedWebsiteTypes =
            DB::table(
                'theme_package_website_type'
            )
                ->where(
                    'theme_package_id',
                    $theme
                )
                ->pluck(
                    'website_type_id'
                )
                ->map(
                    fn ($id) => (int) $id
                )
                ->all();


        return view(
            'admin.themes.edit',
            [
                'theme' =>
                    $themePackage,

                'websiteTypes' =>
                    $websiteTypes,

                'selectedWebsiteTypes' =>
                    $selectedWebsiteTypes,
            ]
        );
    }


    public function update(
        Request $request,
        int $theme
    ) {
        $record = DB::table('theme_packages')
            ->where('id', $theme)
            ->whereNull('deleted_at')
            ->first();

        abort_unless(
            $record,
            404,
            'Theme package not found.'
        );


        $data = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
            ],

            'publisher_name' => [
                'required',
                'string',
                'max:255',
            ],

            'publisher_type' => [
                'required',
                'string',
                'max:30',
            ],

            'saas_price' => [
                'nullable',
                'numeric',
                'min:0',
            ],

            'saas_currency' => [
                'nullable',
                'string',
                'size:3',
            ],

            'saas_billing_period' => [
                'nullable',
                'integer',
                'min:1',
            ],

            'saas_billing_interval' => [
                'nullable',
                'string',
                'max:30',
            ],

            'off_server_price' => [
                'nullable',
                'numeric',
                'min:0',
            ],

            'off_server_currency' => [
                'nullable',
                'string',
                'size:3',
            ],

            'marketplace_category' => [
                'nullable',
                'string',
                'max:100',
            ],

            'commission_rate' => [
                'nullable',
                'numeric',
                'min:0',
                'max:100',
            ],

            'show_in_user_wizard' => [
                'nullable',
                'boolean',
            ],

            'show_in_developer_wizard' => [
                'nullable',
                'boolean',
            ],

            'website_type_ids' => [
                'nullable',
                'array',
            ],

            'website_type_ids.*' => [
                'integer',
                'exists:website_types,id',
            ],

            'release_notes' => [
                'nullable',
                'string',
            ],
        ]);


        $saasAvailable =
            $request->boolean(
                'saas_available'
            );

        $offServerAvailable =
            $request->boolean(
                'off_server_available'
            );

        $marketplaceEnabled =
            $request->boolean(
                'marketplace_enabled'
            );

        $marketplaceFeatured =
            $request->boolean(
                'marketplace_featured'
            );

        $isActive =
            $request->boolean(
                'is_active'
            );

        $showInUserWizard =
            $request->boolean(
                'show_in_user_wizard'
            );

        $showInDeveloperWizard =
            $request->boolean(
                'show_in_developer_wizard'
            );

        $websiteTypeIds = collect(
            $data['website_type_ids']
            ?? []
        )
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values()
            ->all();


        DB::transaction(
            function () use (
                $record,
                $data,
                $saasAvailable,
                $offServerAvailable,
                $marketplaceEnabled,
                $marketplaceFeatured,
                $isActive,
                $showInUserWizard,
                $showInDeveloperWizard,
                $websiteTypeIds
            ) {

                DB::table('theme_packages')
                    ->where(
                        'id',
                        $record->id
                    )
                    ->update([
                        'name' =>
                            $data['name'],

                        'publisher_name' =>
                            $data['publisher_name'],

                        'publisher_type' =>
                            $data['publisher_type'],

                        'saas_available' =>
                            $saasAvailable,

                        'saas_price' =>
                            $data['saas_price']
                            ?? null,

                        'saas_billing_period' =>
                            $data['saas_billing_period']
                            ?? null,

                        'saas_currency' =>
                            strtoupper(
                                $data['saas_currency']
                                ?? 'NGN'
                            ),

                        'saas_billing_interval' =>
                            $data['saas_billing_interval']
                            ?? null,

                        'off_server_available' =>
                            $offServerAvailable,

                        'off_server_price' =>
                            $data['off_server_price']
                            ?? null,

                        'off_server_currency' =>
                            strtoupper(
                                $data['off_server_currency']
                                ?? 'NGN'
                            ),

                        'marketplace_enabled' =>
                            $marketplaceEnabled,

                        'show_in_user_wizard' =>
                            $showInUserWizard,

                        'show_in_developer_wizard' =>
                            $showInDeveloperWizard,

                        'marketplace_featured' =>
                            $marketplaceFeatured,

                        'marketplace_category' =>
                            $data['marketplace_category']
                            ?? null,

                        'commission_rate' =>
                            $data['commission_rate']
                            ?? 0,

                        'release_notes' =>
                            $data['release_notes']
                            ?? null,

                        'is_active' =>
                            $isActive,

                        'updated_at' =>
                            now(),
                    ]);


                /*
                 * ESUBIZ_THEME_WEBSITE_TYPE_ASSIGNMENT_V1
                 *
                 * Themes and Modules are Website-Type scoped.
                 * Add-ons remain universal and are not assigned here.
                 */
                DB::table('theme_package_website_type')
                    ->where(
                        'theme_package_id',
                        $record->id
                    )
                    ->delete();

                if ($websiteTypeIds) {

                    $now = now();

                    DB::table(
                        'theme_package_website_type'
                    )->insert(
                        collect($websiteTypeIds)
                            ->map(
                                fn ($websiteTypeId) => [
                                    'theme_package_id' =>
                                        $record->id,

                                    'website_type_id' =>
                                        $websiteTypeId,

                                    'created_at' =>
                                        $now,

                                    'updated_at' =>
                                        $now,
                                ]
                            )
                            ->all()
                    );
                }


                /*
                 * Keep the canonical catalog audience synchronized.
                 */
                if (
                    $record->catalog_product_id
                ) {

                    $audience =
                        match (true) {

                            $saasAvailable
                            && $offServerAvailable =>
                                'both',

                            $saasAvailable =>
                                'saas',

                            $offServerAvailable =>
                                'developer',

                            default =>
                                'both',
                        };


                    DB::table(
                        'catalog_products'
                    )
                        ->where(
                            'id',
                            $record->catalog_product_id
                        )
                        ->update([
                            'name' =>
                                $data['name'],

                            'audience' =>
                                $audience,

                            'is_active' =>
                                $isActive,

                            'is_public' =>
                                $marketplaceEnabled,

                            'updated_at' =>
                                now(),
                        ]);
                }
            }
        );


        return back()->with(
            'success',
            'Theme configuration updated successfully.'
        );
    }


    /**
     * Upload a NEW VERSION of a theme package.
     *
     * Published versions are never overwritten.
     */
    public function createVersion(
        Request $request,
        int $theme
    ) {
        $parent = DB::table('theme_packages')
            ->where('id', $theme)
            ->whereNull('deleted_at')
            ->first();

        abort_unless(
            $parent,
            404,
            'Theme package not found.'
        );


        $data = $request->validate([
            'version' => [
                'required',
                'string',
                'max:50',
                'regex:/^[0-9]+\.[0-9]+\.[0-9]+$/',
            ],

            'package' => [
                'required',
                'file',
                'mimes:zip',
                'max:51200',
            ],

            'release_notes' => [
                'nullable',
                'string',
            ],
        ]);


        $exists = DB::table(
            'theme_packages'
        )
            ->where(
                'slug',
                $parent->slug
            )
            ->where(
                'version',
                $data['version']
            )
            ->whereNull(
                'deleted_at'
            )
            ->exists();


        abort_if(
            $exists,
            422,
            'That theme version already exists.'
        );


        $file =
            $request->file(
                'package'
            );


        $directory =
            'esubiz-products/themes/'
            . $parent->slug
            . '/'
            . $data['version'];


        $filename =
            $parent->slug
            . '-v'
            . $data['version']
            . '.zip';


        $storedPath =
            $file->storeAs(
                $directory,
                $filename,
                'local'
            );


        abort_unless(
            $storedPath,
            500,
            'Theme package could not be stored.'
        );


        $absolutePath =
            Storage::disk('local')
                ->path(
                    $storedPath
                );


        try {

            $manifest =
                $this
                    ->themePackageService
                    ->validatePackage(
                        $absolutePath
                    );


            abort_unless(
                data_get(
                    $manifest,
                    'theme.slug'
                )
                === $parent->slug,
                422,
                'Package slug does not match this theme.'
            );


            abort_unless(
                data_get(
                    $manifest,
                    'theme.version'
                )
                === $data['version'],
                422,
                'Manifest version does not match the new release version.'
            );


            $checksum =
                hash_file(
                    'sha256',
                    $absolutePath
                );


            DB::transaction(
                function () use (
                    $parent,
                    $data,
                    $storedPath,
                    $file,
                    $checksum,
                    $manifest
                ) {

                    DB::table(
                        'theme_packages'
                    )
                        ->where(
                            'slug',
                            $parent->slug
                        )
                        ->update([
                            'is_current' =>
                                false,

                            'updated_at' =>
                                now(),
                        ]);


                    DB::table(
                        'theme_packages'
                    )
                        ->insert([
                            'catalog_product_id' =>
                                $parent
                                    ->catalog_product_id,

                            'vendor_id' =>
                                $parent
                                    ->vendor_id,

                            'workspace_id' =>
                                $parent
                                    ->workspace_id,

                            'uuid' =>
                                (string)
                                Str::uuid(),

                            'slug' =>
                                $parent->slug,

                            'name' =>
                                data_get(
                                    $manifest,
                                    'theme.name',
                                    $parent->name
                                ),

                            'version' =>
                                $data['version'],

                            'publisher_name' =>
                                data_get(
                                    $manifest,
                                    'publisher.display_name',
                                    $parent->publisher_name
                                ),

                            'publisher_type' =>
                                data_get(
                                    $manifest,
                                    'publisher.type',
                                    $parent->publisher_type
                                ),

                            'package_path' =>
                                $storedPath,

                            'manifest_path' =>
                                $parent
                                    ->manifest_path,

                            'preview_path' =>
                                $parent
                                    ->preview_path,

                            'package_bytes' =>
                                $file->getSize(),

                            'checksum_sha256' =>
                                $checksum,

                            'saas_available' =>
                                $parent
                                    ->saas_available,

                            'saas_price' =>
                                $parent
                                    ->saas_price,

                            'saas_currency' =>
                                $parent
                                    ->saas_currency,

                            'saas_billing_interval' =>
                                $parent
                                    ->saas_billing_interval,

                            'off_server_available' =>
                                $parent
                                    ->off_server_available,

                            'off_server_price' =>
                                $parent
                                    ->off_server_price,

                            'off_server_currency' =>
                                $parent
                                    ->off_server_currency,

                            'website_types' =>
                                $parent
                                    ->website_types,

                            'minimum_core_version' =>
                                data_get(
                                    $manifest,
                                    'compatibility.core_minimum',
                                    $parent
                                        ->minimum_core_version
                                ),

                            'marketplace_ready' =>
                                true,

                            'marketplace_enabled' =>
                                $parent
                                    ->marketplace_enabled,

                            'marketplace_featured' =>
                                $parent
                                    ->marketplace_featured,

                            'marketplace_category' =>
                                $parent
                                    ->marketplace_category,

                            'commission_rate' =>
                                $parent
                                    ->commission_rate,

                            'release_status' =>
                                'draft',

                            'release_notes' =>
                                $data['release_notes']
                                ?? null,

                            'parent_package_id' =>
                                $parent->id,

                            'is_current' =>
                                true,

                            'is_active' =>
                                true,

                            'metadata' =>
                                json_encode([
                                    'validated' =>
                                        true,

                                    'schema' =>
                                        $manifest['schema']
                                        ?? null,

                                    'schema_version' =>
                                        $manifest[
                                            'schema_version'
                                        ]
                                        ?? null,
                                ]),

                            'created_at' =>
                                now(),

                            'updated_at' =>
                                now(),
                        ]);
                }
            );


        } catch (\Throwable $e) {

            Storage::disk(
                'local'
            )->delete(
                $storedPath
            );

            throw $e;
        }


        return back()->with(
            'success',
            'New theme version created and validated successfully.'
        );
    }


    /**
     * Publish a validated release.
     */
    public function publish(
        int $theme
    ) {
        $record = DB::table(
            'theme_packages'
        )
            ->where(
                'id',
                $theme
            )
            ->whereNull(
                'deleted_at'
            )
            ->first();

        abort_unless(
            $record,
            404,
            'Theme package not found.'
        );


        abort_unless(
            $record->marketplace_ready,
            422,
            'Theme must pass Esubiz validation before publishing.'
        );


        DB::table(
            'theme_packages'
        )
            ->where(
                'id',
                $theme
            )
            ->update([
                'release_status' =>
                    'published',

                'published_at' =>
                    now(),

                'updated_at' =>
                    now(),
            ]);


        return back()->with(
            'success',
            'Theme release published successfully.'
        );
    }
}
