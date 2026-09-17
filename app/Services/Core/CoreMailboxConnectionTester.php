<?php

namespace App\Services\Core;

use RuntimeException;
use Symfony\Component\Mailer\Transport\Smtp\EsmtpTransport;
use Webklex\PHPIMAP\ClientManager;

class CoreMailboxConnectionTester
{
    /*
     * ESUBIZ_CORE_MAILBOX_CONNECTION_TESTER_V1
     *
     * Authentication boundary for real Core mailboxes.
     *
     * Automatic discovery is accepted only when:
     *   - SMTP authenticates successfully; AND
     *   - IMAP authenticates successfully.
     *
     * No email is sent during this test.
     * No message is downloaded or persisted.
     */

    public function test(
        string $email,
        string $password,
        array $connection
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

        $smtp = $connection['smtp'] ?? null;
        $imap = $connection['imap'] ?? null;

        if (!is_array($smtp) || !is_array($imap)) {
            return [
                'success' => false,
                'smtp' => [
                    'success' => false,
                    'error' =>
                        'SMTP configuration is incomplete.',
                ],
                'imap' => [
                    'success' => false,
                    'error' =>
                        'IMAP configuration is incomplete.',
                ],
            ];
        }

        $smtpResult = $this->testSmtp(
            $email,
            $password,
            $smtp
        );

        $imapResult = $this->testImap(
            $email,
            $password,
            $imap
        );

        return [
            'success' =>
                ($smtpResult['success'] ?? false)
                && ($imapResult['success'] ?? false),

            'smtp' => $smtpResult,
            'imap' => $imapResult,
        ];
    }

    protected function testSmtp(
        string $email,
        string $password,
        array $config
    ): array {
        $host = strtolower(
            trim((string) ($config['host'] ?? ''))
        );

        $port = (int) ($config['port'] ?? 0);

        $encryption = strtolower(
            trim((string) ($config['encryption'] ?? 'tls'))
        );

        if ($host === '' || $port < 1) {
            return [
                'success' => false,
                'error' =>
                    'SMTP server or port is missing.',
            ];
        }

        try {
            /*
             * EsmtpTransport authenticates when start() is called.
             *
             * Port 465 requires implicit TLS.
             * Port 587/25 may upgrade through STARTTLS when the
             * server advertises it.
             */
            $tls =
                $encryption === 'ssl'
                || $port === 465;

            $transport = new EsmtpTransport(
                $host,
                $port,
                $tls
            );

            $transport->setUsername($email);
            $transport->setPassword($password);

            $transport->start();
            $transport->stop();

            return [
                'success' => true,
                'host' => $host,
                'port' => $port,
                'encryption' => $encryption,
            ];

        } catch (\Throwable $e) {
            return [
                'success' => false,
                'host' => $host,
                'port' => $port,
                'encryption' => $encryption,
                'error' => $this->safeError($e),
            ];
        }
    }

    protected function testImap(
        string $email,
        string $password,
        array $config
    ): array {
        $host = strtolower(
            trim((string) ($config['host'] ?? ''))
        );

        $port = (int) ($config['port'] ?? 0);

        $encryption = strtolower(
            trim((string) ($config['encryption'] ?? 'ssl'))
        );

        if ($host === '' || $port < 1) {
            return [
                'success' => false,
                'error' =>
                    'IMAP server or port is missing.',
            ];
        }

        try {
            $manager = new ClientManager();

            $client = $manager->make([
                'host' => $host,
                'port' => $port,
                'encryption' =>
                    $encryption === 'none'
                        ? false
                        : $encryption,

                'validate_cert' =>
                    (bool) (
                        $config['verify_peer']
                        ?? true
                    ),

                'username' => $email,
                'password' => $password,
                'protocol' => 'imap',

                'authentication' => null,
            ]);

            $client->connect();

            if (!$client->isConnected()) {
                throw new RuntimeException(
                    'IMAP authentication did not establish a connection.'
                );
            }

            $client->disconnect();

            return [
                'success' => true,
                'host' => $host,
                'port' => $port,
                'encryption' => $encryption,
            ];

        } catch (\Throwable $e) {
            return [
                'success' => false,
                'host' => $host,
                'port' => $port,
                'encryption' => $encryption,
                'error' => $this->safeError($e),
            ];
        }
    }

    protected function safeError(\Throwable $e): string
    {
        /*
         * ESUBIZ_CORE_MAILBOX_SAFE_TEST_ERRORS_V2
         *
         * Never expose raw connection-library exception strings to
         * the Core UI. They may contain host, username, protocol or
         * authentication details.
         *
         * The full Throwable is retained in the application log.
         */
        report($e);

        $message =
            strtolower(
                trim(
                    $e->getMessage()
                )
            );

        if (
            str_contains(
                $message,
                'auth'
            )
            || str_contains(
                $message,
                'credential'
            )
            || str_contains(
                $message,
                'password'
            )
            || str_contains(
                $message,
                'login'
            )
        ) {
            return
                'Mailbox authentication failed.';
        }

        if (
            str_contains(
                $message,
                'certificate'
            )
            || str_contains(
                $message,
                'ssl'
            )
            || str_contains(
                $message,
                'tls'
            )
        ) {
            return
                'A secure connection to the mail server could not be established.';
        }

        if (
            str_contains(
                $message,
                'timed out'
            )
            || str_contains(
                $message,
                'timeout'
            )
        ) {
            return
                'The mail server connection timed out.';
        }

        if (
            str_contains(
                $message,
                'refused'
            )
            || str_contains(
                $message,
                'unreachable'
            )
            || str_contains(
                $message,
                'getaddrinfo'
            )
            || str_contains(
                $message,
                'resolve'
            )
        ) {
            return
                'The mail server could not be reached.';
        }

        return
            'Mailbox verification failed.';
    }
}
