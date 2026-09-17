<?php

namespace App\Services\Core;

use App\Models\Website;
use Throwable;

/**
 * ESUBIZ_CORE_INBOUND_SOURCE_ROUTER_V1
 *
 * Universal inbound email source-routing boundary.
 *
 * Responsibilities:
 *
 * 1. Identify which Core source owns/recognizes an inbound email.
 * 2. Enrich the universal message contract with:
 *      source_type
 *      source_label
 *      source_reference
 *      thread_key
 * 3. Dispatch the inbound event to that source independently from
 *    normal mailbox visibility/recording.
 *
 * IMPORTANT:
 *
 * Source routing and mailbox recording are separate concerns.
 *
 * If Core Admin disables "Tickets" from normal mailbox visibility,
 * a real ticket reply must still reach the Tickets system. The only
 * thing disabled is the mirrored Inbox/Spam/Sent ledger entry for
 * that source.
 *
 * Direct Email is the safe fallback until individual source handlers
 * are registered.
 */
class CoreMailInboundSourceRouter
{
    /**
     * Registered source handlers.
     *
     * Handlers are intentionally empty in V1. Future Tickets, CRM,
     * Live Chat, Contact Form and module integrations register here
     * without changing the IMAP transport.
     *
     * Each handler may implement:
     *
     * identify(Website $website, int $mailboxId, array $message): ?array
     *
     * receive(
     *     Website $website,
     *     int $mailboxId,
     *     array $message,
     *     ?int $canonicalMessageId
     * ): void
     */
    protected array $handlers = [];

    /**
     * Identify/enrich an inbound message BEFORE mailbox recording.
     *
     * This method must never require a canonical email_messages row.
     * That allows source functionality to continue when mailbox
     * visibility for the source is disabled.
     */
    public function identify(
        Website $website,
        int $mailboxId,
        array $message
    ): array {
        $message =
            $this->normalizeFallback(
                $message
            );

        foreach (
            $this->handlers
            as $sourceType => $handler
        ) {
            try {
                if (
                    !is_object($handler)
                    || !method_exists(
                        $handler,
                        'identify'
                    )
                ) {
                    continue;
                }

                $match =
                    $handler->identify(
                        $website,
                        $mailboxId,
                        $message
                    );

                if (
                    !is_array($match)
                    || $match === []
                ) {
                    continue;
                }

                $message =
                    array_merge(
                        $message,
                        $match
                    );

                $message[
                    'source_type'
                ] =
                    trim(
                        (string) (
                            $message[
                                'source_type'
                            ]
                                ?? $sourceType
                        )
                    );

                if (
                    $message[
                        'source_type'
                    ] === ''
                ) {
                    $message[
                        'source_type'
                    ] =
                        (string)
                            $sourceType;
                }

                $message[
                    'source_label'
                ] =
                    trim(
                        (string) (
                            $message[
                                'source_label'
                            ]
                                ?? $this->sourceLabel(
                                    $message[
                                        'source_type'
                                    ]
                                )
                        )
                    );

                return $message;
            } catch (Throwable $e) {
                /*
                 * One source recognizer must never prevent the real
                 * mailbox from receiving the email.
                 */
                report($e);
            }
        }

        return $message;
    }

    /**
     * Dispatch the inbound event to the identified source.
     *
     * This happens independently from whether CoreMailGateway created
     * a normal mailbox ledger row.
     *
     * $canonicalMessageId is nullable specifically because mailbox
     * visibility can be disabled for a source.
     */
    public function dispatch(
        Website $website,
        int $mailboxId,
        array $message,
        ?int $canonicalMessageId = null
    ): void {
        $sourceType =
            trim(
                (string) (
                    $message[
                        'source_type'
                    ]
                        ?? 'direct_email'
                )
            );

        if (
            $sourceType === ''
            || $sourceType === 'direct_email'
        ) {
            return;
        }

        $handler =
            $this->handlers[
                $sourceType
            ]
                ?? null;

        if (
            !is_object($handler)
            || !method_exists(
                $handler,
                'receive'
            )
        ) {
            /*
             * Unknown/unregistered sources still remain valid mailbox
             * email. They simply have no source-specific receiver yet.
             */
            return;
        }

        try {
            $handler->receive(
                $website,
                $mailboxId,
                $message,
                $canonicalMessageId
            );
        } catch (Throwable $e) {
            /*
             * Preserve real mailbox synchronization even if one
             * business-source handler fails.
             *
             * Source retry/outbox mechanics can be layered later
             * without coupling IMAP retrieval to source persistence.
             */
            report($e);
        }
    }

    /**
     * Programmatic registration boundary for source handlers.
     *
     * This keeps CoreMailInboundSourceRouter generic instead of
     * hard-coding Tickets/CRM/Live Chat implementation classes into
     * the IMAP transport.
     */
    public function register(
        string $sourceType,
        object $handler
    ): void {
        $sourceType =
            trim(
                $sourceType
            );

        if ($sourceType === '') {
            return;
        }

        $this->handlers[
            $sourceType
        ] =
            $handler;
    }

    public function registeredSources(): array
    {
        return array_keys(
            $this->handlers
        );
    }

    protected function normalizeFallback(
        array $message
    ): array {
        $sourceType =
            trim(
                (string) (
                    $message[
                        'source_type'
                    ]
                        ?? ''
                )
            );

        if ($sourceType === '') {
            $sourceType =
                'direct_email';
        }

        $message[
            'source_type'
        ] =
            $sourceType;

        $sourceLabel =
            trim(
                (string) (
                    $message[
                        'source_label'
                    ]
                        ?? ''
                )
            );

        if ($sourceLabel === '') {
            $sourceLabel =
                $this->sourceLabel(
                    $sourceType
                );
        }

        $message[
            'source_label'
        ] =
            $sourceLabel;

        $message[
            'source_reference'
        ] =
            $this->nullableString(
                $message[
                    'source_reference'
                ]
                    ?? null
            );

        $message[
            'thread_key'
        ] =
            $this->nullableString(
                $message[
                    'thread_key'
                ]
                    ?? null
            );

        return $message;
    }

    protected function sourceLabel(
        string $sourceType
    ): string {
        return match (
            $sourceType
        ) {
            'direct_email' =>
                'Direct Email',

            'core_system' =>
                'Core System',

            'system_auth' =>
                'System Authentication',

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
                        [
                            '_',
                            '-',
                        ],
                        ' ',
                        $sourceType
                    )
                ),
        };
    }

    protected function nullableString(
        mixed $value
    ): ?string {
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

        return $value !== ''
            ? $value
            : null;
    }
}
