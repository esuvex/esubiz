<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('core_addons', function (Blueprint $table) {
            $table
                ->string('preview_image', 500)
                ->nullable()
                ->after('description');

            $table
                ->json('feature_details')
                ->nullable()
                ->after('preview_image');
        });

        Schema::table('core_addon_bundles', function (Blueprint $table) {
            $table
                ->string('preview_image', 500)
                ->nullable()
                ->after('description');

            $table
                ->json('feature_details')
                ->nullable()
                ->after('preview_image');
        });
    }

    public function down(): void
    {
        Schema::table('core_addons', function (Blueprint $table) {
            $table->dropColumn([
                'preview_image',
                'feature_details',
            ]);
        });

        Schema::table('core_addon_bundles', function (Blueprint $table) {
            $table->dropColumn([
                'preview_image',
                'feature_details',
            ]);
        });
    }
};
