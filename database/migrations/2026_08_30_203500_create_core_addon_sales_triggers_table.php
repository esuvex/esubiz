<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        /*
         * ESUBIZ_GENERIC_ADDON_SALES_TRIGGERS_V1
         *
         * Generic plug-and-play Add-on recommendation engine.
         *
         * Examples:
         *
         * Bandwidth:
         *   location  = dashboard.resources
         *   condition = resource_threshold
         *
         * Page Builder Pro:
         *   location  = page_builder.widgets
         *   condition = feature_locked / always
         *
         * 360 Panorama:
         *   location  = media.360_panorama
         *   condition = feature_locked
         *
         * One Add-on may have multiple sales triggers.
         * SaaS/off-server visibility is configured per trigger.
         */
        Schema::create('core_addon_sales_triggers', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('addon_id');

            /*
             * Plug-and-play slot exposed by Core or a Module.
             *
             * Examples:
             * dashboard.resources
             * page_builder.widgets
             * form_builder
             * media.360_panorama
             * email.accounts
             */
            $table->string('location_key', 150);

            /*
             * Generic condition:
             * always
             * resource_threshold
             * limit_reached
             * feature_locked
             */
            $table->string('condition_type', 50)->default('always');

            /*
             * Optional capability/resource involved in the condition.
             * Not required for ordinary feature upgrade triggers.
             */
            $table->string('resource_key', 150)->nullable();

            /*
             * Used only when condition_type requires a percentage.
             */
            $table->decimal(
                'threshold_percentage',
                5,
                2
            )->nullable();

            /*
             * Each trigger independently controls where it is offered.
             */
            $table->boolean('saas_visible')->default(false);
            $table->boolean('off_server_visible')->default(false);

            /*
             * Admin-configurable recommendation content.
             */
            $table->string('title', 255)->nullable();
            $table->text('message')->nullable();
            $table->string('cta_text', 100)->nullable();

            /*
             * Allows multiple eligible Add-ons/triggers at one slot
             * to be ordered without writing product-specific code.
             */
            $table->integer('priority')->default(100);

            $table->boolean('is_active')->default(true);

            /*
             * Future condition options can be stored here without
             * requiring product-specific schema changes.
             */
            $table->json('condition_config')->nullable();
            $table->json('metadata')->nullable();

            $table->timestamps();

            $table->index(
                ['location_key', 'is_active'],
                'core_sales_trigger_location_active_idx'
            );

            $table->index(
                ['addon_id', 'is_active'],
                'core_sales_trigger_addon_active_idx'
            );

            $table->index(
                ['resource_key', 'condition_type'],
                'core_sales_trigger_resource_condition_idx'
            );

            $table->foreign('addon_id')
                ->references('id')
                ->on('core_addons')
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('core_addon_sales_triggers');
    }
};
