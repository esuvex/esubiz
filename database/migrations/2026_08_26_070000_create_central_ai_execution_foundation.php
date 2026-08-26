<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (
            Schema::hasTable('ai_models')
            && !Schema::hasColumn(
                'ai_models',
                'cached_input_cost_per_million'
            )
        ) {
            Schema::table(
                'ai_models',
                function (Blueprint $table) {
                    $table->decimal(
                        'cached_input_cost_per_million',
                        16,
                        6
                    )
                    ->default(0)
                    ->after(
                        'input_cost_per_million'
                    );
                }
            );
        }


        if (
            !Schema::hasTable(
                'ai_usage_logs'
            )
        ) {
            Schema::create(
                'ai_usage_logs',
                function (Blueprint $table) {

                    $table->id();

                    $table->string(
                        'request_uuid',
                        64
                    )->unique();

                    $table->string(
                        'route_key',
                        191
                    )->index();

                    $table->unsignedBigInteger(
                        'ai_provider_id'
                    )->nullable()->index();

                    $table->unsignedBigInteger(
                        'ai_model_id'
                    )->nullable()->index();

                    $table->unsignedBigInteger(
                        'user_id'
                    )->nullable()->index();

                    $table->unsignedBigInteger(
                        'website_id'
                    )->nullable()->index();

                    $table->string(
                        'provider_request_id',
                        191
                    )->nullable()->index();

                    $table->string(
                        'status',
                        32
                    )->default('pending')
                    ->index();

                    $table->boolean(
                        'used_fallback'
                    )->default(false);

                    $table->unsignedBigInteger(
                        'input_tokens'
                    )->default(0);

                    $table->unsignedBigInteger(
                        'cached_input_tokens'
                    )->default(0);

                    $table->unsignedBigInteger(
                        'output_tokens'
                    )->default(0);

                    $table->unsignedBigInteger(
                        'total_tokens'
                    )->default(0);

                    $table->decimal(
                        'provider_cost_usd',
                        18,
                        8
                    )->default(0);

                    $table->decimal(
                        'credits_charged',
                        18,
                        6
                    )->default(0);

                    $table->text(
                        'error_message'
                    )->nullable();

                    $table->json(
                        'metadata'
                    )->nullable();

                    $table->timestamp(
                        'completed_at'
                    )->nullable();

                    $table->timestamps();
                }
            );
        }
    }


    public function down(): void
    {
        Schema::dropIfExists(
            'ai_usage_logs'
        );

        if (
            Schema::hasTable('ai_models')
            && Schema::hasColumn(
                'ai_models',
                'cached_input_cost_per_million'
            )
        ) {
            Schema::table(
                'ai_models',
                function (Blueprint $table) {
                    $table->dropColumn(
                        'cached_input_cost_per_million'
                    );
                }
            );
        }
    }
};
