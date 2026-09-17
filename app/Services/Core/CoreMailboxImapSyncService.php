<?php

namespace App\Services\Core;

use App\Models\Website;
use App\Models\WebsiteMailbox;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use RuntimeException;
use Throwable;
use Webklex\PHPIMAP\ClientManager;

/**
 * ESUBIZ_CORE_REAL_IMAP_SYNC_V1
 *
 * Universal inbound email synchronization service.
 *
 * Real mailbox:
 *      IMAP
 *        ↓
 * CoreMailboxImapSyncService
 *        ↓
 * CoreMailEngine::recordInbound()
 *        ↓
 * canonical email_messages Inbox / Spam
 *        ↓
 * Direct Email / Tickets / CRM / Live Chat / other sources
 *
 * Source systems never own another physical mailbox copy.
 *
 * provider_message_id is the primary inbound idempotency boundary,
 * while source_reference/thread_key allow Core sources to associate
 * the canonical email with their own business records.
 */
class CoreMailboxImapSyncService
{
    public function __construct(
        protected CoreMailEngine $engine,
        protected CoreMailboxAttachmentService $attachments,
        protected CoreMailInboundSourceRouter $sourceRouter,
    ) {
    }

    /**
     * Synchronize one selected real mailbox.
     *
     * No mailbox password is accepted from the caller.
     * Credentials are resolved from the authenticated Core mailbox
     * configuration already stored by Esubiz.
     */
    public function sync(
        Website $website,
        int $mailboxId,
        int $limit = 50
    ): array {
        $limit =
            max(
                1,
                min(
                    $limit,
                    200
                )
            );

        $mailbox =
            $this->mailbox(
                $mailboxId
            );

        $connection =
            $this->resolveConnection(
                $website,
                $mailbox
            );

        $client =
            $this->client(
                $connection
            );

        $result = [
            'success' =>
                true,

            'mailbox_id' =>
                $mailboxId,

            'folders' => [],

            'processed' =>
                0,

            'recorded' =>
                0,

            'existing' =>
                0,

            'failed' =>
                0,
        ];

        try {
            $client->connect();

            if (!$client->isConnected()) {
                throw new RuntimeException(
                    'IMAP connection was not established.'
                );
            }

            /*
             * Inbox is mandatory.
             */
            $folders = [
                [
                    'name' =>
                        'INBOX',

                    'spam' =>
                        false,
                ],
            ];

            /*
             * Spam folder names vary by provider.
             * Resolve the first available conventional folder.
             */
            foreach (
                [
                    'Spam',
                    'Junk',
                    'Junk E-mail',
                    'Junk Email',
                ]
                as $spamName
            ) {
                try {
                    $spamFolder =
                        $client->getFolder(
                            $spamName
                        );

                    if ($spamFolder) {
                        $folders[] = [
                            'name' =>
                                $spamName,

                            'spam' =>
                                true,
                        ];

                        break;
                    }
                } catch (Throwable $e) {
                    /*
                     * Missing provider-specific Spam folder is not
                     * itself a synchronization failure.
                     */
                }
            }

            foreach (
                $folders
                as $folderConfig
            ) {
                $folderName =
                    (string)
                        $folderConfig[
                            'name'
                        ];

                $spam =
                    (bool)
                        $folderConfig[
                            'spam'
                        ];

                try {
                    $folder =
                        $client->getFolder(
                            $folderName
                        );

                    if (!$folder) {
                        continue;
                    }

                    $folderResult =
                        $this->syncFolder(
                            $website,
                            $mailbox,
                            $folder,
                            $spam,
                            $limit
                        );

                    $result['folders'][] =
                        $folderResult;

                    $result['processed'] +=
                        $folderResult[
                            'processed'
                        ];

                    $result['recorded'] +=
                        $folderResult[
                            'recorded'
                        ];

                    $result['existing'] +=
                        $folderResult[
                            'existing'
                        ];

                    $result['failed'] +=
                        $folderResult[
                            'failed'
                        ];
                } catch (Throwable $e) {
                    report($e);

                    $result['failed']++;

                    $result['folders'][] = [
                        'folder' =>
                            $folderName,

                        'spam' =>
                            $spam,

                        'processed' =>
                            0,

                        'recorded' =>
                            0,

                        'existing' =>
                            0,

                        'failed' =>
                            1,
                    ];
                }
            }
        } finally {
            try {
                if (
                    $client->isConnected()
                ) {
                    $client->disconnect();
                }
            } catch (Throwable $e) {
                report($e);
            }
        }

        return $result;
    }

