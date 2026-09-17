<?php

namespace App\Services\Core;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use RuntimeException;

/**
 * ESUBIZ_UNIVERSAL_CORE_MAIL_GATEWAY_V1
 *
 * Universal mailbox ledger boundary for Core.
 *
 * Core System, Authentication, CRM, HR, Tickets, Live Chat,
 * Contact Form, Marketing, modules and future functions use this
 * boundary rather than implementing their own mailbox persistence.
 *
 * email_messages is the canonical Core mailbox ledger.
 * Proxy systems may display/reference these records but must not
 * create another storage-owning copy.
 */
class CoreMailGateway
{
    /*
     * ESUBIZ_CORE_MAIL_TRANSPORT_POLICY_V3
     *
     * PHP/server mail is the standard Core email transport.
     *
     * SaaS:
     * - PHP mail consumes the Esubiz server-mail allowance assigned
     *   to the Website by Central.
     * - Central controls the allowance quantity and reset period.
     *
     * Off-server:
     * - PHP mail consumes the customer's own hosting/server limits.
     *
     * Premium SMTP:
     * - is never the default Core transport;
     * - is an Esubiz Central email-credit service;
     * - requires sufficient authoritative Central Email Credits;
     * - Core cannot manipulate the Central credit balance.
     *
     * Transport choice and mailbox-folder retention are independent:
     * a successfully sent email is mailbox-visible by default unless
     * the Core Admin explicitly disables that source.
     */
    public const TRANSPORT_PHP = 'php';

    public const TRANSPORT_PREMIUM_SMTP = 'premium_smtp';

    public const DEFAULT_TRANSPORT = self::TRANSPORT_PHP;

    public const DEFAULT_SOURCE = 'core_system';

    public const SOURCE_LABELS = [
        'direct_email' => 'Direct Email',
        'core_system' => 'Core System',
        'system_auth' => 'System Authentication',
        'crm' => 'CRM',
        'hr' => 'HR',
        'tickets' => 'Tickets',
        'live_chat' => 'Live Chat',
        'contact_form' => 'Contact Form',
        'marketing' => 'Marketing',
        'module' => 'Module',
        'esubiz' => 'Esubiz',
    ];

    public function mailbox(
        ?int $emailBoxId = null
    ): object {
        $db = DB::connection('website_tenant');

        $query = $db
            ->table('email_boxes')
            ->where('status', 'active');

        if ($emailBoxId) {
            $mailbox = (clone $query)
                ->where('id', $emailBoxId)
                ->first();

            if (!$mailbox) {
                throw new RuntimeException(
                    'Selected Core mailbox is unavailable.'
                );
            }

            return $mailbox;
        }

        $mailbox = (clone $query)
            ->orderByDesc('is_primary')
            ->orderBy('id')
            ->first();

        if (!$mailbox) {
            throw new RuntimeException(
                'No active Core mailbox is available.'
            );
        }

        return $mailbox;
    }

    /**
     * Whether this source should be retained/displayed in mailbox
     * folders for this particular mailbox.
     *
     * This policy does NOT disable the actual Core function or stop
     * its email delivery. It controls Core mailbox-folder retention.
     */
    public function sourceVisible(
        object $mailbox,
        string $sourceType
    ): bool {
        $settings = $this->settings($mailbox);

        $visibility =
            (array) (
                $settings['source_visibility']
                ?? []
            );

        /*
         * ESUBIZ_CORE_MAIL_SOURCE_DEFAULT_ON_V2
         *
         * Every Core email source is mailbox-visible by default.
         *
         * The administrator must explicitly disable a source.
         * This also means future Core modules automatically participate
         * without requiring a schema/config migration merely to become
         * visible in mailbox folders.
         */
        if (
            array_key_exists(
                $sourceType,
                $visibility
            )
        ) {
            return (bool) $visibility[$sourceType];
        }

        return true;
    }

