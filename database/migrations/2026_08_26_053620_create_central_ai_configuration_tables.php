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
        | Central AI Providers
        |--------------------------------------------------------------------------
        |
        | Provider credentials belong to Esubiz Central only.
        | secret_payload will be encrypted by the application layer.
        |
        */
        Schema::create('ai_providers', function (Blueprint $table) {
            $table->id();

            $table->string('name');
            $table->string('slug')->unique();

            $table->string('driver');
            $table->string('base_url')->nullable();

            $table->longText('secret_payload')->nullable();

            $table->boolean('is_active')->default(true);
            $table->boolean('is_default')->default(false);

            $table->unsignedInteger('priority')->default(100);

            $table->json('metadata')->nullable();

            $table->timestamps();

            $table->index([
                'is_active',
                'priority',
            ]);
        });


        /*
        |--------------------------------------------------------------------------
        | Central AI Models
        |--------------------------------------------------------------------------
        |
        | Models are attached to providers and expose capabilities
        | such as text, image, vision, code and future media types.
        |
        */
        Schema::create('ai_models', function (Blueprint $table) {
            $table->id();

            $table
                ->foreignId('ai_provider_id')
                ->constrained('ai_providers')
                ->cascadeOnDelete();

            $table->string('name');
            $table->string('model_key');

            $table->json('capabilities')->nullable();

            $table->boolean('is_active')->default(true);

            $table->decimal(
                'input_cost_per_million',
                16,
                6
            )->nullable();

            $table->decimal(
                'output_cost_per_million',
                16,
                6
            )->nullable();

            $table->json('metadata')->nullable();

            $table->timestamps();

            $table->unique([
                'ai_provider_id',
                'model_key',
            ]);
        });


        /*
        |--------------------------------------------------------------------------
        | AI Routing Rules
        |--------------------------------------------------------------------------
        |
        | Capabilities/services register what they need.
        | Routing determines which model handles the job.
        |
        | Examples:
        | theme.homepage.text
        | theme.homepage.image
        | app.builder.code
        | live_chat.reply
        | social_media.image
        |
        */
        Schema::create('ai_routing_rules', function (Blueprint $table) {
            $table->id();

            $table->string('route_key')->unique();
            $table->string('label');

            $table
                ->foreignId('ai_model_id')
                ->nullable()
                ->constrained('ai_models')
                ->nullOnDelete();

            $table
                ->foreignId('fallback_model_id')
                ->nullable()
                ->constrained(
                    'ai_models',
                    indexName:
                        'ai_routing_fallback_model_fk'
                )
                ->nullOnDelete();

            $table->boolean('is_active')->default(true);

            $table->unsignedInteger('priority')->default(100);

            $table->json('settings')->nullable();

            $table->timestamps();

            $table->index([
                'is_active',
                'priority',
            ]);
        });


        /*
        |--------------------------------------------------------------------------
        | Central AI Credit Configuration
        |--------------------------------------------------------------------------
        |
        | This defines Esubiz credit charging rules.
        | Actual balances/ledger remain separate from provider costs.
        |
        */
        Schema::create('ai_credit_rules', function (Blueprint $table) {
            $table->id();

            $table->string('key')->unique();
            $table->string('label');

            $table->string('unit')->default('request');

            $table->decimal(
                'credits_per_unit',
                16,
                6
            )->default(1);

            $table->decimal(
                'minimum_credits',
                16,
                6
            )->default(1);

            $table->boolean('is_active')->default(true);

            $table->json('settings')->nullable();

            $table->timestamps();
        });
    }


    public function down(): void
    {
        Schema::dropIfExists(
            'ai_credit_rules'
        );

        Schema::dropIfExists(
            'ai_routing_rules'
        );

        Schema::dropIfExists(
            'ai_models'
        );

        Schema::dropIfExists(
            'ai_providers'
        );
    }
};