    /**
     * Synchronize one remote IMAP folder.
     */
    protected function syncFolder(
        Website $website,
        object $mailbox,
        object $folder,
        bool $spam,
        int $limit
    ): array {
        $folderIdentity =
            $this->folderIdentity(
                $folder,
                $spam
            );

        $result = [
            'folder' =>
                method_exists(
                    $folder,
                    'name'
                )
                    ? (string)
                        $folder->name()
                    : (
                        property_exists(
                            $folder,
                            'name'
                        )
                            ? (string)
                                $folder->name
                            : (
                                $spam
                                    ? 'Spam'
                                    : 'INBOX'
                            )
                    ),

            'spam' =>
                $spam,

            'processed' =>
                0,

            'recorded' =>
                0,

            'existing' =>
                0,

            'failed' =>
                0,
        ];

        /*
         * Fetch recent messages rather than marking/fetching only
         * unread mail. Read state on the remote server must not be
         * used as our idempotency boundary.
         */
        $messages =
            $folder
                ->messages()
                ->all()
                ->limit(
                    $limit
                )
                ->get();

        foreach (
            $messages
            as $remote
        ) {
            $result['processed']++;

            try {
                $normalized =
                    $this->normalizeMessage(
                        $remote,
                        $folderIdentity
                    );

                $providerMessageId =
                    $normalized[
                        'provider_message_id'
                    ];

                /*
                 * ESUBIZ_CORE_IMAP_ATTACHMENT_RETRY_V1
                 *
                 * An existing canonical message is not a hard skip.
                 * Attachment ingestion may have failed after the
                 * message row was originally persisted.
                 */
                $existingCanonicalMessageId =
                    $providerMessageId !== null
                        ? $this->recordedMessageId(
                            (int)
                                $mailbox->id,
                            $providerMessageId
                        )
                        : null;

                /*
                 * ESUBIZ_CORE_IMAP_SOURCE_ROUTING_V1
                 *
                 * Identify the business source BEFORE optional mailbox
                 * recording. This is essential: disabling a source from
                 * normal mailbox visibility must never disable that
                 * source's actual inbound functionality.
                 */
                $normalized =
                    $this->sourceRouter
                        ->identify(
                            $website,
                            (int)
                                $mailbox->id,
                            $normalized
                        );

                if (
                    $existingCanonicalMessageId !== null
                ) {
                    $recorded = [
                        'success' => true,
                        'received' => true,

                        'mailbox_id' =>
                            (int) $mailbox->id,

                        'mailbox_message_id' =>
                            $existingCanonicalMessageId,

                        'mailbox_recorded' =>
                            true,

                        'source_type' =>
                            $normalized[
                                'source_type'
                            ]
                                ?? 'direct_email',

                        'source_reference' =>
                            $normalized[
                                'source_reference'
                            ]
                                ?? null,

                        'provider_message_id' =>
                            $providerMessageId,

                        'spam' =>
                            $spam,
                    ];
                } else {
                    $recorded =
                        $this->engine
                            ->recordInbound(
                                (int)
                                    $mailbox->id,
                                $normalized,
                                $spam
                            );
                }

                $canonicalMessageId =
                    (
                        $recorded[
                            'mailbox_recorded'
                        ]
                            ?? false
                    )
                        ? (int) (
                            $recorded[
                                'mailbox_message_id'
                            ]
                                ?? 0
                        )
                        : null;

                /*
                 * Source dispatch is deliberately outside the mailbox
                 * visibility decision.
                 *
                 * A disabled Tickets/CRM/Live Chat mailbox mirror must
                 * still receive its actual inbound event.
                 */
                $this->sourceRouter
                    ->dispatch(
                        $website,
                        (int)
                            $mailbox->id,
                        $normalized,
                        $canonicalMessageId
                            ?: null
                    );

                if (
                    $recorded[
                        'mailbox_recorded'
                    ]
                        ?? false
                ) {
                    if ($canonicalMessageId > 0) {
                        foreach (
                            $normalized[
                                'remote_attachments'
                            ]
                                ?? []
                            as $remoteAttachment
                        ) {
                            $attachment =
                                $this->normalizeRemoteAttachment(
                                    $remoteAttachment
                                );

                            if (
                                $attachment[
                                    'bytes'
                                ]
                                    === null
                            ) {
                                continue;
                            }

                            $this->attachments
                                ->storeInbound(
                                    $website,
                                    (int)
                                        $mailbox->id,
                                    $canonicalMessageId,
                                    $attachment[
                                        'name'
                                    ],
                                    $attachment[
                                        'mime_type'
                                    ],
                                    $attachment[
                                        'bytes'
                                    ],
                                    $attachment[
                                        'provider_attachment_id'
                                    ]
                                );
                        }
                    }

                    if (
                        $existingCanonicalMessageId !== null
                    ) {
                        $result['existing']++;
                    } else {
                        $result['recorded']++;
                    }
                } else {
                    /*
                     * Source visibility may intentionally suppress
                     * mailbox recording without suppressing source
                     * processing.
                     */
                    $result['existing']++;
                }
            } catch (Throwable $e) {
                report($e);

                $result['failed']++;
            }
        }

        return $result;
    }

