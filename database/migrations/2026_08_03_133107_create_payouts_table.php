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
        Schema::create('payouts', function (Blueprint $table) {

            $table->id();

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
            | Settlement Source
            |--------------------------------------------------------------------------
            */

            $table->foreignId('payment_settlement_id')
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
            | Amount
            |--------------------------------------------------------------------------
            */

            $table->decimal('amount', 18, 8);

            $table->decimal('fee', 18, 8)
                ->default(0);

            $table->decimal('net_amount', 18, 8);

            /*
            |--------------------------------------------------------------------------
            | Payout Method
            |--------------------------------------------------------------------------
            */

            $table->enum('method', [

                'bank_transfer',

                'wallet',

                'paypal',

                'stripe_connect',

                'crypto',

                'mobile_money'

            ])->default('bank_transfer');

            /*
            |--------------------------------------------------------------------------
            | Provider
            |--------------------------------------------------------------------------
            */

            $table->foreignId('payment_provider_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();

            $table->string('provider_reference')
                ->nullable();

            $table->json('provider_response')
                ->nullable();

            /*
            |--------------------------------------------------------------------------
            | Recipient Details
            |--------------------------------------------------------------------------
            */

            $table->json('recipient_details')
                ->nullable();

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
            | Processing
            |--------------------------------------------------------------------------
            */

            $table->timestamp('processed_at')
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
        Schema::dropIfExists('payouts');
    }
};
