<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('marketplace_settlements', function (Blueprint $table) {

            $table->id();

            /*
            |--------------------------------------------------------------------------
            | Relationships
            |--------------------------------------------------------------------------
            */

            $table->foreignId('vendor_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('wallet_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();

            $table->foreignId('payout_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();

            $table->foreignId('workspace_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();

            /*
            |--------------------------------------------------------------------------
            | Identity
            |--------------------------------------------------------------------------
            */

            $table->uuid('uuid')->unique();

            $table->string('reference')->unique();

            /*
            |--------------------------------------------------------------------------
            | Settlement
            |--------------------------------------------------------------------------
            */

            $table->decimal('gross_amount', 18, 2);

            $table->decimal('commission_amount', 18, 2)->default(0);

            $table->decimal('fee_amount', 18, 2)->default(0);

            $table->decimal('net_amount', 18, 2);

            $table->string('currency', 3)->default('NGN');

            /*
            |--------------------------------------------------------------------------
            | Period
            |--------------------------------------------------------------------------
            */

            $table->timestamp('period_start')->nullable();

            $table->timestamp('period_end')->nullable();

            /*
            |--------------------------------------------------------------------------
            | Status
            |--------------------------------------------------------------------------
            */

            $table->enum('status', [

                'pending',

                'approved',

                'processing',

                'paid',

                'failed',

                'cancelled'

            ])->default('pending');

            /*
            |--------------------------------------------------------------------------
            | Audit
            |--------------------------------------------------------------------------
            */

            $table->timestamps();

            $table->softDeletes();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('marketplace_settlements');
    }
};