    /**
     * Normalize one Webklex IMAP message into the universal
     * CoreMailEngine inbound contract.
     */
    protected function normalizeMessage(
        object $remote,
        string $folderIdentity
    ): array {
        /*
         * ESUBIZ_CORE_IMAP_THREAD_HEADERS_V2
         */
        $messageId =
            $this->attributeString(
                $remote,
                'getMessageId'
            );

        $inReplyTo =
            $this->attributeString(
                $remote,
                'getInReplyTo'
            );

        $messageReferences =
            $this->attributeString(
                $remote,
                'getReferences'
            );

        /*
         * ESUBIZ_CORE_IMAP_FOLDER_SAFE_FALLBACK_ID_V2
         *
         * RFC Message-ID remains authoritative whenever available.
         * IMAP UID is only a fallback and is scoped to its folder.
         */
        if ($messageId === null) {
            $uid =
                $this->attributeString(
                    $remote,
                    'getUid'
                );

            if ($uid !== null) {
                $folderIdentity =
                    trim(
                        $folderIdentity
                    );

                if ($folderIdentity === '') {
                    $folderIdentity =
                        'unknown-folder';
                }

                $messageId =
                    'imap-folder-uid:'
                    . hash(
                        'sha256',
                        strtolower(
                            $folderIdentity
                        )
                    )
                    . ':'
                    . $uid;
            }
        }

        $from =
            $this->addresses(
                $remote,
                'getFrom'
            );

        $to =
            $this->addresses(
                $remote,
                'getTo'
            );

        $cc =
            $this->addresses(
                $remote,
                'getCc'
            );

        $replyTo =
            $this->addresses(
                $remote,
                'getReplyTo'
            );

        $subject =
            $this->attributeString(
                $remote,
                'getSubject'
            );

        $textBody =
            $this->body(
                $remote,
                'getTextBody'
            );

        $htmlBody =
            $this->body(
                $remote,
                'getHTMLBody'
            );

        $receivedAt =
            null;

        try {
            $date =
                $this->invokeRemoteGetter(
                    $remote,
                    'getDate'
                );

            if (
                is_object($date)
                && method_exists(
                    $date,
                    'first'
                )
            ) {
                $date =
                    $date->first();
            }

            if (
                is_object($date)
                && method_exists(
                    $date,
                    'toDateTime'
                )
            ) {
                $receivedAt =
                    $date
                        ->toDateTime();
            } elseif (
                $date instanceof
                    \DateTimeInterface
            ) {
                $receivedAt =
                    $date;
            }
        } catch (Throwable $e) {
            report($e);
        }

        $readAt = null;

        try {
            $flags =
                $this->invokeRemoteGetter(
                    $remote,
                    'getFlags'
                );

            $flagText =
                strtolower(
                    json_encode(
                        $flags
                    )
                        ?: ''
                );

            if (
                str_contains(
                    $flagText,
                    'seen'
                )
            ) {
                $readAt = now();
            }
        } catch (Throwable $e) {
            report($e);
        }

        return [
            'provider_message_id' =>
                $messageId,

            'in_reply_to' =>
                $inReplyTo,

            'message_references' =>
                $messageReferences,

            'source_type' =>
                'direct_email',

            'source_label' =>
                'Direct Email',

            'from' =>
                $from,

            'to' =>
                $to,

            'cc' =>
                $cc,

            'reply_to' =>
                $replyTo !== []
                    ? implode(
                        ', ',
                        $replyTo
                    )
                    : null,

            'subject' =>
                $subject,

            'body' =>
                $textBody,

            'body_html' =>
                $htmlBody,

            'read_at' =>
                $readAt,

            'received_at' =>
                $receivedAt
                    ?? now(),

            /*
             * ESUBIZ_CORE_IMAP_ATTACHMENT_INGEST_V3
             *
             * Remote attachment objects remain temporary transport
             * handles until the canonical email_messages row exists.
             */
            'remote_attachments' =>
                $this->remoteAttachments(
                    $remote
                ),

            /*
             * has_attachments becomes true only after canonical
             * physical storage succeeds.
             */
            'attachments' =>
                [],
        ];
    }

