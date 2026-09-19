<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('core_installed_products')) {
            return;
        }

        if (!Schema::hasColumn('core_installed_products', 'website_id')) {
            Schema::table('core_installed_products', function (Blueprint $table) {
                $table->unsignedBigInteger('website_id')
                    ->nullable()
                    ->after('id');
            });
        }

        /*
         * Current development installation:
         * Ecommerce v1.0 belongs only to Esuvex website #61.
         */
        DB::table('core_installed_products')
            ->where('product_type', 'module')
            ->where('product_slug', 'ecommerce')
            ->where('product_version', '1.0.0')
            ->whereNull('website_id')
            ->update([
                'website_id' => 61,
                'updated_at' => now(),
            ]);

        Schema::table('core_installed_products', function (Blueprint $table) {
            $table->dropUnique(
                'core_installed_product_identity_unique'
            );

            $table->dropIndex(
                'core_installed_product_enabled_index'
            );

            $table->unique(
                [
                    'website_id',
                    'product_type',
                    'product_slug',
                    'product_version',
                ],
                'core_site_product_uq'
            );

            $table->index(
                [
                    'website_id',
                    'product_type',
                    'product_slug',
                    'is_enabled',
                ],
                'core_site_enabled_idx'
            );

            $table->index(
                'website_id',
                'core_site_website_idx'
            );
        });
    }

    public function down(): void
    {
        if (
            !Schema::hasTable('core_installed_products')
            || !Schema::hasColumn('core_installed_products', 'website_id')
        ) {
            return;
        }

        Schema::table('core_installed_products', function (Blueprint $table) {
            $table->dropUnique('core_site_product_uq');
            $table->dropIndex('core_site_enabled_idx');
            $table->dropIndex('core_site_website_idx');

            $table->unique(
                [
                    'product_type',
                    'product_slug',
                    'product_version',
                ],
                'core_product_identity_uq'
            );

            $table->index(
                [
                    'product_type',
                    'product_slug',
                    'is_enabled',
                ],
                'core_product_enabled_idx'
            );

            $table->dropColumn('website_id');
        });
    }
};
