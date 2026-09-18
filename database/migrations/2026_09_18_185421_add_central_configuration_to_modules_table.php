<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('modules', function (Blueprint $table) {
            /*
             * ESUBIZ_MODULE_CENTRAL_CONFIGURATION_V1
             *
             * Central Admin owns these Module deployment/integration rules.
             *
             * Commercial pricing remains in currency_prices.
             * Preview/package assets remain in module_assets/module_versions.
             */

            $table->string('sidebar_menu_name')
                ->nullable()
                ->after('package_name');

            $table->boolean('saas_wizard_visible')
                ->default(true)
                ->after('sidebar_menu_name');

            $table->boolean('off_server_wizard_visible')
                ->default(true)
                ->after('saas_wizard_visible');

            $table->json('compatible_website_types')
                ->nullable()
                ->after('off_server_wizard_visible');

            $table->json('compatible_themes')
                ->nullable()
                ->after('compatible_website_types');

            $table->json('core_function_configuration')
                ->nullable()
                ->after('compatible_themes');

            $table->json('marketplace_metadata')
                ->nullable()
                ->after('core_function_configuration');
        });
    }

    public function down(): void
    {
        Schema::table('modules', function (Blueprint $table) {
            $table->dropColumn([
                'sidebar_menu_name',
                'saas_wizard_visible',
                'off_server_wizard_visible',
                'compatible_website_types',
                'compatible_themes',
                'core_function_configuration',
                'marketplace_metadata',
            ]);
        });
    }
};
