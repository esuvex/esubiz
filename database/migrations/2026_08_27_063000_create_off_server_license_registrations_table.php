<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(
            'off_server_license_registrations',
            function (Blueprint $table) {

                $table->id();

                /*
                 * Public-safe registration identity.
                 */
                $table->uuid('uuid')->unique();


                /*
                 * Central owner and registered website.
                 *
                 * website_id may initially be null while a newly
                 * purchased licence is waiting for first activation.
                 */
                $table->unsignedBigInteger('user_id');

                $table->unsignedBigInteger('website_id')
                    ->nullable()
                    ->unique();


                /*
                 * Link to the canonical licence record without
                 * forcing this foundation to duplicate the existing
                 * licence engine.
                 */
                $table->string('license_key', 255)
                    ->unique();


                /*
                 * PERMANENT DOMAIN LOCK
                 *
                 * Once registered_domain is assigned, this licence
                 * cannot be activated for another domain.
                 *
                 * A second domain requires another Core licence.
                 */
                $table->string('registered_domain', 255)
                    ->nullable();


                /*
                 * SHA-256 normalized domain fingerprint provides an
                 * indexed exact identity while keeping the readable
                 * canonical domain available for administration.
                 */
                $table->char('domain_hash', 64)
                    ->nullable();


                /*
                 * Installation identity is separate from domain.
                 *
                 * Server migration/reinstallation on the SAME domain
                 * can later rotate/re-authorize installation identity
                 * without changing the licence's permanent domain.
                 */
                $table->uuid('installation_uuid')
                    ->nullable();


                /*
                 * API application bound to this licensed installation.
                 */
                $table->unsignedBigInteger('api_application_id')
                    ->nullable();


                $table->string('status', 32)
                    ->default('pending');


                $table->timestamp('activated_at')
                    ->nullable();

                $table->timestamp('last_verified_at')
                    ->nullable();

                $table->timestamp('revoked_at')
                    ->nullable();


                /*
                 * Useful operational metadata:
                 * Core version, PHP version, installation information,
                 * verification information, etc.
                 *
                 * Never store API secrets here.
                 */
                $table->json('metadata')
                    ->nullable();


                $table->timestamps();


                $table->index(
                    [
                        'user_id',
                        'status',
                    ],
                    'osl_user_status_idx'
                );

                $table->index(
                    [
                        'domain_hash',
                        'status',
                    ],
                    'osl_domain_status_idx'
                );

                $table->index(
                    'api_application_id',
                    'osl_api_app_idx'
                );
            }
        );
    }


    public function down(): void
    {
        Schema::dropIfExists(
            'off_server_license_registrations'
        );
    }
};