    /**
     * ESUBIZ_CORE_MAIL_SOURCE_POLICY_V1
     *
     * Record one canonical outbound mailbox event.
     *
     * Actual transport is deliberately separate from this persistence
     * primitive so legacy/new transports can converge on this gateway
     * without double-sending.
     */
    public function recordSent(
        array $message,
        ?int $emailBoxId = null
    ): ?int {
        $mailbox = $this->mailbox($emailBoxId);

        $source = $this->normalizeSource(
            (string) (
                $message['source_type']
                ?? self::DEFAULT_SOURCE
            )
        );

        if (!$this->sourceVisible($mailbox, $source)) {
            return null;
        }

        return $this->persist(
            $mailbox,
            'outbound',
            'sent',
            $source,
            $message
        );
    }

    public function recordInbound(
        array $message,
        ?int $emailBoxId = null,
        bool $spam = false
    ): ?int {
        $mailbox = $this->mailbox($emailBoxId);

        $source = $this->normalizeSource(
            (string) (
                $message['source_type']
                ?? 'direct_email'
            )
        );

        if (!$this->sourceVisible($mailbox, $source)) {
            return null;
        }

        return $this->persist(
            $mailbox,
            'inbound',
            $spam ? 'spam' : 'inbox',
            $source,
            $message
        );
    }

    public function saveDraft(
        array $message,
        ?int $emailBoxId = null
    ): ?int {
        $mailbox = $this->mailbox($emailBoxId);

        $source = $this->normalizeSource(
            (string) (
                $message['source_type']
                ?? 'direct_email'
            )
        );

        if (!$this->sourceVisible($mailbox, $source)) {
            return null;
        }

        return $this->persist(
            $mailbox,
            'outbound',
            'draft',
            $source,
            $message
        );
    }

    /**
     * ESUBIZ_CORE_EMAIL_DRAFT_EDIT_V2
     *
     * Update one existing canonical draft. The message must
     * belong to the selected active mailbox and must still be
     * in the Draft folder.
     */
    public function updateDraft(
        int $messageId,
        array $message,
        ?int $emailBoxId = null
    ): bool {
        $mailbox =
            $this->mailbox($emailBoxId);

        $source = $this->normalizeSource(
            (string) (
                $message['source_type']
                    ?? 'direct_email'
            )
        );

        if (
            !$this->sourceVisible(
                $mailbox,
                $source
            )
        ) {
            return false;
        }

        $now = now();

        $db =
            DB::connection(
                'website_tenant'
            );

        $update = [
                'source_type' =>
                    $source,

                'source_label' =>
                    trim(
                        (string) (
                            $message['source_label']
                                ?? self::SOURCE_LABELS[
                                    $source
                                ]
                                ?? ucwords(
                                    str_replace(
                                        '_',
                                        ' ',
                                        $source
                                    )
                                )
                        )
                    ),

                'from_address' =>
                    trim(
                        (string) (
                            $message['from']
                                ?? $mailbox->email
                        )
                    ),

                'to_addresses' =>
                    $this->addresses(
                        $message['to']
                            ?? []
                    ),

                'cc_addresses' =>
                    $this->nullableAddresses(
                        $message['cc']
                            ?? []
                    ),

                'bcc_addresses' =>
                    $this->nullableAddresses(
                        $message['bcc']
                            ?? []
                    ),

                'reply_to' =>
                    $message['reply_to']
                        ?? null,

                'subject' =>
                    $message['subject']
                        ?? null,

                'body' =>
                    $message['body']
                        ?? null,

                'body_html' =>
                    $message['body_html']
                        ?? null,

                'updated_at' => $now,
        ];

        $schema =
            $db->getSchemaBuilder();

        if (
            $schema->hasColumn(
                'email_messages',
                'in_reply_to'
            )
        ) {
            $update['in_reply_to'] =
                $message['in_reply_to']
                    ?? null;
        }

        if (
            $schema->hasColumn(
                'email_messages',
                'message_references'
            )
        ) {
            $update[
                'message_references'
            ] =
                $message['message_references']
                    ?? null;
        }

        return $db
            ->table('email_messages')
            ->where('id', $messageId)
            ->where(
                'email_box_id',
                (int) $mailbox->id
            )
            ->where('folder', 'draft')
            ->update(
                $update
            ) > 0;
    }

