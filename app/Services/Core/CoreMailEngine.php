<?php

namespace App\Services\Core;

use App\Models\Website;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Throwable;

/**
 * ESUBIZ_CORE_UNIVERSAL_MAIL_ENGINE_V1
 *
 * Universal Core email execution boundary.
 *
 * All Core features that actually send or receive email must converge
 * on this engine rather than owning independent SMTP/IMAP transports.
 *
 * Examples:
 * - Direct Email / Compose
 * - CRM
 * - Tickets
 * - Live Chat
 * - Contact Forms
 * - Marketing
 * - HR
 * - Authentication / System
 * - Modules
 *
 * IMPORTANT ARCHITECTURE:
 *
 * 1. CoreMailboxTransportService owns real outbound SMTP transport.
 *
 * 2. CoreMailGateway owns the canonical mailbox ledger.
 *
 * 3. A source feature owns its own business record. It does not own
 *    another physical copy of the email or its attachments.
 *
 * 4. Source visibility controls whether source email activity appears
 *    in the selected mailbox folders. It NEVER controls whether the
 *    actual email transport is allowed to happen.
 *
 * 5. provider_message_id and source_reference are the idempotency
 *    boundary between source activity and mailbox synchronization.
 */
class CoreMailEngine
{
    public function __construct(
        protected CoreMailboxTransportService $transport,
        protected CoreMailGateway $gateway,
    ) {
    }

    /**
     * Send one real email for any Core source.
     *
     * This method transports FIRST and records the canonical Sent
     * mailbox event AFTER successful SMTP delivery.
     *
     * If the Core Admin disabled this source from mailbox recording,
     * SMTP still succeeds normally and mailbox_message_id is null.
     */
    public function send(
        Website $website,
        int $mailboxId,
        array $message
    ): array {
        $source =
            trim(
                (string) (
                    $message['source_type']
                    ?? 'direct_email'
                )
            );

        if ($source === '') {
            $source = 'direct_email';
        }

        $message['source_type'] =
            $source;

        $message['source_label'] =
            trim(
                (string) (
                    $message['source_label']
                    ?? $this->defaultSourceLabel(
                        $source
                    )
                )
            );

        $message['source_reference'] =
            $this->nullableString(
                $message['source_reference']
                    ?? null
            );

        /*
         * ESUBIZ_CORE_MAIL_THREAD_CORRELATION_RUNTIME_V2
         *
         * RFC thread ancestry remains separate from Esubiz
         * source_reference/thread_key business correlation.
         */
        $message['thread_key'] =
            $this->nullableString(
                $message['thread_key']
                    ?? null
            );

        $message['in_reply_to'] =
            $this->normalizeThreadHeader(
                $message['in_reply_to']
                    ?? null,
                998
            );

        $message['message_references'] =
            $this->normalizeThreadHeader(
                $message['message_references']
                    ?? null
            );

        /*
         * Resolve canonical mailbox-owned attachments when a source
         * supplies attachment metadata/IDs rather than transport paths.
         *
         * Source systems must reference these attachments instead of
         * creating another physical copy.
         */
        if (
            empty($message['attachments'])
            && !empty(
                $message['mailbox_message_id']
            )
        ) {
            $message['attachments'] =
                $this->transportAttachments(
                    $mailboxId,
                    (int)
                        $message[
                            'mailbox_message_id'
                        ]
                );
        }

        /*
         * Real SMTP transport is deliberately independent of
         * CoreMailGateway source visibility.
         *
         * Therefore disabling CRM/Tickets/Live Chat/etc. from mailbox
         * recording can NEVER prevent those sources from sending.
         */
        $transportResult =
            $this->transport->send(
                $website,
                $mailboxId,
                $message
            );

        if (
            !($transportResult['success'] ?? false)
        ) {
            throw new RuntimeException(
                'Core mailbox transport did not confirm successful delivery.'
            );
        }

        $providerMessageId =
            $this->nullableString(
                $transportResult[
                    'message_id'
                ]
                    ?? null
            );

        if ($providerMessageId !== null) {
            $message[
                'provider_message_id'
            ] = $providerMessageId;
        }

        $message['sent_at'] =
            $message['sent_at']
                ?? now();

        /*
         * This may intentionally return null when the Core Admin has
         * disabled this source from mailbox recording.
         *
         * That is NOT a send failure. SMTP has already succeeded.
         */
        $mailboxMessageId =
            $this->gateway->recordSent(
                $message,
                $mailboxId
            );

        return [
            'success' => true,

            'sent' => true,

            'mailbox_id' =>
                $mailboxId,

            'mailbox_message_id' =>
                $mailboxMessageId,

            'mailbox_recorded' =>
                $mailboxMessageId !== null,

            'source_type' =>
                $source,

            'source_reference' =>
                $message[
                    'source_reference'
                ],

            'provider_message_id' =>
                $providerMessageId,

            'transport' =>
                $transportResult[
                    'transport'
                ]
                    ?? null,
        ];
    }

