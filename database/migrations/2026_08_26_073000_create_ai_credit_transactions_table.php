<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ai_credit_transactions', function (Blueprint $table) {
            $table->id();

            $table->foreignId('website_id')
                ->constrained('websites')
                ->cascadeOnDelete();

            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedBigInteger('workspace_id')->nullable();

            $table->uuid('uuid')->unique();

            /*
             * Idempotency / request protection.
             *
             * One AI request must never be charged twice.
             */
            $table->string('request_key', 191)->unique();

            $table->string('reference', 100)->unique();

            $table->enum('direction', [
                'credit',
                'debit',
                'refund',
            ]);

            $table->string('type', 100);

            $table->decimal('credits', 18, 6);

            $table->decimal('balance_before', 18, 6);
            $table->decimal('balance_after', 18, 6);

            /*
             * AI execution trace.
             */
            $table->unsignedBigInteger('ai_usage_log_id')->nullable();
            $table->unsignedBigInteger('ai_model_id')->nullable();

            $table->string('route_key', 150)->nullable();

            /*
             * Allows future sources:
             *
             * AI request
             * package purchase
             * admin adjustment
             * refund
             * promotion
             * marketplace
             * external API
             */
            $table->string('source_type', 150)->nullable();
            $table->string('source_id', 191)->nullable();

            $table->string('status', 50)
                ->default('completed');

            $table->text('description')->nullable();

            $table->json('metadata')->nullable();

            $table->timestamps();

            $table->index([
                'website_id',
                'created_at',
            ]);

            $table->index([
                'user_id',
                'created_at',
            ]);

            $table->index([
                'route_key',
                'created_at',
            ]);

            $table->index([
                'source_type',
                'source_id',
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_credit_transactions');
    }
};
