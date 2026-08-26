<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('credit_transactions')) {

            Schema::create(
                'credit_transactions',
                function (Blueprint $table) {

                    $table->id();

                    $table->foreignId('website_id')
                        ->constrained('websites')
                        ->cascadeOnDelete();

                    $table->unsignedBigInteger('user_id')
                        ->nullable()
                        ->index();

                    $table->unsignedBigInteger('workspace_id')
                        ->nullable()
                        ->index();

                    /*
                     * ai_credits
                     * sms_credits
                     * email_credits
                     * whatsapp_credits
                     * future credit types
                     */
                    $table->string(
                        'credit_type',
                        100
                    )->index();

                    $table->uuid('uuid')->unique();

                    /*
                     * Idempotency protection.
                     */
                    $table->string(
                        'request_key',
                        191
                    )->unique();

                    $table->string(
                        'reference',
                        100
                    )->unique();

                    $table->enum(
                        'direction',
                        [
                            'credit',
                            'debit',
                            'refund',
                        ]
                    );

                    $table->string(
                        'type',
                        100
                    );

                    $table->decimal(
                        'credits',
                        18,
                        6
                    );

                    $table->decimal(
                        'balance_before',
                        18,
                        6
                    );

                    $table->decimal(
                        'balance_after',
                        18,
                        6
                    );

                    $table->string(
                        'source_type',
                        150
                    )->nullable();

                    $table->string(
                        'source_id',
                        191
                    )->nullable();

                    $table->string(
                        'status',
                        50
                    )->default('completed');

                    $table->text(
                        'description'
                    )->nullable();

                    $table->json(
                        'metadata'
                    )->nullable();

                    $table->timestamps();

                    $table->index([
                        'website_id',
                        'credit_type',
                        'created_at',
                    ]);

                    $table->index([
                        'source_type',
                        'source_id',
                    ]);
                }
            );
        }
    }

    public function down(): void
    {
        Schema::dropIfExists(
            'credit_transactions'
        );
    }
};
