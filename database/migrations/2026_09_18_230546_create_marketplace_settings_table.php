<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('marketplace_settings', function (Blueprint $table) {
            $table->id();

            /*
             * Universal Marketplace setting identity.
             * Examples:
             * marketplace.enabled
             * marketplace.developer_sales_enabled
             * marketplace.referrals_enabled
             */
            $table->string('key', 150)->unique('mkt_setting_key_uq');

            /*
             * Flexible value storage lets Central add Marketplace-wide
             * settings without requiring a schema change for every option.
             */
            $table->json('value')->nullable();

            $table->string('value_type', 24)
                ->default('string');

            $table->string('group', 80)
                ->default('general')
                ->index('mkt_setting_group_ix');

            $table->text('description')->nullable();

            $table->boolean('is_active')
                ->default(true)
                ->index('mkt_setting_active_ix');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('marketplace_settings');
    }
};
