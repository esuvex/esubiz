<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('website_types', function (Blueprint $table) {
            /*
             * ESUBIZ_WEBSITE_TYPE_WIZARD_SETTINGS_V1
             *
             * One Website Type controls both:
             * - User Mode / SaaS wizard
             * - Developer Mode / off-server wizard
             *
             * Product names and prices remain authoritative in their
             * marketplace/catalog records. These fields only control
             * wizard availability and presentation/configuration.
             */
            $table->boolean('show_in_user_wizard')
                ->default(true)
                ->after('is_active');

            $table->boolean('show_in_developer_wizard')
                ->default(true)
                ->after('show_in_user_wizard');

            $table->json('wizard_settings')
                ->nullable()
                ->after('show_in_developer_wizard');
        });
    }

    public function down(): void
    {
        Schema::table('website_types', function (Blueprint $table) {
            $table->dropColumn([
                'show_in_user_wizard',
                'show_in_developer_wizard',
                'wizard_settings',
            ]);
        });
    }
};
