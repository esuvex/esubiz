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
        Schema::create('wallet_settings', function (Blueprint $table) {

            $table->id();

            /*
            |--------------------------------------------------------------------------
            | Owner
            |--------------------------------------------------------------------------
            */

            $table->foreignId('workspace_id')
                ->nullable()
                ->constrained()
                ->cascadeOnDelete();

            /*
            |--------------------------------------------------------------------------
            | Funding
            |--------------------------------------------------------------------------
            */

            $table->boolean('funding_enabled')->default(true);

            $table->decimal('minimum_funding', 18, 2)->default(0);

            $table->decimal('maximum_funding', 18, 2)->nullable();

            $table->enum('funding_fee_type', [
                'free',
                'fixed',
                'percentage'
            ])->default('free');

            $table->decimal('funding_fee', 18, 2)->default(0);

            /*
            |--------------------------------------------------------------------------
            | Payout
            |--------------------------------------------------------------------------
            */

            $table->boolean('payout_enabled')->default(true);

            $table->decimal('minimum_payout', 18, 2)->default(0);

            $table->decimal('maximum_payout', 18, 2)->nullable();

            $table->enum('payout_fee_type', [
                'free',
                'fixed',
                'percentage'
            ])->default('free');

            $table->decimal('payout_fee', 18, 2)->default(0);

            /*
            |--------------------------------------------------------------------------
            | Wallet Transfer
            |--------------------------------------------------------------------------
            */

            $table->boolean('wallet_transfer_enabled')->default(true);

            $table->enum('wallet_transfer_fee_type', [
                'free',
                'fixed',
                'percentage'
            ])->default('free');

            $table->decimal('wallet_transfer_fee', 18, 2)->default(0);

            /*
            |--------------------------------------------------------------------------
            | Processing
            |--------------------------------------------------------------------------
            */

            $table->enum('payout_cycle', [
                'instant',
                'manual',
                'daily',
                'weekly',
                'monthly'
            ])->default('instant');

            $table->boolean('approval_required')->default(false);

            /*
            |--------------------------------------------------------------------------
            | Audit
            |--------------------------------------------------------------------------
            */

            $table->timestamps();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wallet_settings');
    }
};
