<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * ESUBIZ_CENTRAL_DAILY_EMAIL_ALLOWANCE_SCHEMA_V1
     *
     * Central is authoritative for SaaS daily email sending allowance.
     *
     * DirectAdmin may permit a larger server-side volume; Esubiz Central
     * independently controls how much of that capacity each SaaS website
     * may consume.
     */
    public function up(): void
    {
        if (!Schema::hasTable('website_daily_email_usage')) {
            Schema::create('website_daily_email_usage', function (Blueprint $table) {
                $table->id();

                $table->unsignedBigInteger('website_id');

                $table->date('usage_date');

                $table->unsignedBigInteger('sent_count')
                    ->default(0);

                $table->timestamps();

                $table->unique(
                    ['website_id', 'usage_date'],
                    'website_daily_email_usage_site_date_unique'
                );

                $table->index(
                    ['usage_date', 'website_id'],
                    'website_daily_email_usage_date_site_index'
                );
            });
        }

        /*
         * Email is already a registered Core feature.
         *
         * Add daily sends as another limit beneath that same feature so
         * Server Products, plans, Marketplace Add-ons and Admin grants can
         * use the existing Core entitlement architecture.
         */
        if (
            Schema::hasTable('core_features')
            && Schema::hasTable('core_feature_limits')
        ) {
            $emailFeatureId =
                DB::table('core_features')
                    ->where('key', 'email')
                    ->value('id');

            if ($emailFeatureId) {
                $existing =
                    DB::table('core_feature_limits')
                        ->where('core_feature_id', $emailFeatureId)
                        ->where('limit_key', 'email_daily_sends')
                        ->first();

                $values = [
                    'name' => 'Daily Email Sends',
                    'value_type' => 'quantity',
                    'default_value' => 10,
                    'unit' => 'emails/day',
                    'is_unlimited' => false,
                    'is_active' => true,
                    'updated_at' => now(),
                ];

                if ($existing) {
                    DB::table('core_feature_limits')
                        ->where('id', $existing->id)
                        ->update($values);
                } else {
                    DB::table('core_feature_limits')
                        ->insert(array_merge(
                            [
                                'core_feature_id' => $emailFeatureId,
                                'limit_key' => 'email_daily_sends',
                                'created_at' => now(),
                            ],
                            $values
                        ));
                }
            }
        }
    }

    public function down(): void
    {
        if (
            Schema::hasTable('core_features')
            && Schema::hasTable('core_feature_limits')
        ) {
            $emailFeatureId =
                DB::table('core_features')
                    ->where('key', 'email')
                    ->value('id');

            if ($emailFeatureId) {
                DB::table('core_feature_limits')
                    ->where('core_feature_id', $emailFeatureId)
                    ->where('limit_key', 'email_daily_sends')
                    ->delete();
            }
        }

        Schema::dropIfExists('website_daily_email_usage');
    }
};
