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
        Schema::create('payment_attempts', function (Blueprint $table) {

            $table->id();

            /*
            |--------------------------------------------------------------------------
            | Transaction
            |--------------------------------------------------------------------------
            */

            $table->foreignId('payment_transaction_id')
                ->constrained()
                ->cascadeOnDelete();

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
            | Identity
            |--------------------------------------------------------------------------
            */

            $table->uuid('uuid')->unique();

            $table->unsignedInteger('attempt_number')
                ->default(1);

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
            | Amount
            |--------------------------------------------------------------------------
            */

            $table->decimal('amount', 18, 8);

            /*
            |--------------------------------------------------------------------------
            | Status
            |--------------------------------------------------------------------------
            */

            $table->enum('status', [

                'initiated',

                'processing',

                'successful',

                'failed',

                'cancelled'

            ])->default('initiated');

            /*
            |--------------------------------------------------------------------------
            | Error Handling
            |--------------------------------------------------------------------------
            */

            $table->string('failure_code')
                ->nullable();

            $table->text('failure_reason')
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
        Schema::dropIfExists('payment_attempts');
    }
};
