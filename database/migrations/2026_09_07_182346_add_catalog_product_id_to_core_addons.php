<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /*
     * ESUBIZ_CORE_ADDON_CATALOG_LINK_V1
     *
     * core_addons is the native authority for Esubiz Add-ons.
     * This adds the canonical catalog_products relationship used
     * by Marketplace, Website Type deployment profiles and fulfilment.
     *
     * All manually named identifiers are <= 20 characters.
     */

    public function up(): void
    {
        Schema::table('core_addons', function (Blueprint $table) {
            $table->unsignedBigInteger('catalog_product_id')
                ->nullable()
                ->after('id');

            $table->foreign(
                'catalog_product_id',
                'ca_cat_fk'
            )
                ->references('id')
                ->on('catalog_products')
                ->nullOnDelete();

            $table->unique(
                'catalog_product_id',
                'ca_cat_uq'
            );
        });
    }

    public function down(): void
    {
        Schema::table('core_addons', function (Blueprint $table) {
            $table->dropForeign('ca_cat_fk');
            $table->dropUnique('ca_cat_uq');
            $table->dropColumn('catalog_product_id');
        });
    }
};
