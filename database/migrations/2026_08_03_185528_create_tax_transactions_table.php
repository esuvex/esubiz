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
        Schema::create('tax_transactions', function (Blueprint $table) {

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
            | Reference
            |--------------------------------------------------------------------------
            */

            $table->string('taxable_type');

            $table->unsignedBigInteger('taxable_id');

            /*
            |--------------------------------------------------------------------------
            | Tax Rule
            |--------------------------------------------------------------------------
            */

            $table->foreignId('currency_tax_rule_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();

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
            | Calculation
            |--------------------------------------------------------------------------
            */

            $table->decimal('amount_before_tax', 18, 8);

            $table->decimal('tax_amount', 18, 8);

            $table->decimal('amount_after_tax', 18, 8);

            $table->decimal('tax_rate', 8, 4);

            /*
            |--------------------------------------------------------------------------
            | Identity
            |--------------------------------------------------------------------------
            */

            $table->uuid('uuid')->unique();

            $table->string('reference')->unique();

            /*
            |--------------------------------------------------------------------------
            | Type
            |--------------------------------------------------------------------------
            */

            $table->enum('type', [

                'invoice',

                'payment',

                'subscription',

                'marketplace',

                'withdrawal',

                'refund',

                'adjustment'

            ])->default('invoice');

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
        Schema::dropIfExists('tax_transactions');
    }
};
