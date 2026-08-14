<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        /*
         * Rename the Capacity system to Add-ons.
         */

        Schema::rename('capacity_types', 'addon_types');
        Schema::rename('capacity_products', 'addon_products');
        Schema::rename('capacity_product_items', 'addon_product_items');
        Schema::rename('plan_capacities', 'plan_addons');
        Schema::rename('workspace_capacities', 'workspace_addons');

        Schema::table('addon_product_items', function ($table) {
            $table->renameColumn('capacity_product_id', 'addon_product_id');
            $table->renameColumn('capacity_type_id', 'addon_type_id');
        });

        Schema::table('plan_addons', function ($table) {
            $table->renameColumn('capacity_type_id', 'addon_type_id');
        });

        Schema::table('workspace_addons', function ($table) {
            $table->renameColumn('capacity_type_id', 'addon_type_id');
        });

        Schema::table('developer_builds', function ($table) {
            $table->renameColumn('capacity_bundle', 'addon_bundle');
            $table->renameColumn('selected_capacity', 'selected_addons');
        });

        DB::statement("UPDATE catalog_products SET product_type = 'addon' WHERE product_type = 'capacity'");
    }

    public function down(): void
    {
        DB::statement("UPDATE catalog_products SET product_type = 'capacity' WHERE product_type = 'addon'");

        Schema::table('developer_builds', function ($table) {
            $table->renameColumn('addon_bundle', 'capacity_bundle');
            $table->renameColumn('selected_addons', 'selected_capacity');
        });

        Schema::table('workspace_addons', function ($table) {
            $table->renameColumn('addon_type_id', 'capacity_type_id');
        });

        Schema::table('plan_addons', function ($table) {
            $table->renameColumn('addon_type_id', 'capacity_type_id');
        });

        Schema::table('addon_product_items', function ($table) {
            $table->renameColumn('addon_product_id', 'capacity_product_id');
            $table->renameColumn('addon_type_id', 'capacity_type_id');
        });

        Schema::rename('workspace_addons', 'workspace_capacities');
        Schema::rename('plan_addons', 'plan_capacities');
        Schema::rename('addon_product_items', 'capacity_product_items');
        Schema::rename('addon_products', 'capacity_products');
        Schema::rename('addon_types', 'capacity_types');
    }
};
