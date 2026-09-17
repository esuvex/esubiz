<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /*
     * ESUBIZ_CORE_REAL_MAILBOX_CREDENTIAL_SCHEMA_V1
     *
     * Core email_boxes remains the canonical mailbox registry.
     *
     * credential_secret contains ONLY the Laravel-encrypted mailbox
     * credential. SMTP/IMAP server parameters remain non-secret
     * configuration inside email_boxes.settings.
     *
     * SaaS managed mailboxes do not need to duplicate the centrally
     * owned WebsiteMailbox credential into this column.
     *
     * Off-server Core uses this field for the administrator-supplied
     * real mailbox password after SMTP + IMAP authentication succeeds.
     */

    public function up(): void
    {
        if (!Schema::hasTable('email_boxes')) {
            return;
        }

        if (
            !Schema::hasColumn(
                'email_boxes',
                'credential_secret'
            )
        ) {
            Schema::table(
                'email_boxes',
                function (Blueprint $table): void {
                    /*
                     * Laravel encrypted payloads are longer than the
                     * original password, so TEXT is intentional.
                     */
                    $table->text('credential_secret')
                        ->nullable()
                        ->after('username');
                }
            );
        }
    }

    public function down(): void
    {
        if (
            Schema::hasTable('email_boxes')
            && Schema::hasColumn(
                'email_boxes',
                'credential_secret'
            )
        ) {
            Schema::table(
                'email_boxes',
                function (Blueprint $table): void {
                    $table->dropColumn(
                        'credential_secret'
                    );
                }
            );
        }
    }
};
