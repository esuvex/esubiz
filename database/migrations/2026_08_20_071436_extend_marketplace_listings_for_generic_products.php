<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('marketplace_listings', function (Blueprint $table) {
            $table->string('product_type')->nullable()->after('catalog_product_id');
            $table->unsignedBigInteger('product_id')->nullable()->after('product_type');
            $table->string('product_key')->nullable()->after('product_id');

            $table->index(['product_type', 'product_id']);
            $table->index('product_key');
        });
    }

    public function down(): void
    {
        Schema::table('marketplace_listings', function (Blueprint $table) {
            $table->dropIndex(['product_type', 'product_id']);
            $table->dropIndex(['product_key']);

            $table->dropColumn([
                'product_type',
                'product_id',
                'product_key',
            ]);
        });
    }
};
