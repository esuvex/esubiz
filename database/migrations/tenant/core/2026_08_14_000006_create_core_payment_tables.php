<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        /*
        |--------------------------------------------------------------------------
        | Payment Gateways
        |--------------------------------------------------------------------------
        */

        Schema::create('payment_gateways', function (Blueprint $table) {
            $table->id();

            $table->string('name');
            $table->string('slug')->unique();

            $table->string('type')->default('offline');

            $table->string('provider')->nullable();

            $table->string('status')->default('inactive');

            $table->json('credentials')->nullable();
            $table->json('settings')->nullable();

            $table->timestamps();

            $table->index('type');
            $table->index('status');
        });

        /*
        |--------------------------------------------------------------------------
        | Payment Methods
        |--------------------------------------------------------------------------
        */

        Schema::create('payment_methods', function (Blueprint $table) {
            $table->id();

            $table->foreignId('gateway_id')
                ->constrained('payment_gateways')
                ->cascadeOnDelete();

            $table->string('name');
            $table->string('code')->nullable();

            $table->string('status')->default('active');

            $table->json('settings')->nullable();

            $table->timestamps();
        });

        /*
        |--------------------------------------------------------------------------
        | Payment Transactions
        |--------------------------------------------------------------------------
        */

        Schema::create('payment_transactions', function (Blueprint $table) {
            $table->id();

            $table->foreignId('gateway_id')
                ->nullable()
                ->constrained('payment_gateways')
                ->nullOnDelete();

            $table->foreignId('payment_method_id')
                ->nullable()
                ->constrained('payment_methods')
                ->nullOnDelete();

            $table->string('reference')->unique();

            $table->string('type')->default('payment');

            $table->decimal('amount', 20, 2);
            $table->string('currency', 10)->default('NGN');

            $table->string('status')->default('pending');

            $table->string('customer_name')->nullable();
            $table->string('customer_email')->nullable();
            $table->string('customer_phone')->nullable();

            $table->string('external_reference')->nullable();

            $table->json('metadata')->nullable();

            $table->timestamp('paid_at')->nullable();

            $table->timestamps();

            $table->index('status');
            $table->index('external_reference');
        });

        /*
        |--------------------------------------------------------------------------
        | Payment Refunds
        |--------------------------------------------------------------------------
        */

        Schema::create('payment_refunds', function (Blueprint $table) {
            $table->id();

            $table->foreignId('payment_transaction_id')
                ->constrained('payment_transactions')
                ->cascadeOnDelete();

            $table->string('reference')->unique();

            $table->decimal('amount', 20, 2);

            $table->string('status')->default('pending');

            $table->text('reason')->nullable();

            $table->timestamp('refunded_at')->nullable();

            $table->timestamps();
        });

        /*
        |--------------------------------------------------------------------------
        | Payment Webhooks
        |--------------------------------------------------------------------------
        */

        Schema::create('payment_webhooks', function (Blueprint $table) {
            $table->id();

            $table->foreignId('gateway_id')
                ->nullable()
                ->constrained('payment_gateways')
                ->nullOnDelete();

            $table->string('event')->nullable();

            $table->string('external_id')->nullable();

            $table->json('payload');

            $table->string('status')->default('received');

            $table->text('error')->nullable();

            $table->timestamps();

            $table->index('external_id');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_webhooks');
        Schema::dropIfExists('payment_refunds');
        Schema::dropIfExists('payment_transactions');
        Schema::dropIfExists('payment_methods');
        Schema::dropIfExists('payment_gateways');
    }
};
