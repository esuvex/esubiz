<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('website_types', function (Blueprint $table) {
            $table->decimal('saas_price', 15, 2)
                ->nullable()
                ->after('wizard_settings');

            $table->unsignedSmallInteger('saas_billing_period')
                ->nullable()
                ->after('saas_price');

            $table->string('saas_billing_interval', 20)
                ->nullable()
                ->after('saas_billing_period');

            $table->decimal('off_server_price', 15, 2)
                ->nullable()
                ->after('saas_billing_interval');
        });
    }

    public function down(): void
    {
        Schema::table('website_types', function (Blueprint $table) {
            $table->dropColumn([
                'saas_price',
                'saas_billing_period',
                'saas_billing_interval',
                'off_server_price',
            ]);
        });
    }
};
