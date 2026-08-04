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
        Schema::create('payment_transactions', function (Blueprint $table) {

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
            | Provider & Method
            |--------------------------------------------------------------------------
            */

            $table->foreignId('payment_provider_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();

            $table->foreignId('payment_method_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();

            /*
            |--------------------------------------------------------------------------
            | Related Resources
            |--------------------------------------------------------------------------
            */

            $table->string('payable_type')
                ->nullable();

            $table->unsignedBigInteger('payable_id')
                ->nullable();

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

            $table->decimal('net_amount', 18, 8)
                ->default(0);

            /*
            |--------------------------------------------------------------------------
            | Gateway Information
            |--------------------------------------------------------------------------
            */

            $table->string('gateway_reference')
                ->nullable();

            $table->json('gateway_response')
                ->nullable();

            /*
            |--------------------------------------------------------------------------
            | Transaction Type
            |--------------------------------------------------------------------------
            */

            $table->enum('type', [

                'payment',

                'deposit',

                'withdrawal',

                'subscription',

                'invoice',

                'marketplace',

                'refund'

            ])->default('payment');

            /*
            |--------------------------------------------------------------------------
            | Status
            |--------------------------------------------------------------------------
            */

            $table->enum('status', [

                'pending',

                'processing',

                'successful',

                'failed',

                'cancelled',

                'refunded'

            ])->default('pending');

            /*
            |--------------------------------------------------------------------------
            | Timing
            |--------------------------------------------------------------------------
            */

            $table->timestamp('paid_at')
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
        Schema::dropIfExists('payment_transactions');
    }
};