    /**
     * Move to Trash does not free storage.
     */
    public function moveToTrash(
        int $messageId,
        ?int $emailBoxId = null
    ): bool {
        $mailbox = $this->mailbox($emailBoxId);

        return DB::connection('website_tenant')
            ->table('email_messages')
            ->where('id', $messageId)
            ->where(
                'email_box_id',
                (int) $mailbox->id
            )
            ->update([
                'folder' => 'trash',
                'trashed_at' => now(),
                'updated_at' => now(),
            ]) > 0;
    }

    /**
     * ESUBIZ_CORE_MAIL_PERMANENT_DELETE_V1
     *
     * Permanent deletion removes the canonical Core record and its
     * mailbox-owned attachment metadata. Physical provider/maildir
     * purge is performed by the mailbox transport/provider layer.
     */
    public function permanentlyDelete(
        int $messageId,
        ?int $emailBoxId = null
    ): bool {
        $mailbox = $this->mailbox($emailBoxId);

        $db = DB::connection('website_tenant');

        return $db->transaction(
            function () use (
                $db,
                $mailbox,
                $messageId
            ) {
                $message = $db
                    ->table('email_messages')
                    ->where('id', $messageId)
                    ->where(
                        'email_box_id',
                        (int) $mailbox->id
                    )
                    ->first();

                if (!$message) {
                    return false;
                }

                if (
                    Schema::connection('website_tenant')
                        ->hasTable('email_attachments')
                ) {
                    $db->table('email_attachments')
                        ->where(
                            'email_message_id',
                            $messageId
                        )
                        ->delete();
                }

                return $db
                    ->table('email_messages')
                    ->where('id', $messageId)
                    ->delete() > 0;
            }
        );
    }

    /**
     * ESUBIZ_CORE_MAIL_RETENTION_POLICY_V1
     *
     * Returns the configured automatic permanent-delete age for a
     * folder. Null/0 means keep indefinitely.
     */
    public function retentionDays(
        object $mailbox,
        string $folder
    ): ?int {
        $settings = $this->settings($mailbox);

        $retention =
            (array) (
                $settings['retention_days']
                ?? []
            );

        $days = (int) (
            $retention[$folder]
            ?? 0
        );

        return $days > 0 ? $days : null;
    }

    public function settings(object $mailbox): array
    {
        $raw = $mailbox->settings ?? null;

        if (is_array($raw)) {
            return $raw;
        }

        if (!is_string($raw) || trim($raw) === '') {
            return [];
        }

        $decoded = json_decode(
            $raw,
            true
        );

        return is_array($decoded)
            ? $decoded
            : [];
    }

