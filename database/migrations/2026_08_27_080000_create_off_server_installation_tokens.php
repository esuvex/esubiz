<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(
            'off_server_installation_tokens',
            function (Blueprint $table) {

                $table->id();

                $table->uuid('uuid')
                    ->unique();

                $table->unsignedBigInteger(
                    'installation_id'
                )
                    ->index();

                $table->unsignedBigInteger(
                    'website_id'
                )
                    ->index();

                $table->unsignedBigInteger(
                    'api_application_id'
                )
                    ->nullable()
                    ->index();

                /*
                 * Store HASH ONLY.
                 *
                 * The raw bearer token is returned to Core once
                 * after successful installation activation.
                 */
                $table->char(
                    'token_hash',
                    64
                )
                    ->unique();

                $table->json(
                    'scopes'
                )
                    ->nullable();

                $table->string(
                    'status',
                    32
                )
                    ->default('active');

                $table->timestamp(
                    'last_used_at'
                )
                    ->nullable();

                $table->timestamp(
                    'expires_at'
                )
                    ->nullable();

                $table->timestamp(
                    'revoked_at'
                )
                    ->nullable();

                $table->timestamps();

                $table->index(
                    [
                        'installation_id',
                        'status',
                    ],
                    'osit_installation_status_idx'
                );

                $table->index(
                    [
                        'website_id',
                        'status',
                    ],
                    'osit_website_status_idx'
                );
            }
        );
    }


    public function down(): void
    {
        Schema::dropIfExists(
            'off_server_installation_tokens'
        );
    }
};
