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
        Schema::create('api_subscriptions', function (Blueprint $table) {

            $table->id();

            /*
            |--------------------------------------------------------------------------
            | Relationships
            |--------------------------------------------------------------------------
            */

            $table->foreignId('api_plan_id')
                ->constrained()
                ->cascadeOnDelete();

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

            $table->string('reference')->unique();

            /*
            |--------------------------------------------------------------------------
            | Subscription
            |--------------------------------------------------------------------------
            */

            $table->timestamp('starts_at')->nullable();

            $table->timestamp('expires_at')->nullable();

            /*
            |--------------------------------------------------------------------------
            | Billing
            |--------------------------------------------------------------------------
            */

            $table->decimal('amount', 18, 2)->default(0);

            $table->string('currency', 3)->default('NGN');

            /*
            |--------------------------------------------------------------------------
            | Status
            |--------------------------------------------------------------------------
            */

            $table->enum('status', [

                'active',

                'expired',

                'cancelled',

                'suspended'

            ])->default('active');

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
        Schema::dropIfExists('api_subscriptions');
    }
};