    private function persist(
        object $mailbox,
        string $direction,
        string $folder,
        string $source,
        array $message
    ): int {
        $db = DB::connection('website_tenant');

        /*
         * Provider/source identifiers provide the universal
         * idempotency boundary. A proxy and mailbox synchronization
         * must resolve to the same canonical email record.
         */
        $providerMessageId = trim(
            (string) (
                $message['provider_message_id']
                ?? ''
            )
        );

        $sourceReference = trim(
            (string) (
                $message['source_reference']
                ?? ''
            )
        );

        if ($providerMessageId !== '') {
            $existing = $db
                ->table('email_messages')
                ->where(
                    'email_box_id',
                    (int) $mailbox->id
                )
                ->where(
                    'provider_message_id',
                    $providerMessageId
                )
                ->first();

            if ($existing) {
                return (int) $existing->id;
            }
        }

        if ($sourceReference !== '') {
            $existing = $db
                ->table('email_messages')
                ->where(
                    'email_box_id',
                    (int) $mailbox->id
                )
                ->where(
                    'source_type',
                    $source
                )
                ->where(
                    'source_reference',
                    $sourceReference
                )
                ->first();

            if ($existing) {
                return (int) $existing->id;
            }
        }

        $now = now();

        $payload = [
                'email_box_id' =>
                    (int) $mailbox->id,

                'direction' => $direction,
                'folder' => $folder,

                'source_type' => $source,

                'source_label' =>
                    trim(
                        (string) (
                            $message['source_label']
                            ?? self::SOURCE_LABELS[$source]
                            ?? ucwords(
                                str_replace(
                                    '_',
                                    ' ',
                                    $source
                                )
                            )
                        )
                    ),

                'source_reference' =>
                    $sourceReference !== ''
                        ? $sourceReference
                        : null,

                'provider_message_id' =>
                    $providerMessageId !== ''
                        ? $providerMessageId
                        : null,

                'thread_key' =>
                    $message['thread_key']
                    ?? null,

                /*
                 * ESUBIZ_CORE_MAIL_THREAD_CORRELATION_PERSISTENCE_V2
                 *
                 * The payload is filtered below for tenants that have
                 * not executed the 7G1 migration yet.
                 */
                'in_reply_to' =>
                    $message['in_reply_to']
                    ?? null,

                'message_references' =>
                    $message['message_references']
                    ?? null,

                'from_address' =>
                    trim(
                        (string) (
                            $message['from']
                            ?? $mailbox->email
                        )
                    ),

                'to_addresses' =>
                    $this->addresses(
                        $message['to']
                        ?? []
                    ),

                'cc_addresses' =>
                    $this->nullableAddresses(
                        $message['cc']
                        ?? []
                    ),

                'bcc_addresses' =>
                    $this->nullableAddresses(
                        $message['bcc']
                        ?? []
                    ),

                'reply_to' =>
                    $message['reply_to']
                    ?? null,

                'subject' =>
                    $message['subject']
                    ?? null,

                'body' =>
                    $message['body']
                    ?? null,

                'body_html' =>
                    $message['body_html']
                    ?? null,

                'has_attachments' =>
                    !empty(
                        $message['attachments']
                        ?? []
                    ),

                'status' =>
                    $folder === 'draft'
                        ? 'draft'
                        : (
                            $direction === 'outbound'
                                ? 'sent'
                                : 'received'
                        ),

                'read_at' =>
                    $message['read_at']
                    ?? null,

                'received_at' =>
                    $direction === 'inbound'
                        ? (
                            $message['received_at']
                            ?? $now
                        )
                        : null,

                'sent_at' =>
                    $direction === 'outbound'
                    && $folder === 'sent'
                        ? (
                            $message['sent_at']
                            ?? $now
                        )
                        : null,

                'trashed_at' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ];

        $schema =
            $db->getSchemaBuilder();

        if (
            !$schema->hasColumn(
                'email_messages',
                'in_reply_to'
            )
        ) {
            unset(
                $payload['in_reply_to']
            );
        }

        if (
            !$schema->hasColumn(
                'email_messages',
                'message_references'
            )
        ) {
            unset(
                $payload[
                    'message_references'
                ]
            );
        }

        return (int) $db
            ->table('email_messages')
            ->insertGetId(
                $payload
            );
    }

    private function normalizeSource(
        string $source
    ): string {
        $source = strtolower(
            trim($source)
        );

        $source = preg_replace(
            '/[^a-z0-9_]+/',
            '_',
            $source
        ) ?: self::DEFAULT_SOURCE;

        return trim($source, '_')
            ?: self::DEFAULT_SOURCE;
    }

    private function addresses(mixed $addresses): string
    {
        if (is_string($addresses)) {
            return trim($addresses);
        }

        return implode(
            ',',
            array_values(
                array_filter(
                    array_map(
                        fn ($value) =>
                            trim((string) $value),
                        (array) $addresses
                    )
                )
            )
        );
    }

    private function nullableAddresses(
        mixed $addresses
    ): ?string {
        $value = $this->addresses(
            $addresses
        );

        return $value !== ''
            ? $value
            : null;
    }
}
