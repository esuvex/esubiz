<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('catalog_products')) {
            return;
        }

        Schema::table('catalog_products', function (Blueprint $table) {
            if (!Schema::hasColumn('catalog_products', 'credit_quantity')) {
                $table->unsignedBigInteger('credit_quantity')
                    ->nullable()
                    ->after('product_type');
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('catalog_products')) {
            return;
        }

        Schema::table('catalog_products', function (Blueprint $table) {
            if (Schema::hasColumn('catalog_products', 'credit_quantity')) {
                $table->dropColumn('credit_quantity');
            }
        });
    }
};
