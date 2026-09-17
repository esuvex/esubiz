<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /*
     * ESUBIZ_CORE_EMAIL_THREAD_CORRELATION_SCHEMA_V1
     *
     * provider_message_id remains the canonical RFC Message-ID field.
     *
     * These additional fields preserve the standard email threading
     * headers required to correlate real replies across Direct Email,
     * Tickets, CRM, Live Chat, Contact Forms, Marketing, modules and
     * other Core email sources.
     *
     * source_reference and thread_key remain Esubiz-owned business
     * correlation identifiers and are intentionally separate from
     * RFC email headers.
     */

    public function up(): void
    {
        if (
            !Schema::connection(
                'website_tenant'
            )->hasTable(
                'email_messages'
            )
        ) {
            return;
        }

        if (
            !Schema::connection(
                'website_tenant'
            )->hasColumn(
                'email_messages',
                'in_reply_to'
            )
        ) {
            Schema::connection(
                'website_tenant'
            )->table(
                'email_messages',
                function (Blueprint $table): void {
                    /*
                     * RFC In-Reply-To normally contains one Message-ID.
                     * 998 characters gives sufficient room without
                     * pretending it is an Esubiz source identifier.
                     */
                    $table->string(
                        'in_reply_to',
                        998
                    )
                        ->nullable()
                        ->after(
                            'thread_key'
                        );
                }
            );
        }

        if (
            !Schema::connection(
                'website_tenant'
            )->hasColumn(
                'email_messages',
                'message_references'
            )
        ) {
            Schema::connection(
                'website_tenant'
            )->table(
                'email_messages',
                function (Blueprint $table): void {
                    /*
                     * RFC References can contain a chain of Message-IDs
                     * and can exceed normal VARCHAR lengths.
                     *
                     * Use a TEXT column and avoid the ambiguous/reserved
                     * generic column name "references".
                     */
                    $table->text(
                        'message_references'
                    )
                        ->nullable()
                        ->after(
                            'in_reply_to'
                        );
                }
            );
        }
    }

    public function down(): void
    {
        if (
            !Schema::connection(
                'website_tenant'
            )->hasTable(
                'email_messages'
            )
        ) {
            return;
        }

        $drop = [];

        if (
            Schema::connection(
                'website_tenant'
            )->hasColumn(
                'email_messages',
                'message_references'
            )
        ) {
            $drop[] =
                'message_references';
        }

        if (
            Schema::connection(
                'website_tenant'
            )->hasColumn(
                'email_messages',
                'in_reply_to'
            )
        ) {
            $drop[] =
                'in_reply_to';
        }

        if ($drop !== []) {
            Schema::connection(
                'website_tenant'
            )->table(
                'email_messages',
                function (
                    Blueprint $table
                ) use ($drop): void {
                    $table->dropColumn(
                        $drop
                    );
                }
            );
        }
    }
};
