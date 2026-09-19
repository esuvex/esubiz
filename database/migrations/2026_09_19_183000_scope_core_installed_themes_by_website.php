<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('core_installed_themes')) {
            return;
        }

        if (!Schema::hasColumn('core_installed_themes', 'website_id')) {
            Schema::table('core_installed_themes', function (Blueprint $table) {
                $table->unsignedBigInteger('website_id')
                    ->nullable()
                    ->after('id');
            });
        }

        /*
         * Existing development Theme installation belongs to
         * the Esuvex test website.
         */
        DB::table('core_installed_themes')
            ->whereNull('website_id')
            ->update([
                'website_id' => 61,
                'updated_at' => now(),
            ]);

        Schema::table('core_installed_themes', function (Blueprint $table) {
            $table->dropUnique(
                'core_installed_themes_slug_version_unique'
            );

            $table->dropIndex(
                'core_installed_themes_active_index'
            );

            $table->unique(
                [
                    'website_id',
                    'theme_slug',
                    'theme_version',
                ],
                'core_site_theme_uq'
            );

            $table->index(
                [
                    'website_id',
                    'is_active',
                ],
                'core_site_theme_act_idx'
            );

            $table->index(
                'website_id',
                'core_theme_site_idx'
            );
        });
    }

    public function down(): void
    {
        if (
            !Schema::hasTable('core_installed_themes')
            || !Schema::hasColumn('core_installed_themes', 'website_id')
        ) {
            return;
        }

        Schema::table('core_installed_themes', function (Blueprint $table) {
            $table->dropUnique('core_site_theme_uq');
            $table->dropIndex('core_site_theme_act_idx');
            $table->dropIndex('core_theme_site_idx');

            $table->unique(
                [
                    'theme_slug',
                    'theme_version',
                ],
                'core_theme_slug_ver_uq'
            );

            $table->index(
                'is_active',
                'core_theme_active_idx'
            );

            $table->dropColumn('website_id');
        });
    }
};
