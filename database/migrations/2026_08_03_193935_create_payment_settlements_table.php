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
        Schema::create('payment_settlements', function (Blueprint $table) {

            $table->id();

            /*
            |--------------------------------------------------------------------------
            | Payment Reference
            |--------------------------------------------------------------------------
            */

            $table->foreignId('payment_transaction_id')
                ->constrained()
                ->cascadeOnDelete();

            /*
            |--------------------------------------------------------------------------
            | Ownership
            |--------------------------------------------------------------------------
            */

            $table->foreignId('user_id')
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
            | Currency
            |--------------------------------------------------------------------------
            */

            $table->foreignId('currency_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();

            /*
            |--------------------------------------------------------------------------
            | Amount Breakdown
            |--------------------------------------------------------------------------
            */

            $table->decimal('gross_amount', 18, 8);

            $table->decimal('gateway_fee', 18, 8)
                ->default(0);

            $table->decimal('tax_amount', 18, 8)
                ->default(0);

            $table->decimal('platform_fee', 18, 8)
                ->default(0);

            $table->decimal('commission_amount', 18, 8)
                ->default(0);

            $table->decimal('net_amount', 18, 8);

            /*
            |--------------------------------------------------------------------------
            | Settlement Type
            |--------------------------------------------------------------------------
            */

            $table->enum('type', [

                'platform',

                'developer',

                'seller',

                'referral',

                'wallet'

            ])->default('platform');

            /*
            |--------------------------------------------------------------------------
            | Status
            |--------------------------------------------------------------------------
            */

            $table->enum('status', [

                'pending',

                'processing',

                'completed',

                'failed',

                'cancelled'

            ])->default('pending');

            /*
            |--------------------------------------------------------------------------
            | Settlement Date
            |--------------------------------------------------------------------------
            */

            $table->timestamp('settled_at')
                ->nullable();

            /*
            |--------------------------------------------------------------------------
            | Metadata
            |--------------------------------------------------------------------------
            */

            $table->json('metadata')
                ->nullable();

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
        Schema::dropIfExists('payment_settlements');
    }
};
