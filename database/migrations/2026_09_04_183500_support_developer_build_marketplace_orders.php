<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('marketplace_orders', function (Blueprint $table) {
            /*
             * Normal Marketplace products continue using
             * marketplace_listing_id.
             *
             * Compiled Developer Builds use developer_build_id because
             * Website Types themselves are not Marketplace products.
             */
            $table->unsignedBigInteger('marketplace_listing_id')
                ->nullable()
                ->change();

            $table->unsignedBigInteger('developer_build_id')
                ->nullable()
                ->after('marketplace_listing_id');

            $table->index(
                'developer_build_id',
                'mkt_orders_dev_build_idx'
            );
        });
    }

    public function down(): void
    {
        Schema::table('marketplace_orders', function (Blueprint $table) {
            $table->dropIndex('mkt_orders_dev_build_idx');
            $table->dropColumn('developer_build_id');
        });

        /*
         * Do not automatically restore NOT NULL here because Developer
         * Build orders may exist by the time this migration is rolled back.
         */
    }
};
