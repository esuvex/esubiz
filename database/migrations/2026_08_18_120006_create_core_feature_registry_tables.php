<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('core_features', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('key')->unique();
            $table->string('name');
            $table->string('category');
            $table->text('description')->nullable();
            $table->enum('type', [
                'feature',
                'service',
                'communication',
                'resource',
                'integration',
            ])->default('feature');
            $table->boolean('is_core')->default(true);
            $table->boolean('is_active')->default(true);
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['category', 'is_active']);
        });

        Schema::create('core_feature_limits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('core_feature_id')
                ->constrained('core_features')
                ->cascadeOnDelete();

            $table->string('limit_key');
            $table->string('name');
            $table->enum('value_type', [
                'quantity',
                'boolean',
                'storage',
                'bandwidth',
                'credits',
                'unlimited',
            ])->default('quantity');

            $table->unsignedBigInteger('default_value')->nullable();
            $table->string('unit')->nullable();
            $table->boolean('is_unlimited')->default(false);
            $table->boolean('is_active')->default(true);
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['core_feature_id', 'limit_key']);
            $table->index(['limit_key', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('core_feature_limits');
        Schema::dropIfExists('core_features');
    }
};
