<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * ESUBIZ_OFF_SERVER_CENTRAL_CONNECTION_V1
     *
     * Local Core-side Central Esubiz connection.
     *
     * IMPORTANT:
     *
     * The raw Central bearer token is encrypted by the
     * Core application's APP_KEY through the model cast.
     *
     * Central Esubiz itself stores only the token hash.
     */
    public function up(): void
    {
        if (
            Schema::hasTable(
                'core_central_connections'
            )
        ) {
            return;
        }

        Schema::create(
            'core_central_connections',
            function (Blueprint $table) {
                $table->id();

                $table
                    ->string(
                        'central_url',
                        500
                    );

                $table
                    ->unsignedBigInteger(
                        'website_id'
                    )
                    ->nullable();

                $table
                    ->uuid(
                        'website_uuid'
                    )
                    ->nullable();

                $table
                    ->unsignedBigInteger(
                        'installation_id'
                    )
                    ->nullable();

                $table
                    ->uuid(
                        'installation_uuid'
                    )
                    ->nullable();

                $table
                    ->string(
                        'api_application_id',
                        191
                    )
                    ->nullable();

                /*
                 * Laravel encrypted cast expands ciphertext,
                 * therefore TEXT is intentional.
                 */
                $table
                    ->text(
                        'access_token'
                    );

                $table
                    ->string(
                        'token_type',
                        30
                    )
                    ->default(
                        'Bearer'
                    );

                $table
                    ->json(
                        'scopes'
                    )
                    ->nullable();

                $table
                    ->boolean(
                        'active'
                    )
                    ->default(
                        true
                    )
                    ->index();

                $table
                    ->timestamp(
                        'last_verified_at'
                    )
                    ->nullable();

                $table
                    ->timestamp(
                        'last_used_at'
                    )
                    ->nullable();

                $table->timestamps();

                $table->index(
                    [
                        'website_uuid',
                        'active',
                    ],
                    'core_central_connection_website_active_idx'
                );
            }
        );
    }

    public function down(): void
    {
        Schema::dropIfExists(
            'core_central_connections'
        );
    }
};
