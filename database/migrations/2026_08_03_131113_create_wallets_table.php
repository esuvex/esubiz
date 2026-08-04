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
        Schema::create('wallets', function (Blueprint $table) {

            $table->id();

            /*
            |--------------------------------------------------------------------------
            | Ownership
            |--------------------------------------------------------------------------
            */

            $table->foreignId('workspace_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();

            $table->foreignId('user_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();

            /*
            |--------------------------------------------------------------------------
            | Identity
            |--------------------------------------------------------------------------
            */

            $table->uuid('uuid')->unique();

            $table->string('name');

            /*
            |--------------------------------------------------------------------------
            | Wallet Type
            |--------------------------------------------------------------------------
            */

            $table->enum('type', [

                'platform',

                'workspace',

                'referral',

                'developer',

                'customer',

                'vendor',

                'escrow'

            ]);

            /*
            |--------------------------------------------------------------------------
            | Currency
            |--------------------------------------------------------------------------
            */

            $table->string('currency', 3)->default('NGN');

            /*
            |--------------------------------------------------------------------------
            | Balance
            |--------------------------------------------------------------------------
            */

            $table->decimal('available_balance', 18, 2)->default(0);

            $table->decimal('pending_balance', 18, 2)->default(0);

            $table->decimal('reserved_balance', 18, 2)->default(0);

            /*
            |--------------------------------------------------------------------------
            | Status
            |--------------------------------------------------------------------------
            */

            $table->boolean('is_default')->default(false);

            $table->boolean('is_active')->default(true);

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
        Schema::dropIfExists('wallets');
    }
};