    protected function mailbox(
        int $mailboxId
    ): object {
        $schema =
            Schema::connection(
                'website_tenant'
            );

        if (
            !$schema->hasTable(
                'email_boxes'
            )
        ) {
            throw new RuntimeException(
                'The Core mailbox registry is unavailable.'
            );
        }

        $mailbox =
            DB::connection(
                'website_tenant'
            )
                ->table(
                    'email_boxes'
                )
                ->where(
                    'id',
                    $mailboxId
                )
                ->where(
                    'status',
                    'active'
                )
                ->first();

        if (!$mailbox) {
            throw new RuntimeException(
                'The selected mailbox is unavailable.'
            );
        }

        return $mailbox;
    }

    /**
     * Resolve either:
     *
     * SaaS:
     *   centrally managed WebsiteMailbox credential.
     *
     * Off-server:
     *   encrypted credential_secret stored after successful
     *   automatic/manual SMTP+IMAP verification.
     */
    protected function resolveConnection(
        Website $website,
        object $mailbox
    ): array {
        $settings =
            $this->settings(
                $mailbox
            );

        $connection =
            (array) (
                $settings[
                    'connection'
                ][
                    'imap'
                ]
                    ?? []
            );

        if (
            empty(
                $connection[
                    'host'
                ]
            )
            || empty(
                $connection[
                    'port'
                ]
            )
        ) {
            throw new RuntimeException(
                'The selected mailbox has no verified IMAP connection.'
            );
        }

        $managed =
            (bool) (
                $settings[
                    'managed_by_esubiz'
                ]
                    ?? false
            );

        if ($managed) {
            $centralMailboxId =
                (int) (
                    $settings[
                        'central_mailbox_id'
                    ]
                        ?? 0
                );

            if ($centralMailboxId < 1) {
                throw new RuntimeException(
                    'The managed mailbox credential reference is unavailable.'
                );
            }

            $centralMailbox =
                WebsiteMailbox::query()
                    ->where(
                        'id',
                        $centralMailboxId
                    )
                    ->where(
                        'website_id',
                        $website->id
                    )
                    ->where(
                        'status',
                        'active'
                    )
                    ->first();

            if (!$centralMailbox) {
                throw new RuntimeException(
                    'The managed mailbox credential is unavailable.'
                );
            }

            $password =
                (string)
                    $centralMailbox
                        ->password;

            $username =
                trim(
                    (string) (
                        $centralMailbox
                            ->address
                        ?: $mailbox->email
                    )
                );
        } else {
            $secret =
                trim(
                    (string) (
                        $mailbox
                            ->credential_secret
                        ?? ''
                    )
                );

            if ($secret === '') {
                throw new RuntimeException(
                    'The external mailbox credential is unavailable.'
                );
            }

            try {
                $password =
                    Crypt::decryptString(
                        $secret
                    );
            } catch (Throwable $e) {
                report($e);

                throw new RuntimeException(
                    'The external mailbox credential could not be resolved.'
                );
            }

            $username =
                trim(
                    (string) (
                        $mailbox->username
                        ?: $mailbox->email
                    )
                );
        }

        if (
            $username === ''
            || $password === ''
        ) {
            throw new RuntimeException(
                'The mailbox authentication credential is incomplete.'
            );
        }

        return [
            'host' =>
                (string)
                    $connection[
                        'host'
                    ],

            'port' =>
                (int)
                    $connection[
                        'port'
                    ],

            'encryption' =>
                $this->normalizeEncryption(
                    $connection[
                        'encryption'
                    ]
                        ?? 'ssl'
                ),

            'verify_peer' =>
                (bool) (
                    $connection[
                        'verify_peer'
                    ]
                        ?? true
                ),

            'username' =>
                $username,

            'password' =>
                $password,
        ];
    }

