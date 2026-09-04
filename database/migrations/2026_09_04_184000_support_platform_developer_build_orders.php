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
             * Vendor-backed Marketplace products retain vendor_id.
             *
             * Esubiz-owned Developer Build purchases have no external
             * Marketplace vendor, so vendor_id must be nullable.
             */
            $table->unsignedBigInteger('vendor_id')
                ->nullable()
                ->change();
        });
    }

    public function down(): void
    {
        /*
         * Intentionally do not restore NOT NULL automatically because
         * platform-owned Developer Build orders may already exist.
         */
    }
};
