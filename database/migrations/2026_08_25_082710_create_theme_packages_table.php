<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('theme_packages')) {
            return;
        }

        Schema::create(
            'theme_packages',
            function (Blueprint $table) {

                $table->id();

                /*
                 * Canonical Esubiz catalog product.
                 */
                $table
                    ->foreignId(
                        'catalog_product_id'
                    )
                    ->nullable()
                    ->constrained(
                        'catalog_products'
                    )
                    ->nullOnDelete();

                /*
                 * Marketplace ownership.
                 *
                 * Esubiz-owned themes may have no developer/workspace.
                 * Developer themes can populate these fields.
                 */
                $table
                    ->unsignedBigInteger(
                        'vendor_id'
                    )
                    ->nullable()
                    ->index();

                $table
                    ->unsignedBigInteger(
                        'workspace_id'
                    )
                    ->nullable()
                    ->index();

                /*
                 * Theme identity.
                 */
                $table
                    ->uuid('uuid')
                    ->unique();

                $table
                    ->string('slug')
                    ->index();

                $table
                    ->string('name');

                $table
                    ->string('version', 50);

                /*
                 * Publisher identity shown in theme metadata.
                 *
                 * Example:
                 * Esuvex Limited
                 * Developer nickname
                 * Developer company
                 */
                $table
                    ->string(
                        'publisher_name'
                    );

                $table
                    ->string(
                        'publisher_type',
                        30
                    )
                    ->default('company');

                /*
                 * Protected package storage.
                 */
                $table
                    ->string(
                        'package_path'
                    );

                $table
                    ->string(
                        'manifest_path'
                    );

                $table
                    ->string(
                        'preview_path'
                    )
                    ->nullable();

                $table
                    ->unsignedBigInteger(
                        'package_bytes'
                    )
                    ->default(0);

                $table
                    ->string(
                        'checksum_sha256',
                        64
                    )
                    ->nullable();

                /*
                 * Deployment availability.
                 */
                $table
                    ->boolean(
                        'saas_available'
                    )
                    ->default(true)
                    ->index();

                $table
                    ->boolean(
                        'off_server_available'
                    )
                    ->default(false)
                    ->index();

                /*
                 * Theme compatibility.
                 */
                $table
                    ->json(
                        'website_types'
                    )
                    ->nullable();

                $table
                    ->string(
                        'minimum_core_version',
                        50
                    )
                    ->nullable();

                /*
                 * Marketplace lifecycle.
                 */
                $table
                    ->boolean(
                        'marketplace_ready'
                    )
                    ->default(false);

                $table
                    ->boolean(
                        'is_active'
                    )
                    ->default(true)
                    ->index();

                /*
                 * Internal package provenance.
                 *
                 * This is NOT customer-facing branding.
                 * It can later record trusted generation /
                 * validation information.
                 */
                $table
                    ->json(
                        'metadata'
                    )
                    ->nullable();

                $table->timestamps();
                $table->softDeletes();

                $table->unique(
                    [
                        'slug',
                        'version'
                    ],
                    'uq_theme_package_version'
                );
            }
        );
    }

    public function down(): void
    {
        Schema::dropIfExists(
            'theme_packages'
        );
    }
};
