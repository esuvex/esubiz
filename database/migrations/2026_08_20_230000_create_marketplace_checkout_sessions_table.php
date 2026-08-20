<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('marketplace_checkout_sessions', function (Blueprint $table) {

            $table->id();

            /*
            |--------------------------------------------------------------------------
            | Ownership / Account Mode
            |--------------------------------------------------------------------------
            */

            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->enum('account_mode', [
                'user',
                'developer',
            ]);

            /*
            |--------------------------------------------------------------------------
            | Generic Marketplace Product
            |--------------------------------------------------------------------------
            */

            $table->string('product_type');
            $table->unsignedBigInteger('product_id');

            /*
            |--------------------------------------------------------------------------
            | Checkout
            |--------------------------------------------------------------------------
            */

            $table->unsignedInteger('quantity')->default(1);

            $table->decimal('unit_price', 18, 2);
            $table->decimal('total_amount', 18, 2);

            $table->string('currency', 10)->default('NGN');

            /*
            |--------------------------------------------------------------------------
            | Payment Selection
            |--------------------------------------------------------------------------
            */

            $table->foreignId('payment_method_id')
                ->nullable()
                ->constrained('payment_methods')
                ->nullOnDelete();

            $table->foreignId('payment_provider_id')
                ->nullable()
                ->constrained('payment_providers')
                ->nullOnDelete();

            /*
            |--------------------------------------------------------------------------
            | Connected Records
            |--------------------------------------------------------------------------
            */

            $table->foreignId('marketplace_order_id')
                ->nullable()
                ->constrained('marketplace_orders')
                ->nullOnDelete();

            $table->foreignId('payment_transaction_id')
                ->nullable()
                ->constrained('payment_transactions')
                ->nullOnDelete();

            /*
            |--------------------------------------------------------------------------
            | Session State
            |--------------------------------------------------------------------------
            */

            $table->enum('status', [
                'active',
                'pending_payment',
                'completed',
                'cancelled',
                'expired',
            ])->default('active');

            $table->timestamp('expires_at')->nullable();

            $table->timestamps();

            $table->softDeletes();

            $table->index([
                'user_id',
                'account_mode',
                'status',
            ]);

            $table->index([
                'product_type',
                'product_id',
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('marketplace_checkout_sessions');
    }
};
