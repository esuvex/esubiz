<?php

namespace App\Services\Core;

use RuntimeException;

class CoreMailboxAutoConnectionService
{
    /*
     * ESUBIZ_CORE_MAILBOX_AUTO_CONNECTION_V1
     *
     * Universal mailbox auto-connection orchestrator.
     *
     * OFF-SERVER NORMAL FLOW:
     *   email + password
     *       -> discover SMTP/IMAP candidates
     *       -> authenticate candidates
     *       -> return verified real-mail parameters
     *
     * If no authenticated SMTP + IMAP combination exists:
     *   manual_required = true
     *
     * The Core UI can then expose the advanced real-mail fields:
     * SMTP server / port / encryption
     * IMAP server / port / encryption
     * TLS certificate verification.
     *
     * This service does not persist credentials.
     */

    public function __construct(
        protected CoreMailboxConnectionDiscoveryService $discovery,
        protected CoreMailboxConnectionTester $tester
    ) {
    }

    public function connect(
        string $email,
        string $password
    ): array {
        $email = strtolower(trim($email));

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new RuntimeException(
                'A valid mailbox email address is required.'
            );
        }

        if ($password === '') {
            throw new RuntimeException(
                'Mailbox password is required.'
            );
        }

        $discovered = $this->discovery->discover($email);

        $smtpCandidates =
            $discovered['candidates']['smtp']
            ?? [];

        $imapCandidates =
            $discovered['candidates']['imap']
            ?? [];

        if (
            $smtpCandidates === []
            || $imapCandidates === []
        ) {
            return $this->manualResult(
                $email,
                $discovered,
                'Automatic discovery could not locate both SMTP and IMAP services.'
            );
        }

        /*
         * Test each SMTP candidate independently first.
         * There is no reason to repeatedly authenticate IMAP against
         * every failed SMTP candidate.
         */
        $workingSmtp = null;
        $smtpAttempts = [];

        foreach ($smtpCandidates as $smtp) {
            $result = $this->tester->test(
                $email,
                $password,
                [
                    'smtp' => $smtp,

                    /*
                     * A real IMAP candidate is required by tester.
                     * Its result is ignored during this phase.
                     */
                    'imap' => $imapCandidates[0],
                ]
            );

            $smtpResult = $result['smtp'] ?? [
                'success' => false,
            ];

            $smtpAttempts[] =
                $this->publicAttempt($smtpResult);

            if ($smtpResult['success'] ?? false) {
                $workingSmtp = $smtp;
                break;
            }
        }

        if (!is_array($workingSmtp)) {
            return $this->manualResult(
                $email,
                $discovered,
                'Automatic SMTP authentication failed.',
                [
                    'smtp' => $smtpAttempts,
                    'imap' => [],
                ]
            );
        }

        /*
         * Now authenticate each IMAP candidate using the verified SMTP
         * candidate. Tester still validates both sides, ensuring the
         * final accepted pair is genuinely usable together.
         */
        $imapAttempts = [];

        foreach ($imapCandidates as $imap) {
            $result = $this->tester->test(
                $email,
                $password,
                [
                    'smtp' => $workingSmtp,
                    'imap' => $imap,
                ]
            );

            $imapResult = $result['imap'] ?? [
                'success' => false,
            ];

            $imapAttempts[] =
                $this->publicAttempt($imapResult);

            if (
                ($result['success'] ?? false)
                && ($imapResult['success'] ?? false)
            ) {
                return [
                    'success' => true,
                    'automatic' => true,
                    'manual_required' => false,

                    'email' => $email,

                    'connection' => [
                        'smtp' => $workingSmtp,
                        'imap' => $imap,
                    ],

                    'attempts' => [
                        'smtp' => $smtpAttempts,
                        'imap' => $imapAttempts,
                    ],
                ];
            }
        }

        return $this->manualResult(
            $email,
            $discovered,
            'Automatic IMAP authentication failed.',
            [
                'smtp' => $smtpAttempts,
                'imap' => $imapAttempts,
            ]
        );
    }

    public function testManual(
        string $email,
        string $password,
        array $smtp,
        array $imap
    ): array {
        $normalized = [
            'smtp' => $this->normalizeManualProtocol(
                'SMTP',
                $smtp
            ),

            'imap' => $this->normalizeManualProtocol(
                'IMAP',
                $imap
            ),
        ];

        $result = $this->tester->test(
            $email,
            $password,
            $normalized
        );

        return [
            'success' => (bool) (
                $result['success']
                ?? false
            ),

            'automatic' => false,

            'manual_required' =>
                !(bool) (
                    $result['success']
                    ?? false
                ),

            'email' => strtolower(trim($email)),

            'connection' =>
                ($result['success'] ?? false)
                    ? $normalized
                    : null,

            'test' => [
                'smtp' =>
                    $this->publicAttempt(
                        $result['smtp'] ?? []
                    ),

                'imap' =>
                    $this->publicAttempt(
                        $result['imap'] ?? []
                    ),
            ],
        ];
    }

    protected function normalizeManualProtocol(
        string $label,
        array $config
    ): array {
        $host = strtolower(
            trim((string) ($config['host'] ?? ''))
        );

        $port = (int) ($config['port'] ?? 0);

        $encryption = strtolower(
            trim(
                (string) (
                    $config['encryption']
                    ?? 'tls'
                )
            )
        );

        if (
            $host === ''
            || $port < 1
            || $port > 65535
        ) {
            throw new RuntimeException(
                "{$label} server and valid port are required."
            );
        }

        if (
            !in_array(
                $encryption,
                ['ssl', 'tls', 'none'],
                true
            )
        ) {
            throw new RuntimeException(
                "{$label} encryption must be SSL, TLS or none."
            );
        }

        return [
            'host' => $host,
            'port' => $port,
            'encryption' => $encryption,

            /*
             * Secure default. A later UI may expose certificate
             * verification only where genuinely required.
             */
            'verify_peer' =>
                array_key_exists(
                    'verify_peer',
                    $config
                )
                    ? (bool) $config['verify_peer']
                    : true,
        ];
    }

    protected function manualResult(
        string $email,
        array $discovered,
        string $reason,
        array $attempts = []
    ): array {
        return [
            'success' => false,
            'automatic' => false,
            'manual_required' => true,

            'email' => $email,
            'reason' => $reason,

            /*
             * These are public connection parameters only.
             * Never include the supplied password.
             */
            'suggested' => [
                'smtp' =>
                    $discovered['automatic']['smtp']
                    ?? null,

                'imap' =>
                    $discovered['automatic']['imap']
                    ?? null,
            ],

            'attempts' => $attempts,
        ];
    }

    protected function publicAttempt(
        array $result
    ): array {
        return [
            'success' => (bool) (
                $result['success']
                ?? false
            ),

            'host' =>
                $result['host']
                ?? null,

            'port' =>
                $result['port']
                ?? null,

            'encryption' =>
                $result['encryption']
                ?? null,

            'error' =>
                $result['error']
                ?? null,
        ];
    }
}
