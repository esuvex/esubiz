<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * ESUBIZ_CORE_UNIFIED_EMAIL_SCHEMA_V1
     *
     * The mailbox owns the actual email and attachment storage.
     *
     * Live Chat, CRM, HR, Tickets, Contact Form, Marketing,
     * modules, Core System and Esubiz are source/proxy systems.
     * They reference/display mailbox email records rather than
     * duplicating the same storage-consuming email.
     */
    public function up(): void
    {
        if (Schema::hasTable('email_boxes')) {
            Schema::table(
                'email_boxes',
                function (Blueprint $table) {
                    if (
                        !Schema::hasColumn(
                            'email_boxes',
                            'connection_type'
                        )
                    ) {
                        $table->string(
                            'connection_type',
                            40
                        )
                            ->default('managed')
                            ->after('username');
                    }

                    if (
                        !Schema::hasColumn(
                            'email_boxes',
                            'is_primary'
                        )
                    ) {
                        $table->boolean('is_primary')
                            ->default(false)
                            ->after('connection_type');
                    }
                }
            );
        }

        if (Schema::hasTable('email_messages')) {
            Schema::table(
                'email_messages',
                function (Blueprint $table) {
                    if (
                        !Schema::hasColumn(
                            'email_messages',
                            'folder'
                        )
                    ) {
                        $table->string('folder', 20)
                            ->default('inbox')
                            ->after('direction');
                    }

                    if (
                        !Schema::hasColumn(
                            'email_messages',
                            'source_type'
                        )
                    ) {
                        $table->string('source_type', 80)
                            ->default('direct_email')
                            ->after('folder');
                    }

                    if (
                        !Schema::hasColumn(
                            'email_messages',
                            'source_label'
                        )
                    ) {
                        $table->string('source_label', 120)
                            ->default('Direct Email')
                            ->after('source_type');
                    }

                    if (
                        !Schema::hasColumn(
                            'email_messages',
                            'source_reference'
                        )
                    ) {
                        $table->string(
                            'source_reference',
                            191
                        )
                            ->nullable()
                            ->after('source_label');
                    }

                    if (
                        !Schema::hasColumn(
                            'email_messages',
                            'provider_message_id'
                        )
                    ) {
                        $table->string(
                            'provider_message_id',
                            255
                        )
                            ->nullable()
                            ->after('source_reference');
                    }

                    if (
                        !Schema::hasColumn(
                            'email_messages',
                            'thread_key'
                        )
                    ) {
                        $table->string('thread_key', 191)
                            ->nullable()
                            ->after('provider_message_id');
                    }

                    if (
                        !Schema::hasColumn(
                            'email_messages',
                            'reply_to'
                        )
                    ) {
                        $table->string('reply_to', 254)
                            ->nullable()
                            ->after('bcc_addresses');
                    }

                    if (
                        !Schema::hasColumn(
                            'email_messages',
                            'body_html'
                        )
                    ) {
                        $table->longText('body_html')
                            ->nullable()
                            ->after('body');
                    }

                    if (
                        !Schema::hasColumn(
                            'email_messages',
                            'has_attachments'
                        )
                    ) {
                        $table->boolean('has_attachments')
                            ->default(false)
                            ->after('body_html');
                    }

                    if (
                        !Schema::hasColumn(
                            'email_messages',
                            'received_at'
                        )
                    ) {
                        $table->timestamp('received_at')
                            ->nullable()
                            ->after('read_at');
                    }

                    if (
                        !Schema::hasColumn(
                            'email_messages',
                            'sent_at'
                        )
                    ) {
                        $table->timestamp('sent_at')
                            ->nullable()
                            ->after('received_at');
                    }

                    if (
                        !Schema::hasColumn(
                            'email_messages',
                            'trashed_at'
                        )
                    ) {
                        $table->timestamp('trashed_at')
                            ->nullable()
                            ->after('sent_at');
                    }
                }
            );
        }

        /*
         * ESUBIZ_CORE_EMAIL_ATTACHMENT_REFERENCE_V1
         *
         * This is mailbox-owned attachment metadata.
         * Proxy systems reference the email/attachment instead of
         * storing another physical copy.
         */
        if (!Schema::hasTable('email_attachments')) {
            Schema::create(
                'email_attachments',
                function (Blueprint $table) {
                    $table->id();

                    $table->foreignId('email_message_id')
                        ->constrained(
                            'email_messages',
                            'id',
                            'fk_email_attachment_message'
                        )
                        ->cascadeOnDelete();

                    $table->string(
                        'provider_attachment_id',
                        255
                    )->nullable();

                    $table->string('original_name');

                    $table->string(
                        'mime_type',
                        191
                    )->nullable();

                    $table->unsignedBigInteger('size_bytes')
                        ->default(0);

                    /*
                     * Identifies the mailbox-owned attachment.
                     * This must not point at a duplicate proxy copy.
                     */
                    $table->text('mailbox_locator')
                        ->nullable();

                    $table->timestamps();

                    $table->index(
                        'email_message_id',
                        'ix_email_attachment_message'
                    );
                }
            );
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('email_attachments');

        if (Schema::hasTable('email_messages')) {
            Schema::table(
                'email_messages',
                function (Blueprint $table) {
                    foreach ([
                        'folder',
                        'source_type',
                        'source_label',
                        'source_reference',
                        'provider_message_id',
                        'thread_key',
                        'reply_to',
                        'body_html',
                        'has_attachments',
                        'received_at',
                        'sent_at',
                        'trashed_at',
                    ] as $column) {
                        if (
                            Schema::hasColumn(
                                'email_messages',
                                $column
                            )
                        ) {
                            $table->dropColumn($column);
                        }
                    }
                }
            );
        }

        if (Schema::hasTable('email_boxes')) {
            Schema::table(
                'email_boxes',
                function (Blueprint $table) {
                    foreach (
                        ['connection_type', 'is_primary']
                        as $column
                    ) {
                        if (
                            Schema::hasColumn(
                                'email_boxes',
                                $column
                            )
                        ) {
                            $table->dropColumn($column);
                        }
                    }
                }
            );
        }
    }
};