    /**
     * ESUBIZ_CORE_UNIVERSAL_DRAFT_SEND_V1
     *
     * Send one existing canonical Draft through the universal
     * transport boundary and convert that SAME mailbox row to Sent.
     *
     * This is used by Direct Email Compose and by any future Core
     * source whose workflow owns a canonical mailbox Draft before
     * transport.
     *
     * It deliberately does NOT call recordSent(), because the
     * canonical mailbox row already exists.
     */
    public function sendDraft(
        Website $website,
        int $mailboxId,
        int $messageId,
        array $message
    ): array {
        $db =
            DB::connection(
                'website_tenant'
            );

        $schema =
            Schema::connection(
                'website_tenant'
            );

        if (
            !$schema->hasTable(
                'email_messages'
            )
        ) {
            throw new RuntimeException(
                'The Core mailbox message ledger is unavailable.'
            );
        }

        $draft =
            $db->table(
                'email_messages'
            )
                ->where(
                    'id',
                    $messageId
                )
                ->where(
                    'email_box_id',
                    $mailboxId
                )
                ->where(
                    'folder',
                    'draft'
                )
                ->first();

        if (!$draft) {
            throw new RuntimeException(
                'The canonical email Draft is unavailable.'
            );
        }

        $source =
            trim(
                (string) (
                    $message['source_type']
                    ?? $draft->source_type
                    ?? 'direct_email'
                )
            );

        if ($source === '') {
            $source = 'direct_email';
        }

        $message['source_type'] =
            $source;

        $message['source_label'] =
            trim(
                (string) (
                    $message['source_label']
                    ?? $draft->source_label
                    ?? $this->defaultSourceLabel(
                        $source
                    )
                )
            );

        $message['source_reference'] =
            $this->nullableString(
                $message['source_reference']
                    ?? $draft->source_reference
                    ?? null
            );

        $message['thread_key'] =
            $this->nullableString(
                $message['thread_key']
                    ?? $draft->thread_key
                    ?? null
            );

        $message['in_reply_to'] =
            $this->normalizeThreadHeader(
                $message['in_reply_to']
                    ?? (
                        property_exists(
                            $draft,
                            'in_reply_to'
                        )
                            ? $draft->in_reply_to
                            : null
                    ),
                998
            );

        $message['message_references'] =
            $this->normalizeThreadHeader(
                $message['message_references']
                    ?? (
                        property_exists(
                            $draft,
                            'message_references'
                        )
                            ? $draft->message_references
                            : null
                    )
            );

        /*
         * The canonical Draft owns its attachments.
         * Resolve those exact files for SMTP; never copy them.
         */
        $message['attachments'] =
            $this->transportAttachments(
                $mailboxId,
                $messageId
            );

        /*
         * Real transport happens before changing the canonical Draft.
         * A transport exception therefore leaves the message in Draft.
         */
        $transportResult =
            $this->transport->send(
                $website,
                $mailboxId,
                $message
            );

        if (
            !($transportResult['success'] ?? false)
        ) {
            throw new RuntimeException(
                'Core mailbox transport did not confirm successful delivery.'
            );
        }

        $providerMessageId =
            $this->nullableString(
                $transportResult[
                    'message_id'
                ]
                    ?? null
            );

        $now = now();

        $update = [
            'folder' =>
                'sent',

            'direction' =>
                'outbound',

            'status' =>
                'sent',

            'sent_at' =>
                $now,

            'updated_at' =>
                $now,
        ];

        if (
            $providerMessageId !== null
            && $schema->hasColumn(
                'email_messages',
                'provider_message_id'
            )
        ) {
            $update[
                'provider_message_id'
            ] = $providerMessageId;
        }

        /*
         * SMTP has already succeeded here.
         *
         * The folder predicate prevents an accidental second
         * Draft-to-Sent transition from being treated as valid.
         */
        $transitioned =
            $db->table(
                'email_messages'
            )
                ->where(
                    'id',
                    $messageId
                )
                ->where(
                    'email_box_id',
                    $mailboxId
                )
                ->where(
                    'folder',
                    'draft'
                )
                ->update(
                    $update
                );

        if (!$transitioned) {
            /*
             * NEVER retry SMTP automatically after this point.
             * Delivery already succeeded; only local ledger
             * reconciliation is required.
             */
            report(
                new RuntimeException(
                    'Core SMTP succeeded but canonical Draft-to-Sent transition failed for email message '
                    . $messageId
                )
            );

            return [
                'success' =>
                    true,

                'sent' =>
                    true,

                'mailbox_id' =>
                    $mailboxId,

                'mailbox_message_id' =>
                    $messageId,

                'mailbox_recorded' =>
                    true,

                'ledger_warning' =>
                    true,

                'source_type' =>
                    $source,

                'source_reference' =>
                    $message[
                        'source_reference'
                    ],

                'provider_message_id' =>
                    $providerMessageId,

                'transport' =>
                    $transportResult[
                        'transport'
                    ]
                        ?? null,
            ];
        }

        return [
            'success' =>
                true,

            'sent' =>
                true,

            'mailbox_id' =>
                $mailboxId,

            'mailbox_message_id' =>
                $messageId,

            'mailbox_recorded' =>
                true,

            'ledger_warning' =>
                false,

            'source_type' =>
                $source,

            'source_reference' =>
                $message[
                    'source_reference'
                ],

            'provider_message_id' =>
                $providerMessageId,

            'transport' =>
                $transportResult[
                    'transport'
                ]
                    ?? null,
        ];
    }


