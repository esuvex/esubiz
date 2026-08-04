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
        Schema::create('api_invoices', function (Blueprint $table) {

            $table->id();

            /*
            |--------------------------------------------------------------------------
            | Relationships
            |--------------------------------------------------------------------------
            */

            $table->foreignId('api_subscription_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();

            $table->foreignId('api_application_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();

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

            $table->string('invoice_number')->unique();

            /*
            |--------------------------------------------------------------------------
            | Billing
            |--------------------------------------------------------------------------
            */

            $table->decimal('subtotal', 18, 2)->default(0);

            $table->decimal('tax', 18, 2)->default(0);

            $table->decimal('discount', 18, 2)->default(0);

            $table->decimal('total', 18, 2)->default(0);

            $table->string('currency', 3)->default('NGN');

            /*
            |--------------------------------------------------------------------------
            | Period
            |--------------------------------------------------------------------------
            */

            $table->timestamp('billing_start')->nullable();

            $table->timestamp('billing_end')->nullable();

            /*
            |--------------------------------------------------------------------------
            | Payment
            |--------------------------------------------------------------------------
            */

            $table->enum('status', [

                'draft',

                'pending',

                'paid',

                'failed',

                'cancelled'

            ])->default('pending');

            $table->timestamp('paid_at')->nullable();

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
        Schema::dropIfExists('api_invoices');
    }
};
