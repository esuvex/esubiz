<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('gift_cards', function (Blueprint $table) {
            $table->id();

            $table->uuid('uuid')->unique();
            $table->string('code')->unique();

            $table->string('name');

            $table->enum('amount_type', [
                'fixed',
                'variable',
            ])->default('fixed');

            $table->decimal('initial_amount', 18, 2);
            $table->decimal('remaining_balance', 18, 2);

            $table->string('currency', 3)->default('NGN');

            $table->unsignedInteger('usage_limit')->nullable();
            $table->unsignedInteger('usage_count')->default(0);

            $table->timestamp('starts_at')->nullable();
            $table->timestamp('expires_at')->nullable();

            $table->boolean('enabled')->default(true);
            $table->boolean('usable_at_checkout')->default(true);
            $table->boolean('usable_for_wallet_funding')->default(true);

            $table->boolean('allow_partial_redemption')->default(true);

            $table->enum('status', [
                'active',
                'disabled',
                'expired',
                'exhausted',
                'cancelled',
            ])->default('active');

            $table->foreignId('issued_to_user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->json('settings')->nullable();
            $table->json('metadata')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index([
                'status',
                'enabled',
            ]);

            $table->index([
                'expires_at',
                'status',
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('gift_cards');
    }
};
