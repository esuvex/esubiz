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
        Schema::create('api_payments', function (Blueprint $table) {

            $table->id();

            /*
            |--------------------------------------------------------------------------
            | Relationships
            |--------------------------------------------------------------------------
            */

            $table->foreignId('api_invoice_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();

            $table->foreignId('wallet_transaction_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();

            $table->foreignId('payment_transaction_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();

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
            | Payment
            |--------------------------------------------------------------------------
            */

            $table->decimal('amount', 18, 2);

            $table->string('currency', 3)->default('NGN');

            /*
            |--------------------------------------------------------------------------
            | Gateway
            |--------------------------------------------------------------------------
            */

            $table->string('gateway')->nullable();

            $table->string('gateway_reference')->nullable();

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

                'refunded'

            ])->default('pending');

            $table->timestamp('paid_at')->nullable();

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
        Schema::dropIfExists('api_payments');
    }
};
