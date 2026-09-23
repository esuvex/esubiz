<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('email_service_settings', function (Blueprint $table) {
            /*
             * ESUBIZ EMAIL CONTROL FOUNDATION
             *
             * Central is authoritative for email service policy used by:
             * - Central Esubiz email
             * - SaaS Core Default Sender
             * - SaaS Core Premium Sender
             * - Off-server Core Premium Sender
             *
             * AWS provider pricing is NOT manually configured here.
             * It will be synchronized from the provider/pricing service.
             */

            $table->string('limit_period', 20)
                ->nullable()
                ->after('daily_limit');

            $table->unsignedBigInteger('limit_amount')
                ->nullable()
                ->after('limit_period');

            $table->boolean('allow_saas')
                ->default(true)
                ->after('limit_amount');

            $table->boolean('allow_off_server')
                ->default(false)
                ->after('allow_saas');

            $table->boolean('compliance_enabled')
                ->default(true)
                ->after('allow_off_server');
        });
    }

    public function down(): void
    {
        Schema::table('email_service_settings', function (Blueprint $table) {
            $table->dropColumn([
                'limit_period',
                'limit_amount',
                'allow_saas',
                'allow_off_server',
                'compliance_enabled',
            ]);
        });
    }
};
