<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * ESUBIZ_CENTRAL_EMAIL_SERVICE_SETTINGS_V1
 *
 * Central Admin controls how Default and Premium email sending
 * services are presented to both SaaS and off-server Core.
 *
 * Credit balances/packages remain in the existing Central credit
 * architecture. This table does not duplicate credit accounting.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('email_service_settings')) {
            Schema::create(
                'email_service_settings',
                function (Blueprint $table) {
                    $table->id();

                    $table->string('service_key', 40)
                        ->unique();

                    $table->string('name', 120);

                    $table->string('short_label', 120);

                    $table->text('description')
                        ->nullable();

                    $table->longText('info_content')
                        ->nullable();

                    $table->unsignedInteger('daily_limit')
                        ->nullable();

                    $table->string('period_label', 80)
                        ->nullable();

                    $table->boolean('is_enabled')
                        ->default(true);

                    $table->unsignedInteger('sort_order')
                        ->default(0);

                    $table->timestamps();
                }
            );
        }

        /*
         * Seed only neutral defaults.
         *
         * Delivery-success claims are deliberately NOT invented.
         * Central Admin supplies the actual customer-facing wording.
         */
        DB::table('email_service_settings')->updateOrInsert(
            ['service_key' => 'default'],
            [
                'name' => 'Default Email Sender',
                'short_label' => 'Default',
                'description' =>
                    'Send email using the website hosting server.',
                'info_content' =>
                    'Uses the standard server email sending service. Sending allowance and service information are controlled by Esubiz Central Admin.',
                'daily_limit' => null,
                'period_label' => 'Daily',
                'is_enabled' => true,
                'sort_order' => 10,
                'updated_at' => now(),
                'created_at' => now(),
            ]
        );

        DB::table('email_service_settings')->updateOrInsert(
            ['service_key' => 'premium'],
            [
                'name' => 'Esubiz Premium',
                'short_label' => 'Premium',
                'description' =>
                    'Use Esubiz Email Credits for premium email delivery.',
                'info_content' =>
                    'Premium email delivery consumes Email Credits from the authoritative Esubiz Central balance. Packages and service information are controlled by Esubiz Central Admin.',
                'daily_limit' => null,
                'period_label' => 'Daily',
                'is_enabled' => true,
                'sort_order' => 20,
                'updated_at' => now(),
                'created_at' => now(),
            ]
        );
    }

    public function down(): void
    {
        Schema::dropIfExists(
            'email_service_settings'
        );
    }
};
