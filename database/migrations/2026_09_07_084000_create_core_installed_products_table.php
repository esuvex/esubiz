<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * ESUBIZ_CORE_INSTALLED_PRODUCTS_REGISTRY_V1
 *
 * Persistent registry for installable Core products other than Themes.
 *
 * Themes retain their dedicated core_installed_themes registry.
 *
 * This registry is deployment-agnostic and therefore works inside:
 * - Esubiz-hosted SaaS tenant Core;
 * - off-server Esubiz Core.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('core_installed_products')) {
            return;
        }

        Schema::create('core_installed_products', function (Blueprint $table) {
            $table->id();

            $table->string('product_type', 40);
            $table->string('product_slug', 191);
            $table->string('product_version', 100);

            $table->string('name')->nullable();

            $table->string('install_path', 1024)->nullable();

            $table->boolean('is_enabled')
                ->default(false);

            $table->timestamp('installed_at')
                ->nullable();

            $table->timestamp('enabled_at')
                ->nullable();

            $table->unsignedBigInteger('entitlement_id')
                ->nullable();

            $table->unsignedBigInteger('license_id')
                ->nullable();

            $table->unsignedBigInteger('catalog_product_id')
                ->nullable();

            $table->unsignedBigInteger('marketplace_listing_id')
                ->nullable();

            $table->string('checksum_sha256', 64)
                ->nullable();

            $table->json('metadata')
                ->nullable();

            $table->timestamps();

            $table->unique(
                [
                    'product_type',
                    'product_slug',
                    'product_version',
                ],
                'core_installed_product_identity_unique'
            );

            $table->index(
                [
                    'product_type',
                    'product_slug',
                    'is_enabled',
                ],
                'core_installed_product_enabled_index'
            );

            $table->index('entitlement_id');
            $table->index('license_id');
            $table->index('catalog_product_id');
            $table->index('marketplace_listing_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(
            'core_installed_products'
        );
    }
};
