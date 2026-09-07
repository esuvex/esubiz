<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * ESUBIZ_WEBSITE_TYPE_DEPLOYMENT_COMPONENTS_SCHEMA_V1
 *
 * Product composition for a Website Type deployment profile.
 *
 * Admin can independently assign the products included in:
 *
 * - SaaS Website Type provisioning
 * - Off-server Website Type packaging
 *
 * Supported component families:
 *
 * - theme
 * - module
 * - addon
 * - bundle
 *
 * No Website Type-specific logic is stored here. Future Website Types
 * automatically use the same composition structure.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('website_type_deployment_components', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('website_type_deployment_profile_id');

            /*
             * Canonical Marketplace/catalog identity of the assigned product.
             */
            $table->unsignedBigInteger('catalog_product_id');

            /*
             * Explicit component type is retained for fast profile resolution
             * and to validate that Admin cannot assign an incompatible product.
             */
            $table->enum('component_type', [
                'theme',
                'module',
                'addon',
                'bundle',
            ]);

            /*
             * Required components are automatically provisioned/packaged
             * as part of the Website Type and are not wizard choices.
             *
             * Themes are still assigned here; the profile separately records
             * which assigned theme is the default.
             */
            $table->boolean('is_required')->default(true);

            /*
             * Stable ordering for deterministic provisioning and package builds.
             */
            $table->unsignedInteger('sort_order')->default(0);

            /*
             * Reserved for component-specific Website Type configuration,
             * not product identity or licensing.
             */
            $table->json('configuration')->nullable();

            $table->timestamps();

            /*
             * The same canonical product may only be assigned once to a
             * particular deployment profile.
             */
            $table->unique(
                [
                    'website_type_deployment_profile_id',
                    'catalog_product_id',
                ],
                'wt_deployment_components_product_unique'
            );

            $table->index(
                [
                    'website_type_deployment_profile_id',
                    'component_type',
                    'sort_order',
                ],
                'wt_deployment_components_lookup_idx'
            );

            $table->foreign(
                'website_type_deployment_profile_id',
                'wt_comp_prof_fk'
            )
                ->references('id')
                ->on('website_type_deployment_profiles')
                ->cascadeOnDelete();

            $table->foreign(
                'catalog_product_id',
                'wt_comp_cat_fk'
            )
                ->references('id')
                ->on('catalog_products')
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('website_type_deployment_components');
    }
};