    protected function client(
        array $connection
    ): object {
        $manager =
            new ClientManager();

        return $manager->make([
            'host' =>
                $connection['host'],

            'port' =>
                $connection['port'],

            'encryption' =>
                $connection[
                    'encryption'
                ],

            'validate_cert' =>
                $connection[
                    'verify_peer'
                ],

            'username' =>
                $connection[
                    'username'
                ],

            'password' =>
                $connection[
                    'password'
                ],

            'protocol' =>
                'imap',

            'authentication' =>
                null,
        ]);
    }

    protected function recordedMessageId(
        int $mailboxId,
        string $providerMessageId
    ): ?int {
        if (
            !Schema::connection(
                'website_tenant'
            )->hasTable(
                'email_messages'
            )
        ) {
            return null;
        }

        $messageId =
            DB::connection(
                'website_tenant'
            )
                ->table(
                    'email_messages'
                )
                ->where(
                    'email_box_id',
                    $mailboxId
                )
                ->where(
                    'provider_message_id',
                    $providerMessageId
                )
                ->value(
                    'id'
                );

        return $messageId !== null
            ? (int) $messageId
            : null;
    }

    protected function settings(
        object $mailbox
    ): array {
        $raw =
            $mailbox->settings
                ?? null;

        if (is_array($raw)) {
            return $raw;
        }

        if (
            !is_string($raw)
            || trim($raw) === ''
        ) {
            return [];
        }

        $decoded =
            json_decode(
                $raw,
                true
            );

        return is_array($decoded)
            ? $decoded
            : [];
    }

    protected function normalizeEncryption(
        mixed $value
    ): string|false {
        $value =
            strtolower(
                trim(
                    (string) $value
                )
            );

        return match ($value) {
            'ssl' =>
                'ssl',

            'tls' =>
                'tls',

            'none',
            '' =>
                false,

            default =>
                'ssl',
        };
    }


    /*
     * ESUBIZ_CORE_WEBKLEX_MAGIC_GETTER_COMPAT_V1
     *
     * Webklex Message / Attachment objects expose many getXxx()
     * accessors through __call(). PHP method_exists() intentionally
     * does not report those magic methods, so all Webklex-facing
     * adapters must invoke through this boundary.
     */
    protected function invokeRemoteGetter(
        mixed $remote,
        string $method,
        mixed $default = null
    ): mixed {
        if (!is_object($remote)) {
            return $default;
        }

        if (
            !method_exists(
                $remote,
                $method
            )
            && !method_exists(
                $remote,
                '__call'
            )
        ) {
            return $default;
        }

        try {
            return $remote->{$method}();
        } catch (Throwable $e) {
            report($e);

            return $default;
        }
    }

