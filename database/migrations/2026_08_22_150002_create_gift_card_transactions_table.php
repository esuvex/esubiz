<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('gift_card_transactions', function (Blueprint $table) {
            $table->id();

            $table->foreignId('gift_card_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('user_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();

            $table->uuid('uuid')->unique();
            $table->string('reference')->unique();

            $table->enum('type', [
                'issuance',
                'redemption',
                'refund',
                'adjustment',
                'expiry',
            ]);

            $table->decimal('amount', 18, 2);

            $table->decimal('balance_before', 18, 2);
            $table->decimal('balance_after', 18, 2);

            $table->string('currency', 3)->default('NGN');

            $table->string('reference_type')->nullable();
            $table->unsignedBigInteger('reference_id')->nullable();

            $table->string('usage_context')->nullable();

            $table->json('metadata')->nullable();

            $table->timestamps();

            $table->index([
                'reference_type',
                'reference_id',
            ]);

            $table->index([
                'gift_card_id',
                'created_at',
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('gift_card_transactions');
    }
};
