<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('ecommerce_categories')) {
            Schema::create('ecommerce_categories', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('website_id');
                $table->string('name');
                $table->string('slug');
                $table->text('description')->nullable();
                $table->string('image')->nullable();
                $table->unsignedInteger('sort_order')->default(0);
                $table->boolean('is_active')->default(true);
                $table->timestamps();

                $table->unique(
                    ['website_id', 'slug'],
                    'ecom_site_category_uq'
                );

                $table->index(
                    ['website_id', 'is_active'],
                    'ecom_site_cat_act_idx'
                );
            });
        }

        if (!Schema::hasTable('ecommerce_products')) {
            Schema::create('ecommerce_products', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('website_id');
                $table->unsignedBigInteger('category_id')->nullable();

                $table->string('name');
                $table->string('slug');
                $table->string('sku')->nullable();

                $table->text('short_description')->nullable();
                $table->longText('description')->nullable();

                $table->decimal('price', 15, 2)->default(0);
                $table->decimal('compare_price', 15, 2)->nullable();

                $table->string('currency', 10)->nullable();

                $table->integer('stock_quantity')->default(0);
                $table->boolean('track_inventory')->default(true);

                $table->string('product_type')->default('physical');
                $table->string('status')->default('draft');

                $table->boolean('is_featured')->default(false);

                $table->string('image')->nullable();
                $table->json('gallery')->nullable();
                $table->json('metadata')->nullable();

                $table->timestamps();

                $table->unique(
                    ['website_id', 'slug'],
                    'ecom_site_product_uq'
                );

                $table->unique(
                    ['website_id', 'sku'],
                    'ecom_site_sku_uq'
                );

                $table->index(
                    ['website_id', 'status'],
                    'ecom_site_prod_stat_idx'
                );

                $table->index(
                    ['website_id', 'is_featured'],
                    'ecom_site_feature_idx'
                );

                $table->index(
                    ['website_id', 'category_id'],
                    'ecom_site_category_idx'
                );
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('ecommerce_products');
        Schema::dropIfExists('ecommerce_categories');
    }
};
