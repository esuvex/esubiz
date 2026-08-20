<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('revenue_events', function (Blueprint $table) {
            $table->string('revenue_owner')
                ->default('merchant')
                ->after('currency');

            $table->boolean('is_platform_revenue')
                ->default(false)
                ->after('revenue_owner');

            $table->boolean('is_esubiz_commissionable')
                ->default(false)
                ->after('is_platform_revenue');

            $table->string('hosting_scope')
                ->nullable()
                ->after('is_esubiz_commissionable');

            $table->string('commission_plan')
                ->nullable()
                ->after('hosting_scope');

            $table->index([
                'is_platform_revenue',
                'revenue_owner',
            ]);

            $table->index([
                'is_esubiz_commissionable',
            ]);
        });
    }

    public function down(): void
    {
        Schema::table('revenue_events', function (Blueprint $table) {
            $table->dropIndex([
                'revenue_events_is_platform_revenue_revenue_owner_index',
            ]);

            $table->dropIndex([
                'revenue_events_is_esubiz_commissionable_index',
            ]);

            $table->dropColumn([
                'revenue_owner',
                'is_platform_revenue',
                'is_esubiz_commissionable',
                'hosting_scope',
                'commission_plan',
            ]);
        });
    }
};
