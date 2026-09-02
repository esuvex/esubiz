<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('core_addon_sales_triggers', function (Blueprint $table) {
            /*
             * ESUBIZ_GENERIC_ADDON_TRIGGER_REPEAT_POLICY_V1
             *
             * once_until_purchased:
             *   Stop recommending the Add-on once the website owns it.
             *
             * resource_retrigger:
             *   Resource Add-ons may be recommended again when their configured
             *   resource threshold/limit condition becomes true after purchase.
             *
             * Trigger is_active remains the Admin master override.
             */
            $table->string('repeat_policy', 40)
                ->default('once_until_purchased')
                ->after('condition_type')
                ->index();
        });
    }

    public function down(): void
    {
        Schema::table('core_addon_sales_triggers', function (Blueprint $table) {
            $table->dropIndex(['repeat_policy']);
            $table->dropColumn('repeat_policy');
        });
    }
};
