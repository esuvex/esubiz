<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (
            Schema::hasTable(
                'ai_personas'
            )
        ) {
            return;
        }

        Schema::create(
            'ai_personas',
            function (
                Blueprint $table
            ) {

                $table->id();

                $table
                    ->string(
                        'name',
                        100
                    );

                /*
                 * Small official Esubiz persona avatar.
                 *
                 * This is Central-owned persona metadata/media,
                 * not generated customer content.
                 */
                $table
                    ->string(
                        'avatar_path',
                        1000
                    )
                    ->nullable();

                $table
                    ->string(
                        'gender',
                        30
                    )
                    ->nullable();

                $table
                    ->text(
                        'description'
                    )
                    ->nullable();

                $table
                    ->text(
                        'persona_prompt'
                    )
                    ->nullable();

                $table
                    ->boolean(
                        'is_active'
                    )
                    ->default(
                        true
                    );

                $table
                    ->boolean(
                        'is_default'
                    )
                    ->default(
                        false
                    );

                $table
                    ->unsignedInteger(
                        'sort_order'
                    )
                    ->default(
                        0
                    );

                $table->timestamps();

                $table->index([
                    'is_active',
                    'sort_order',
                ]);
            }
        );
    }


    public function down(): void
    {
        Schema::dropIfExists(
            'ai_personas'
        );
    }
};
