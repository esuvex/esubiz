<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('website_types', function (Blueprint $table) {
            $table->string('package_key')->nullable()->after('slug');
            $table->string('package_version')->nullable()->after('package_key');

            $table->index(
                ['package_key', 'package_version'],
                'website_types_package_lookup_index'
            );
        });
    }

    public function down(): void
    {
        Schema::table('website_types', function (Blueprint $table) {
            $table->dropIndex('website_types_package_lookup_index');
            $table->dropColumn([
                'package_key',
                'package_version',
            ]);
        });
    }
};
