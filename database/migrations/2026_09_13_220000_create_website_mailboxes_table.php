<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * ESUBIZ_CENTRAL_WEBSITE_MAILBOXES_V1
     *
     * Central registry for managed and connected Core mailboxes.
     *
     * SaaS managed mailbox:
     *     subdomain.esubiz.com -> subdomain@esubiz.com
     *
     * Mailbox storage remains part of the Website's shared Core
     * storage allowance. It is not an independent storage pool.
     */
    public function up(): void
    {
        Schema::create('website_mailboxes', function (Blueprint $table) {
            $table->id();

            $table->foreignId('website_id')
                ->constrained('websites')
                ->cascadeOnDelete();

            $table->string('provider', 50)
                ->default('directadmin');

            $table->string('connection_type', 50)
                ->default('managed');

            $table->string('domain', 190);

            $table->string('local_part', 190);

            $table->string('email_address', 254)
                ->unique();

            /*
             * Stored encrypted by the WebsiteMailbox model.
             *
             * This is the mailbox credential, never the DirectAdmin
             * administrator/login-key credential.
             */
            $table->text('password')->nullable();

            $table->string('status', 40)
                ->default('pending');

            $table->unsignedBigInteger('last_storage_bytes')
                ->default(0);

            $table->timestamp('storage_checked_at')
                ->nullable();

            $table->timestamp('provisioned_at')
                ->nullable();

            $table->timestamp('deleted_at_remote')
                ->nullable();

            $table->text('last_error')
                ->nullable();

            $table->timestamps();

            $table->index([
                'website_id',
                'status',
            ]);

            $table->index([
                'provider',
                'connection_type',
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('website_mailboxes');
    }
};
