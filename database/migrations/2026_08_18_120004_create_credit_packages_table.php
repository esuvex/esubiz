<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('credit_packages')) return;

        Schema::create('credit_packages', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('catalog_product_id')->index();
            $table->string('credit_type', 30)->index();
            $table->string('name');
            $table->unsignedBigInteger('credit_quantity');
            $table->decimal('price', 15, 2);
            $table->string('currency', 10)->default('NGN');
            $table->boolean('is_active')->default(true)->index();
            $table->unsignedInteger('sort_order')->default(0);
            $table->unsignedInteger('expiry_days')->nullable();
            $table->text('description')->nullable();
            $table->timestamps();

            $table->unique(
                ['catalog_product_id', 'credit_type'],
                'credit_package_product_type_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('credit_packages');
    }
};
