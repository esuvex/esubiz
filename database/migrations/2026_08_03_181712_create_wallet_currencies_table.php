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
        Schema::create('wallet_currencies', function (Blueprint $table) {

            $table->id();

            /*
            |--------------------------------------------------------------------------
            | Wallet Owner
            |--------------------------------------------------------------------------
            */

            $table->foreignId('wallet_id')
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
            | Balance
            |--------------------------------------------------------------------------
            */

            $table->decimal('balance', 18, 8)
                ->default(0);

            $table->decimal('available_balance', 18, 8)
                ->default(0);

            $table->decimal('locked_balance', 18, 8)
                ->default(0);

            /*
            |--------------------------------------------------------------------------
            | Limits
            |--------------------------------------------------------------------------
            */

            $table->decimal('daily_limit', 18, 8)
                ->nullable();

            $table->decimal('withdrawal_limit', 18, 8)
                ->nullable();

            /*
            |--------------------------------------------------------------------------
            | Status
            |--------------------------------------------------------------------------
            */

            $table->boolean('is_active')
                ->default(true);

            /*
            |--------------------------------------------------------------------------
            | Audit
            |--------------------------------------------------------------------------
            */

            $table->timestamps();

            $table->softDeletes();

            $table->unique([
                'wallet_id',
                'currency_id'
            ]);

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wallet_currencies');
    }
};