    protected function attributeString(
        object $message,
        string $method
    ): ?string {
        $value =
            $this->invokeRemoteGetter(
                $message,
                $method
            );

        if (
            is_object($value)
            && method_exists(
                $value,
                'toString'
            )
        ) {
            $value =
                $value->toString();
        }

        /*
         * Webklex Attribute values commonly expose first()/all()
         * rather than toString().
         */
        if (
            is_object($value)
            && method_exists(
                $value,
                'first'
            )
        ) {
            try {
                $value =
                    $value->first();
            } catch (Throwable $e) {
                report($e);

                return null;
            }
        }

        if (is_array($value)) {
            $value =
                implode(
                    ', ',
                    array_filter(
                        array_map(
                            static fn ($item) =>
                                is_scalar($item)
                                    ? (string) $item
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
            try {
                $value =
                    (string) $value;
            } catch (Throwable $e) {
                report($e);

                return null;
            }
        }

        $value =
            trim(
                (string) (
                    $value
                    ?? ''
                )
            );

        return $value !== ''
            ? $value
            : null;
    }

    protected function body(
        object $message,
        string $method
    ): ?string {
        $body =
            $this->invokeRemoteGetter(
                $message,
                $method
            );

        if ($body === null) {
            return null;
        }

        try {
            $body =
                (string) $body;
        } catch (Throwable $e) {
            report($e);

            return null;
        }

        return $body !== ''
            ? $body
            : null;
    }

    /**
     * Normalize Webklex address collections to plain email strings.
     */
    protected function addresses(
        object $message,
        string $method
    ): array {
        $collection =
            $this->invokeRemoteGetter(
                $message,
                $method
            );

        if (!$collection) {
            return [];
        }

        if (
            is_object($collection)
            && method_exists(
                $collection,
                'all'
            )
        ) {
            try {
                $collection =
                    $collection->all();
            } catch (Throwable $e) {
                report($e);

                return [];
            }
        }

        if (
            $collection instanceof
                \Traversable
        ) {
            $collection =
                iterator_to_array(
                    $collection
                );
        }

        if (!is_array($collection)) {
            $collection = [
                $collection,
            ];
        }

        $addresses = [];

        foreach (
            $collection
            as $address
        ) {
            $email = null;

            if (is_string($address)) {
                $email =
                    trim(
                        $address
                    );
            } elseif (
                is_object($address)
            ) {
                foreach (
                    [
                        'mail',
                        'email',
                        'address',
                    ]
                    as $property
                ) {
                    try {
                        if (
                            isset(
                                $address
                                    ->{$property}
                            )
                        ) {
                            $email =
                                trim(
                                    (string)
                                        $address
                                            ->{$property}
                                );

                            break;
                        }
                    } catch (Throwable $e) {
                        report($e);
                    }
                }

                if ($email === null) {
                    $getter =
                        $this->invokeRemoteGetter(
                            $address,
                            'getEmail'
                        );

                    if (
                        is_scalar($getter)
                    ) {
                        $email =
                            trim(
                                (string)
                                    $getter
                            );
                    }
                }
            }

            if (
                $email !== null
                && $email !== ''
            ) {
                $addresses[] =
                    $email;
            }
        }

        return array_values(
            array_unique(
                $addresses
            )
        );
    }

    protected function remoteAttachments(
        object $message
    ): array {
        try {
            $attachments =
                $this->invokeRemoteGetter(
                    $message,
                    'getAttachments'
                );

            if (!$attachments) {
                return [];
            }

            if (
                is_object($attachments)
                && method_exists(
                    $attachments,
                    'all'
                )
            ) {
                $attachments =
                    $attachments->all();
            }

            if (
                $attachments instanceof
                    \Traversable
            ) {
                $attachments =
                    iterator_to_array(
                        $attachments
                    );
            }

            if (!is_array($attachments)) {
                return [];
            }

            return array_values(
                $attachments
            );
        } catch (Throwable $e) {
            report($e);

            return [];
        }
    }

    protected function normalizeRemoteAttachment(
        mixed $remote
    ): array {
        return [
            'name' =>
                $this->remoteAttachmentString(
                    $remote,
                    [
                        'getName',
                        'getFilename',
                    ],
                    [
                        'name',
                        'filename',
                    ]
                )
                    ?? 'attachment',

            'mime_type' =>
                $this->remoteAttachmentString(
                    $remote,
                    [
                        'getMimeType',
                        'getContentType',
                    ],
                    [
                        'mime_type',
                        'content_type',
                    ]
                ),

            'provider_attachment_id' =>
                $this->remoteAttachmentString(
                    $remote,
                    [
                        'getId',
                        'getContentId',
                    ],
                    [
                        'id',
                        'content_id',
                    ]
                ),

            'bytes' =>
                $this->remoteAttachmentBytes(
                    $remote
                ),
        ];
    }

    protected function remoteAttachmentString(
        mixed $remote,
        array $methods,
        array $properties = []
    ): ?string {
        foreach ($methods as $method) {
            $value =
                $this->invokeRemoteGetter(
                    $remote,
                    $method
                );

            if (
                is_object($value)
                && method_exists(
                    $value,
                    'toString'
                )
            ) {
                try {
                    $value =
                        $value->toString();
                } catch (Throwable $e) {
                    report($e);

                    $value = null;
                }
            }

            if (
                is_object($value)
                && method_exists(
                    $value,
                    'first'
                )
            ) {
                try {
                    $value =
                        $value->first();
                } catch (Throwable $e) {
                    report($e);

                    $value = null;
                }
            }

            if (is_scalar($value)) {
                $value =
                    trim(
                        (string) $value
                    );

                if ($value !== '') {
                    return $value;
                }
            }
        }

        foreach ($properties as $property) {
            try {
                if (
                    is_object($remote)
                    && isset(
                        $remote->{$property}
                    )
                ) {
                    $value =
                        $remote->{$property};

                    if (
                        is_object($value)
                        && method_exists(
                            $value,
                            'first'
                        )
                    ) {
                        $value =
                            $value->first();
                    }

                    if (!is_scalar($value)) {
                        continue;
                    }

                    $value =
                        trim(
                            (string) $value
                        );

                    if ($value !== '') {
                        return $value;
                    }
                }
            } catch (Throwable $e) {
                report($e);
            }
        }

        return null;
    }

    protected function remoteAttachmentBytes(
        mixed $remote
    ): ?string {
        foreach (
            [
                'getContent',
                'getDecodedContent',
            ]
            as $method
        ) {
            $value =
                $this->invokeRemoteGetter(
                    $remote,
                    $method
                );

            if ($value === null) {
                continue;
            }

            try {
                return (string) $value;
            } catch (Throwable $e) {
                report($e);
            }
        }

        return null;
    }


    /**
     * Resolve the most specific stable identity exposed by the
     * remote IMAP folder object.
     */
    protected function folderIdentity(
        object $folder,
        bool $spam
    ): string {
        /*
         * Prefer full/path-like folder identifiers before display names.
         */
        foreach (
            [
                'getPath',
                'getFullName',
                'fullName',
                'path',
                'getName',
                'name',
            ]
            as $method
        ) {
            try {
                if (
                    !method_exists(
                        $folder,
                        $method
                    )
                ) {
                    continue;
                }

                $value =
                    $folder->{$method}();

                if (
                    is_object($value)
                    && method_exists(
                        $value,
                        'toString'
                    )
                ) {
                    $value =
                        $value->toString();
                }

                if (!is_scalar($value)) {
                    continue;
                }

                $value =
                    trim(
                        (string) $value
                    );

                if ($value !== '') {
                    return $value;
                }
            } catch (Throwable $e) {
                report($e);
            }
        }

        foreach (
            [
                'path',
                'full_name',
                'fullname',
                'name',
            ]
            as $property
        ) {
            try {
                if (
                    !property_exists(
                        $folder,
                        $property
                    )
                ) {
                    continue;
                }

                $value =
                    $folder->{$property};

                if (!is_scalar($value)) {
                    continue;
                }

                $value =
                    trim(
                        (string) $value
                    );

                if ($value !== '') {
                    return $value;
                }
            } catch (Throwable $e) {
                report($e);
            }
        }

        /*
         * We already know which logical folder sync is executing.
         * This fallback therefore remains deterministic.
         */
        return $spam
            ? 'Spam'
            : 'INBOX';
    }

}
