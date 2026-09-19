<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('developer_product_type_policies', function (Blueprint $table) {
            $table->boolean('admin_build_enabled')
                ->default(false)
                ->after('product_type');

            $table->boolean('developer_resell_enabled')
                ->default(false)
                ->after('developer_publish_enabled');
        });
    }

    public function down(): void
    {
        Schema::table('developer_product_type_policies', function (Blueprint $table) {
            $table->dropColumn([
                'admin_build_enabled',
                'developer_resell_enabled',
            ]);
        });
    }
};
