<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        /*
         * ESUBIZ_CORE_RESOURCE_SETTINGS_TABLE_V1
         *
         * One central configuration per managed Core resource.
         *
         * Core remains authoritative for default allocations.
         * Add-ons remain authoritative for purchased allocations.
         * Sales triggers remain individual to each Add-on.
         */
        Schema::create('core_resource_settings', function (Blueprint $table) {
            $table->id();

            /*
             * Canonical Core capability/resource key:
             * storage, bandwidth, crm.clients, etc.
             */
            $table->string('resource_key', 150)->unique();

            /*
             * Resource card becomes visible on the website dashboard
             * when usage reaches this independently configured level.
             */
            $table->decimal(
                'dashboard_threshold_percentage',
                5,
                2
            )->default(50);

            /*
             * Resource availability is independently controlled
             * for SaaS and off-server websites.
             */
            $table->boolean('saas_visible')->default(false);
            $table->boolean('off_server_visible')->default(false);

            $table->boolean('is_active')->default(true);

            $table->json('metadata')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('core_resource_settings');
    }
};
