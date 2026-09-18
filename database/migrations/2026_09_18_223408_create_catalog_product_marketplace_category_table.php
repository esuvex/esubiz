<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        /*
         * A failed MySQL CREATE may leave this table behind because
         * DDL is not guaranteed to roll back transactionally.
         */
        Schema::dropIfExists(
            'catalog_product_marketplace_category'
        );

        Schema::create(
            'catalog_product_marketplace_category',
            function (Blueprint $table) {
                $table->id();

                $table->unsignedBigInteger(
                    'catalog_product_id'
                );

                $table->unsignedBigInteger(
                    'marketplace_category_id'
                );

                $table->timestamps();

                $table->foreign(
                    'catalog_product_id',
                    'cp_mc_product_fk'
                )
                    ->references('id')
                    ->on('catalog_products')
                    ->cascadeOnDelete();

                $table->foreign(
                    'marketplace_category_id',
                    'cp_mc_category_fk'
                )
                    ->references('id')
                    ->on('marketplace_categories')
                    ->cascadeOnDelete();

                $table->unique(
                    [
                        'catalog_product_id',
                        'marketplace_category_id',
                    ],
                    'cp_mc_unique'
                );

                $table->index(
                    [
                        'marketplace_category_id',
                        'catalog_product_id',
                    ],
                    'cp_mc_category_product_idx'
                );
            }
        );
    }

    public function down(): void
    {
        Schema::dropIfExists(
            'catalog_product_marketplace_category'
        );
    }
};
