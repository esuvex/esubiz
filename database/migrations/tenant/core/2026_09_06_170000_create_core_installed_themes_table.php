<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /*
     * ESUBIZ_CORE_INSTALLED_THEMES_TABLE_V1
     *
     * Universal Core installed-theme registry.
     *
     * This table belongs to each Core website database.
     * It is NOT Business-specific and it is NOT Marketplace-specific.
     *
     * Marketplace, Theme Hub, installer, activation and checkout
     * should all resolve installed state from this Core registry.
     */

    public function up(): void
    {
        if (Schema::hasTable('core_installed_themes')) {
            return;
        }

        Schema::create(
            'core_installed_themes',
            function (Blueprint $table) {
                $table->id();

                $table->string('theme_slug', 191);
                $table->string('theme_version', 64);

                $table->string('name', 191)->nullable();

                /*
                 * Marketplace identity is optional because Core must
                 * also support themes installed outside Marketplace.
                 */
                $table->unsignedBigInteger(
                    'marketplace_theme_package_id'
                )->nullable();

                $table->uuid(
                    'marketplace_theme_uuid'
                )->nullable();

                /*
                 * Universal package integrity metadata.
                 */
                $table->string(
                    'checksum_sha256',
                    64
                )->nullable();

                /*
                 * Where the installed Theme lives inside this Core.
                 */
                $table->string(
                    'install_path'
                )->nullable();

                $table->string(
                    'preview_path'
                )->nullable();

                /*
                 * Active state is independent from installed state.
                 * Many themes can be installed, one may be active.
                 */
                $table->boolean(
                    'is_active'
                )->default(false);

                $table->timestamp(
                    'installed_at'
                )->nullable();

                $table->timestamp(
                    'activated_at'
                )->nullable();

                $table->json(
                    'metadata'
                )->nullable();

                $table->timestamps();

                $table->unique(
                    [
                        'theme_slug',
                        'theme_version',
                    ],
                    'core_installed_themes_slug_version_unique'
                );

                $table->index(
                    'marketplace_theme_package_id',
                    'core_installed_themes_marketplace_id_index'
                );

                $table->index(
                    'is_active',
                    'core_installed_themes_active_index'
                );
            }
        );
    }


    public function down(): void
    {
        Schema::dropIfExists(
            'core_installed_themes'
        );
    }
};
