<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('theme_packages', function (Blueprint $table) {

            /*
             * Commercial configuration.
             */
            $table->decimal(
                'saas_price',
                18,
                2
            )->nullable()->after(
                'saas_available'
            );

            $table->string(
                'saas_currency',
                3
            )->default('NGN')->after(
                'saas_price'
            );

            $table->string(
                'saas_billing_interval',
                30
            )->nullable()->after(
                'saas_currency'
            );


            $table->decimal(
                'off_server_price',
                18,
                2
            )->nullable()->after(
                'off_server_available'
            );

            $table->string(
                'off_server_currency',
                3
            )->default('NGN')->after(
                'off_server_price'
            );


            /*
             * Release management.
             */
            $table->string(
                'release_status',
                30
            )->default('draft')->index();

            $table->text(
                'release_notes'
            )->nullable();

            $table->timestamp(
                'published_at'
            )->nullable();


            /*
             * Marketplace controls.
             */
            $table->boolean(
                'marketplace_enabled'
            )->default(false)->index();

            $table->boolean(
                'marketplace_featured'
            )->default(false);

            $table->decimal(
                'commission_rate',
                5,
                2
            )->default(0);

            $table->string(
                'marketplace_category',
                100
            )->nullable();


            /*
             * Package editing/version lineage.
             */
            $table->unsignedBigInteger(
                'parent_package_id'
            )->nullable()->index();

            $table->boolean(
                'is_current'
            )->default(true)->index();
        });
    }


    public function down(): void
    {
        Schema::table('theme_packages', function (Blueprint $table) {

            $table->dropColumn([
                'saas_price',
                'saas_currency',
                'saas_billing_interval',
                'off_server_price',
                'off_server_currency',
                'release_status',
                'release_notes',
                'published_at',
                'marketplace_enabled',
                'marketplace_featured',
                'commission_rate',
                'marketplace_category',
                'parent_package_id',
                'is_current',
            ]);
        });
    }
};