    /**
     * Register one real inbound email for any Core source.
     *
     * The future IMAP synchronization service will call this after
     * retrieving a real mailbox message.
     *
     * If source visibility is disabled, the source can still consume
     * and process the inbound email; it simply is not mirrored into
     * the Core mailbox ledger.
     */
    public function recordInbound(
        int $mailboxId,
        array $message,
        bool $spam = false
    ): array {
        $source =
            trim(
                (string) (
                    $message['source_type']
                    ?? 'direct_email'
                )
            );

        if ($source === '') {
            $source = 'direct_email';
        }

        $message['source_type'] =
            $source;

        $message['source_label'] =
            trim(
                (string) (
                    $message['source_label']
                    ?? $this->defaultSourceLabel(
                        $source
                    )
                )
            );

        $message['source_reference'] =
            $this->nullableString(
                $message['source_reference']
                    ?? null
            );

        $message['thread_key'] =
            $this->nullableString(
                $message['thread_key']
                    ?? null
            );

        $message['in_reply_to'] =
            $this->normalizeThreadHeader(
                $message['in_reply_to']
                    ?? null,
                998
            );

        $message['message_references'] =
            $this->normalizeThreadHeader(
                $message['message_references']
                    ?? null
            );

        $mailboxMessageId =
            $this->gateway->recordInbound(
                $message,
                $mailboxId,
                $spam
            );

        return [
            'success' => true,

            'received' => true,

            'mailbox_id' =>
                $mailboxId,

            'mailbox_message_id' =>
                $mailboxMessageId,

            'mailbox_recorded' =>
                $mailboxMessageId !== null,

            'source_type' =>
                $source,

            'source_reference' =>
                $message[
                    'source_reference'
                ],

            'provider_message_id' =>
                $this->nullableString(
                    $message[
                        'provider_message_id'
                    ]
                        ?? null
                ),

            'spam' =>
                $spam,
        ];
    }

    /**
     * Save a Draft through the same universal source policy.
     *
     * Direct Email uses this immediately. Other sources may use it
     * when their workflow supports drafts.
     */
    public function saveDraft(
        int $mailboxId,
        array $message
    ): ?int {
        $source =
            trim(
                (string) (
                    $message['source_type']
                    ?? 'direct_email'
                )
            );

        if ($source === '') {
            $source = 'direct_email';
        }

        $message['source_type'] =
            $source;

        $message['source_label'] =
            trim(
                (string) (
                    $message['source_label']
                    ?? $this->defaultSourceLabel(
                        $source
                    )
                )
            );

        $message['source_reference'] =
            $this->nullableString(
                $message['source_reference']
                    ?? null
            );

        $message['thread_key'] =
            $this->nullableString(
                $message['thread_key']
                    ?? null
            );

        $message['in_reply_to'] =
            $this->normalizeThreadHeader(
                $message['in_reply_to']
                    ?? null,
                998
            );

        $message['message_references'] =
            $this->normalizeThreadHeader(
                $message['message_references']
                    ?? null
            );

        return $this->gateway->saveDraft(
            $message,
            $mailboxId
        );
    }

