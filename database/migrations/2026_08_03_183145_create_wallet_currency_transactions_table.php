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
        Schema::create('wallet_currency_transactions', function (Blueprint $table) {

            $table->id();

            /*
            |--------------------------------------------------------------------------
            | Wallet
            |--------------------------------------------------------------------------
            */

            $table->foreignId('wallet_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('wallet_currency_id')
                ->constrained()
                ->cascadeOnDelete();

            /*
            |--------------------------------------------------------------------------
            | Currency
            |--------------------------------------------------------------------------
            */

            $table->foreignId('currency_id')
                ->constrained()
                ->cascadeOnDelete();

            /*
            |--------------------------------------------------------------------------
            | Transaction Identity
            |--------------------------------------------------------------------------
            */

            $table->uuid('uuid')->unique();

            $table->string('reference')->unique();

            /*
            |--------------------------------------------------------------------------
            | Transaction Type
            |--------------------------------------------------------------------------
            */

            $table->enum('type', [

                'deposit',

                'withdrawal',

                'transfer',

                'conversion',

                'payment',

                'refund',

                'commission',

                'adjustment'

            ]);

            /*
            |--------------------------------------------------------------------------
            | Amount
            |--------------------------------------------------------------------------
            */

            $table->decimal('amount', 18, 8);

            $table->decimal('balance_before', 18, 8);

            $table->decimal('balance_after', 18, 8);

            /*
            |--------------------------------------------------------------------------
            | Related Transaction
            |--------------------------------------------------------------------------
            */

            $table->string('source')->nullable();

            $table->unsignedBigInteger('source_id')->nullable();

            /*
            |--------------------------------------------------------------------------
            | Metadata
            |--------------------------------------------------------------------------
            */

            $table->json('metadata')->nullable();

            /*
            |--------------------------------------------------------------------------
            | Status
            |--------------------------------------------------------------------------
            */

            $table->enum('status', [

                'pending',

                'completed',

                'failed',

                'cancelled'

            ])->default('completed');

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
        Schema::dropIfExists('wallet_currency_transactions');
    }
};
