<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Marketplace\ThemePackageService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use RuntimeException;

class ThemeController extends Controller
{
    public function __construct(
        protected ThemePackageService $themePackageService
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
            ->get();

        return view(
            'admin.themes.index',
            compact('themes')
        );
    }


    /**
     * Update commercial/listing configuration.
     *
     * This does NOT rewrite a published package ZIP.
     */
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


        DB::transaction(
            function () use (
                $record,
                $data,
                $saasAvailable,
                $offServerAvailable,
                $marketplaceEnabled,
                $marketplaceFeatured,
                $isActive
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