    /**
     * Update one existing canonical Draft.
     */
    public function updateDraft(
        int $mailboxId,
        int $messageId,
        array $message
    ): bool {
        $message['source_reference'] =
            $this->nullableString(
                $message['source_reference']
                    ?? null
            );

        $message['thread_key'] =
            $this->nullableString(
                $message['thread_key']
                    ?? null
            );

        $message['in_reply_to'] =
            $this->normalizeThreadHeader(
                $message['in_reply_to']
                    ?? null,
                998
            );

        $message['message_references'] =
            $this->normalizeThreadHeader(
                $message['message_references']
                    ?? null
            );

        return $this->gateway->updateDraft(
            $messageId,
            $message,
            $mailboxId
        );
    }

    /**
     * Convert canonical mailbox attachment metadata to paths accepted
     * by CoreMailboxTransportService.
     */
    protected function transportAttachments(
        int $mailboxId,
        int $messageId
    ): array {
        if (
            !Schema::connection(
                'website_tenant'
            )->hasTable(
                'email_attachments'
            )
        ) {
            return [];
        }

        $rows =
            DB::connection(
                'website_tenant'
            )
                ->table(
                    'email_attachments'
                )
                ->where(
                    'email_message_id',
                    $messageId
                )
                ->orderBy('id')
                ->get([
                    'original_name',
                    'mime_type',
                    'mailbox_locator',
                ]);

        $attachments = [];

        foreach ($rows as $row) {
            $locator =
                ltrim(
                    trim(
                        (string)
                            $row
                                ->mailbox_locator
                    ),
                    '/'
                );

            if ($locator === '') {
                continue;
            }

            /*
             * Use Laravel's configured local disk rather than assuming
             * storage/app/private or another physical root.
             */
            try {
                $path =
                    Storage::disk(
                        'local'
                    )->path(
                        $locator
                    );
            } catch (Throwable $e) {
                report($e);

                throw new RuntimeException(
                    'A mailbox attachment could not be resolved for transport.'
                );
            }

            if (!is_file($path)) {
                throw new RuntimeException(
                    'A mailbox attachment required for this email is unavailable.'
                );
            }

            $attachments[] = [
                'path' =>
                    $path,

                'name' =>
                    (string)
                        $row
                            ->original_name,

                'mime_type' =>
                    $row
                        ->mime_type
                        ?: null,
            ];
        }

        return $attachments;
    }

    protected function nullableString(
        mixed $value
    ): ?string {
        if ($value === null) {
            return null;
        }

        $value =
            trim(
                (string) $value
            );

        return $value !== ''
            ? $value
            : null;
    }

    protected function defaultSourceLabel(
        string $source
    ): string {
        return match ($source) {
            'direct_email' =>
                'Direct Email',

            'core_system' =>
                'Core System',

            'system_auth' =>
                'Authentication',

            'crm' =>
                'CRM',

            'hr' =>
                'HR',

            'tickets' =>
                'Tickets',

            'live_chat' =>
                'Live Chat',

            'contact_form' =>
                'Contact Form',

            'marketing' =>
                'Marketing',

            'module' =>
                'Module',

            'esubiz' =>
                'Esubiz',

            default =>
                ucwords(
                    str_replace(
                        '_',
                        ' ',
                        $source
                    )
                ),
        };
    }

    protected function normalizeThreadHeader(
        mixed $value,
        ?int $maxLength = null
    ): ?string {
        if (is_array($value)) {
            $value =
                implode(
                    ' ',
                    array_filter(
                        array_map(
                            static fn ($item) =>
                                is_scalar($item)
                                    ? trim(
                                        (string) $item
                                    )
                                    : '',
                            $value
                        ),
                        static fn ($item) =>
                            $item !== ''
                    )
                );
        }

        if (
            !is_scalar($value)
            && $value !== null
        ) {
            return null;
        }

        $value =
            trim(
                (string) (
                    $value
                    ?? ''
                )
            );

        if ($value === '') {
            return null;
        }

        /*
         * RFC header values must never contain injected CR/LF.
         */
        $value =
            preg_replace(
                '/[\r\n]+/',
                ' ',
                $value
            );

        $value =
            preg_replace(
                '/\s+/',
                ' ',
                (string) $value
            );

        $value =
            trim(
                (string) $value
            );

        if ($value === '') {
            return null;
        }

        if (
            $maxLength !== null
            && mb_strlen($value) > $maxLength
        ) {
            $value =
                mb_substr(
                    $value,
                    0,
                    $maxLength
                );
        }

        return $value;
    }

}
