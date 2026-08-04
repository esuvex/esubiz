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
        Schema::create('payment_refunds', function (Blueprint $table) {

            $table->id();

            /*
            |--------------------------------------------------------------------------
            | Original Payment
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
            | Amount
            |--------------------------------------------------------------------------
            */

            $table->decimal('amount', 18, 8);

            $table->decimal('fee_refunded', 18, 8)
                ->default(0);

            /*
            |--------------------------------------------------------------------------
            | Gateway
            |--------------------------------------------------------------------------
            */

            $table->string('gateway_reference')
                ->nullable();

            $table->json('gateway_response')
                ->nullable();

            /*
            |--------------------------------------------------------------------------
            | Refund Type
            |--------------------------------------------------------------------------
            */

            $table->enum('type', [

                'full',

                'partial',

                'automatic',

                'manual'

            ])->default('partial');

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

                'cancelled'

            ])->default('pending');

            /*
            |--------------------------------------------------------------------------
            | Reason
            |--------------------------------------------------------------------------
            */

            $table->text('reason')
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
        Schema::dropIfExists('payment_refunds');
    }
};
