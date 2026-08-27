<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(
            'off_server_installations',
            function (Blueprint $table) {

                $table->id();

                /*
                 * Public-safe installation record identity.
                 */
                $table->uuid('uuid')
                    ->unique();


                /*
                 * One Central license registration owns the
                 * installation lifecycle.
                 */
                $table->unsignedBigInteger(
                    'license_registration_id'
                )
                    ->index();


                /*
                 * Permanent Central website identity.
                 */
                $table->unsignedBigInteger(
                    'website_id'
                )
                    ->index();


                /*
                 * Installer-generated UUID supplied by Core.
                 *
                 * A reinstall/server migration creates a new
                 * installation UUID while preserving website_id.
                 */
                $table->uuid(
                    'installation_uuid'
                )
                    ->unique();


                /*
                 * Permanent licensed domain.
                 */
                $table->string(
                    'registered_domain',
                    255
                );

                $table->char(
                    'domain_hash',
                    64
                );


                /*
                 * ApiApplication binding is attached in
                 * Checkpoint 4 command 2.
                 */
                $table->unsignedBigInteger(
                    'api_application_id'
                )
                    ->nullable()
                    ->index();


                /*
                 * current     = installation currently authorized
                 * superseded  = replaced by successful reinstall
                 * revoked     = explicitly disabled
                 */
                $table->string(
                    'status',
                    32
                )
                    ->default('current');


                $table->boolean(
                    'is_current'
                )
                    ->default(true);


                /*
                 * Allows audit of same-domain server migration.
                 */
                $table->unsignedBigInteger(
                    'supersedes_installation_id'
                )
                    ->nullable()
                    ->index();

                $table->timestamp(
                    'activated_at'
                )
                    ->nullable();

                $table->timestamp(
                    'last_seen_at'
                )
                    ->nullable();

                $table->timestamp(
                    'superseded_at'
                )
                    ->nullable();

                $table->timestamp(
                    'revoked_at'
                )
                    ->nullable();


                /*
                 * Core/PHP version, server fingerprint metadata,
                 * etc. Never store raw API secrets here.
                 */
                $table->json(
                    'metadata'
                )
                    ->nullable();


                $table->timestamps();


                /*
                 * A website may have historical installations,
                 * but only one should remain current.
                 */
                $table->index(
                    [
                        'website_id',
                        'is_current',
                        'status',
                    ],
                    'osi_website_current_status_idx'
                );

                $table->index(
                    [
                        'license_registration_id',
                        'is_current',
                    ],
                    'osi_license_current_idx'
                );

                $table->index(
                    [
                        'domain_hash',
                        'is_current',
                    ],
                    'osi_domain_current_idx'
                );
            }
        );
    }


    public function down(): void
    {
        Schema::dropIfExists(
            'off_server_installations'
        );
    }
};
