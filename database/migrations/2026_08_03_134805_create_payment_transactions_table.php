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
        Schema::create('payment_transactions', function (Blueprint $table) {

            $table->id();

            $table->foreignId('workspace_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('wallet_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();

            // Add FK later after payment_providers exists
            $table->foreignId('payment_provider_id')
                ->nullable();

            $table->string('reference')->unique();

            $table->decimal('amount', 15, 2);

            $table->string('currency', 10)->default('NGN');

            $table->string('status')->default('pending');

            $table->json('payload')->nullable();

            $table->timestamps();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_transactions');
    }
};
