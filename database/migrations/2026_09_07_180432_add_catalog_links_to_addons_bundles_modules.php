<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /*
     * ESUBIZ_PRODUCT_CATALOG_LINKS_V1
     *
     * Gives Add-ons, Add-on Bundles and Modules the same canonical
     * catalog_products relationship already used by Theme Packages.
     *
     * Native product tables remain the product-specific authority.
     * catalog_products provides the shared Marketplace / Website Type
     * product identity used by deployment profiles and fulfilment.
     *
     * All manually named database identifiers are <= 20 characters.
     */

    public function up(): void
    {
        Schema::table('addon_products', function (Blueprint $table) {
            $table->unsignedBigInteger('catalog_product_id')
                ->nullable()
                ->after('id');

            $table->foreign(
                'catalog_product_id',
                'ap_cat_fk'
            )
                ->references('id')
                ->on('catalog_products')
                ->nullOnDelete();

            $table->unique(
                'catalog_product_id',
                'ap_cat_uq'
            );
        });

        Schema::table('core_addon_bundles', function (Blueprint $table) {
            $table->unsignedBigInteger('catalog_product_id')
                ->nullable()
                ->after('id');

            $table->foreign(
                'catalog_product_id',
                'cab_cat_fk'
            )
                ->references('id')
                ->on('catalog_products')
                ->nullOnDelete();

            $table->unique(
                'catalog_product_id',
                'cab_cat_uq'
            );
        });

        Schema::table('modules', function (Blueprint $table) {
            $table->unsignedBigInteger('catalog_product_id')
                ->nullable()
                ->after('id');

            $table->foreign(
                'catalog_product_id',
                'mod_cat_fk'
            )
                ->references('id')
                ->on('catalog_products')
                ->nullOnDelete();

            $table->unique(
                'catalog_product_id',
                'mod_cat_uq'
            );
        });
    }

    public function down(): void
    {
        Schema::table('modules', function (Blueprint $table) {
            $table->dropForeign('mod_cat_fk');
            $table->dropUnique('mod_cat_uq');
            $table->dropColumn('catalog_product_id');
        });

        Schema::table('core_addon_bundles', function (Blueprint $table) {
            $table->dropForeign('cab_cat_fk');
            $table->dropUnique('cab_cat_uq');
            $table->dropColumn('catalog_product_id');
        });

        Schema::table('addon_products', function (Blueprint $table) {
            $table->dropForeign('ap_cat_fk');
            $table->dropUnique('ap_cat_uq');
            $table->dropColumn('catalog_product_id');
        });
    }
};
