<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('ai_commercial_settings')) {
            Schema::create(
                'ai_commercial_settings',
                function (Blueprint $table) {
                    $table->id();

                    $table->string(
                        'key',
                        150
                    )->unique();

                    $table->string(
                        'label',
                        191
                    );

                    $table->decimal(
                        'value',
                        20,
                        8
                    );

                    $table->string(
                        'unit',
                        100
                    )->nullable();

                    $table->boolean(
                        'is_active'
                    )->default(true);

                    $table->json(
                        'metadata'
                    )->nullable();

                    $table->timestamps();
                }
            );
        }
    }

    public function down(): void
    {
        Schema::dropIfExists(
            'ai_commercial_settings'
        );
    }
};
