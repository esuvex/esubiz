<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        /*
         * ------------------------------------------------------------
         * INDIVIDUAL ADD-ON ZIP PACKAGES
         * ------------------------------------------------------------
         *
         * core_addons remains the commercial/entitlement definition.
         *
         * This table stores versioned, protected installable ZIP
         * packages belonging to those add-ons.
         */
        Schema::create(
            'core_addon_packages',
            function (Blueprint $table) {
                $table->id();

                $table->foreignId('addon_id')
                    ->constrained('core_addons')
                    ->cascadeOnDelete();

                $table->uuid('uuid')->unique();

                $table->string('version', 64);

                $table->string('package_path');

                $table->string(
                    'checksum_sha256',
                    64
                );

                $table->unsignedBigInteger(
                    'package_bytes'
                )->default(0);

                $table->string(
                    'manifest_schema',
                    100
                )->default(
                    'esubiz-addon-package'
                );

                $table->string(
                    'manifest_schema_version',
                    32
                )->default('1.0');

                $table->string(
                    'minimum_core_version',
                    64
                )->nullable();

                $table->json(
                    'deployment_compatibility'
                )->nullable();

                $table->json(
                    'website_types'
                )->nullable();

                $table->json(
                    'metadata'
                )->nullable();

                $table->boolean(
                    'is_current'
                )->default(false);

                $table->boolean(
                    'is_active'
                )->default(true);

                $table->timestamp(
                    'published_at'
                )->nullable();

                $table->timestamps();
                $table->softDeletes();

                $table->unique(
                    [
                        'addon_id',
                        'version',
                    ],
                    'cap_addon_version_uq'
                );

                $table->index(
                    [
                        'addon_id',
                        'is_current',
                        'is_active',
                    ],
                    'cap_addon_current_idx'
                );
            }
        );


        /*
         * ------------------------------------------------------------
         * BUNDLE OUTER ZIP PACKAGES
         * ------------------------------------------------------------
         *
         * A bundle ZIP contains:
         *
         * bundle.json
         * addons/
         *   addon-one.zip
         *   addon-two.zip
         *   ...
         *
         * The individual inner ZIPs are NOT flattened into the bundle.
         * Each remains an independently valid Esubiz add-on package.
         */
        Schema::create(
            'core_addon_bundle_packages',
            function (Blueprint $table) {
                $table->id();

                $table->foreignId('bundle_id')
                    ->constrained(
                        'core_addon_bundles'
                    )
                    ->cascadeOnDelete();

                $table->uuid('uuid')->unique();

                $table->string('version', 64);

                $table->string('package_path');

                $table->string(
                    'checksum_sha256',
                    64
                );

                $table->unsignedBigInteger(
                    'package_bytes'
                )->default(0);

                $table->string(
                    'manifest_schema',
                    100
                )->default(
                    'esubiz-addon-bundle-package'
                );

                $table->string(
                    'manifest_schema_version',
                    32
                )->default('1.0');

                $table->string(
                    'minimum_core_version',
                    64
                )->nullable();

                $table->json(
                    'deployment_compatibility'
                )->nullable();

                $table->json(
                    'website_types'
                )->nullable();

                $table->json(
                    'metadata'
                )->nullable();

                $table->boolean(
                    'is_current'
                )->default(false);

                $table->boolean(
                    'is_active'
                )->default(true);

                $table->timestamp(
                    'published_at'
                )->nullable();

                $table->timestamps();
                $table->softDeletes();

                $table->unique(
                    [
                        'bundle_id',
                        'version',
                    ],
                    'cabp_bundle_version_uq'
                );

                $table->index(
                    [
                        'bundle_id',
                        'is_current',
                        'is_active',
                    ],
                    'cabp_bundle_current_idx'
                );
            }
        );


        /*
         * ------------------------------------------------------------
         * BUNDLE PACKAGE CONTENT SNAPSHOT
         * ------------------------------------------------------------
         *
         * core_addon_bundle_items describes the commercial bundle.
         *
         * This table records EXACTLY which add-on package/version ZIP
         * was physically embedded in a particular bundle release.
         *
         * This prevents a future add-on update from silently changing
         * the contents of an already-published bundle ZIP.
         */
        Schema::create(
            'core_addon_bundle_package_items',
            function (Blueprint $table) {
                $table->id();

                $table->foreignId(
                    'bundle_package_id'
                )
                    ->constrained(
                        'core_addon_bundle_packages'
                    )
                    ->cascadeOnDelete();

                $table->foreignId(
                    'addon_id'
                )
                    ->constrained(
                        'core_addons'
                    )
                    ->cascadeOnDelete();

                $table->foreignId(
                    'addon_package_id'
                )
                    ->constrained(
                        'core_addon_packages'
                    )
                    ->cascadeOnDelete();

                /*
                 * Path of the nested ZIP inside
                 * the outer bundle package.
                 */
                $table->string(
                    'embedded_path'
                );

                /*
                 * Snapshot checksum allows the bundle validator
                 * to prove the nested ZIP has not been replaced.
                 */
                $table->string(
                    'checksum_sha256',
                    64
                );

                $table->unsignedInteger(
                    'sort_order'
                )->default(0);

                $table->json(
                    'metadata'
                )->nullable();

                $table->timestamps();

                $table->unique(
                    [
                        'bundle_package_id',
                        'addon_id',
                    ],
                    'cabpi_bundle_addon_uq'
                );

                $table->index(
                    'addon_package_id',
                    'cabpi_addon_package_idx'
                );
            }
        );
    }


    public function down(): void
    {
        Schema::dropIfExists(
            'core_addon_bundle_package_items'
        );

        Schema::dropIfExists(
            'core_addon_bundle_packages'
        );

        Schema::dropIfExists(
            'core_addon_packages'
        );
    }
};
