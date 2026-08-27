<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(
            'off_server_license_products',
            function (Blueprint $table) {

                $table->id();

                $table->unsignedBigInteger(
                    'catalog_product_id'
                )
                    ->nullable()
                    ->index();

                $table->string(
                    'name',
                    150
                );

                $table->string(
                    'slug',
                    180
                )
                    ->unique();

                $table->text(
                    'description'
                )
                    ->nullable();

                /*
                 * License family/type.
                 *
                 * Examples:
                 * core
                 * ecommerce
                 * hotel
                 * school
                 *
                 * This allows Website Types to require the correct
                 * off-server license family without duplicating the
                 * license engine.
                 */
                $table->string(
                    'license_type',
                    100
                )
                    ->default('core');

                $table->decimal(
                    'price',
                    18,
                    2
                )
                    ->default(0);

                $table->string(
                    'currency',
                    10
                )
                    ->default('NGN');

                /*
                 * Permanent commercial rule.
                 */
                $table->unsignedInteger(
                    'domains_per_license'
                )
                    ->default(1);

                $table->boolean(
                    'is_active'
                )
                    ->default(true);

                $table->unsignedInteger(
                    'sort_order'
                )
                    ->default(0);

                $table->json(
                    'metadata'
                )
                    ->nullable();

                $table->timestamps();

                $table->index(
                    [
                        'license_type',
                        'is_active',
                    ],
                    'oslp_type_active_idx'
                );
            }
        );


        /*
         * Track which exact license product issued each registration.
         */
        Schema::table(
            'off_server_license_registrations',
            function (Blueprint $table) {

                $table->unsignedBigInteger(
                    'license_product_id'
                )
                    ->nullable()
                    ->after('user_id')
                    ->index();

                $table->unsignedBigInteger(
                    'marketplace_order_id'
                )
                    ->nullable()
                    ->after('license_product_id')
                    ->unique();

                $table->string(
                    'license_type',
                    100
                )
                    ->default('core')
                    ->after('marketplace_order_id');
            }
        );
    }


    public function down(): void
    {
        Schema::table(
            'off_server_license_registrations',
            function (Blueprint $table) {

                $table->dropUnique([
                    'marketplace_order_id'
                ]);

                $table->dropColumn([
                    'license_product_id',
                    'marketplace_order_id',
                    'license_type',
                ]);
            }
        );

        Schema::dropIfExists(
            'off_server_license_products'
        );
    }
};
