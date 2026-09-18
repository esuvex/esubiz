<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('developer_product_type_policies', function (Blueprint $table) {
            $table->decimal('saas_max_price', 18, 2)
                ->nullable()
                ->after('developer_publish_enabled');

            $table->decimal('off_server_max_price', 18, 2)
                ->nullable()
                ->after('saas_max_price');
        });
    }

    public function down(): void
    {
        Schema::table('developer_product_type_policies', function (Blueprint $table) {
            $table->dropColumn([
                'saas_max_price',
                'off_server_max_price',
            ]);
        });
    }
};
