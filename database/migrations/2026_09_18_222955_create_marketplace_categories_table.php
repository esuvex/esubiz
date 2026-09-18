<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('marketplace_categories', function (Blueprint $table) {
            $table->id();

            /*
             * Stable category identity.
             *
             * Products should relate through this ID rather than
             * storing the editable category name.
             */
            $table->uuid('uuid')->unique();

            $table->string('name', 150);
            $table->string('slug', 150)->unique();

            $table->text('description')->nullable();

            /*
             * Empty/null means the category may be used by every
             * Marketplace product type.
             *
             * Otherwise this contains allowed types such as:
             * theme, module, addon, bundle, website_type.
             */
            $table->json('product_types')->nullable();

            $table->boolean('is_active')
                ->default(true)
                ->index();

            $table->unsignedInteger('sort_order')
                ->default(0)
                ->index();

            $table->timestamps();
            $table->softDeletes();

            $table->index(
                ['is_active', 'sort_order'],
                'marketplace_categories_active_sort_index'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('marketplace_categories');
    }
};
