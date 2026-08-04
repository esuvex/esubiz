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
        Schema::create('commission_transactions', function (Blueprint $table) {

            $table->id();

            /*
            |--------------------------------------------------------------------------
            | Recipient
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
            | Commission Rule
            |--------------------------------------------------------------------------
            */

            $table->foreignId('commission_rule_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();

            /*
            |--------------------------------------------------------------------------
            | Source Transaction
            |--------------------------------------------------------------------------
            */

            $table->string('source_type')
                ->nullable();

            $table->unsignedBigInteger('source_id')
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
            | Commission Calculation
            |--------------------------------------------------------------------------
            */

            $table->decimal('base_amount', 18, 8);

            $table->decimal('commission_rate', 8, 4)
                ->default(0);

            $table->decimal('commission_amount', 18, 8);

            /*
            |--------------------------------------------------------------------------
            | Type
            |--------------------------------------------------------------------------
            */

            $table->enum('type', [

                'referral',

                'partner',

                'developer',

                'marketplace',

                'subscription',

                'custom'

            ])->default('referral');

            /*
            |--------------------------------------------------------------------------
            | Status
            |--------------------------------------------------------------------------
            */

            $table->enum('status', [

                'pending',

                'approved',

                'paid',

                'cancelled'

            ])->default('pending');

            /*
            |--------------------------------------------------------------------------
            | Settlement
            |--------------------------------------------------------------------------
            */

            $table->foreignId('payment_settlement_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();

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
        Schema::dropIfExists('commission_transactions');
    }
};
