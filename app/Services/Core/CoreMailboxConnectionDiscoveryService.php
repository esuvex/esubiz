<?php

namespace App\Services\Core;

use RuntimeException;

class CoreMailboxConnectionDiscoveryService
{
    /*
     * ESUBIZ_CORE_MAILBOX_CONNECTION_DISCOVERY_V1
     *
     * Universal mail-server discovery boundary.
     *
     * OFF-SERVER:
     *   Admin normally supplies only email + password.
     *   Core discovers SMTP + IMAP automatically.
     *   Manual server/security fields are required only when
     *   automatic discovery cannot produce a working connection.
     *
     * SAAS:
     *   The same normalized connection contract is used, but
     *   Esubiz supplies the managed mailbox identity/credential.
     *
     * This service performs server discovery only.
     * It does not persist credentials and does not send/receive mail.
     */

    public function discover(string $email): array
    {
        $email = strtolower(trim($email));

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new RuntimeException(
                'A valid mailbox email address is required.'
            );
        }

        [, $domain] = explode('@', $email, 2);

        $domain = strtolower(trim($domain, '.'));

        if ($domain === '') {
            throw new RuntimeException(
                'Unable to determine the mailbox domain.'
            );
        }

        $hosts = $this->candidateHosts($domain);

        /*
         * ESUBIZ_CORE_MAILBOX_CONNECTION_CANDIDATES_V2
         *
         * Discovery must retain every reachable conventional candidate.
         * Authentication decides which SMTP/IMAP pair is authoritative.
         */
        $smtpCandidates = $this->discoverProtocolCandidates(
            'smtp',
            $hosts,
            [
                [
                    'port' => 465,
                    'encryption' => 'ssl',
                    'implicit_tls' => true,
                ],
                [
                    'port' => 587,
                    'encryption' => 'tls',
                    'implicit_tls' => false,
                ],
                [
                    'port' => 25,
                    'encryption' => 'tls',
                    'implicit_tls' => false,
                ],
            ]
        );

        $imapCandidates = $this->discoverProtocolCandidates(
            'imap',
            $hosts,
            [
                [
                    'port' => 993,
                    'encryption' => 'ssl',
                    'implicit_tls' => true,
                ],
                [
                    'port' => 143,
                    'encryption' => 'tls',
                    'implicit_tls' => false,
                ],
            ]
        );

        $smtp = $smtpCandidates[0] ?? null;
        $imap = $imapCandidates[0] ?? null;

        return [
            'email' => $email,
            'domain' => $domain,

            /*
             * Preserve the original automatic contract for callers
             * that only need the preferred reachable candidates.
             */
            'automatic' => [
                'smtp' => $smtp,
                'imap' => $imap,
            ],

            /*
             * The connection orchestrator consumes all candidates and
             * authenticates them before accepting a mailbox.
             */
            'candidates' => [
                'smtp' => $smtpCandidates,
                'imap' => $imapCandidates,
            ],

            'complete' =>
                is_array($smtp)
                && is_array($imap),

            'manual_required' =>
                !is_array($smtp)
                || !is_array($imap),

            'discovered_at' => now()->toIso8601String(),
        ];
    }

    protected function candidateHosts(string $domain): array
    {
        $hosts = [
            'mail.' . $domain,
            'smtp.' . $domain,
            'imap.' . $domain,
            $domain,
        ];

        /*
         * MX hosts are useful candidates for installations where the
         * same provider exposes client mail services on its MX host.
         *
         * They are candidates only. A successful socket probe is still
         * required before they are returned.
         */
        $mxHosts = [];
        $weights = [];

        if (getmxrr($domain, $mxHosts, $weights)) {
            foreach ($mxHosts as $mxHost) {
                $mxHost = strtolower(
                    trim((string) $mxHost, '.')
                );

                if ($mxHost !== '') {
                    $hosts[] = $mxHost;
                }
            }
        }

        return array_values(
            array_unique(
                array_filter($hosts)
            )
        );
    }

    protected function discoverProtocolCandidates(
        string $protocol,
        array $hosts,
        array $ports
    ): array {
        $orderedHosts = $this->prioritizeHosts(
            $protocol,
            $hosts
        );

        $found = [];

        foreach ($orderedHosts as $host) {
            foreach ($ports as $candidate) {
                if (
                    !$this->socketAvailable(
                        $host,
                        (int) $candidate['port'],
                        (bool) $candidate['implicit_tls']
                    )
                ) {
                    continue;
                }

                $key =
                    $host
                    . ':'
                    . (int) $candidate['port']
                    . ':'
                    . (string) $candidate['encryption'];

                $found[$key] = [
                    'host' => $host,
                    'port' => (int) $candidate['port'],
                    'encryption' =>
                        (string) $candidate['encryption'],

                    'verify_peer' => true,
                ];
            }
        }

        return array_values($found);
    }

    protected function discoverProtocol(
        string $protocol,
        array $hosts,
        array $ports
    ): ?array {
        /*
         * Prefer conventional protocol-specific hostnames before
         * generic mail/MX hosts where both answer.
         */
        $orderedHosts = $this->prioritizeHosts(
            $protocol,
            $hosts
        );

        foreach ($orderedHosts as $host) {
            foreach ($ports as $candidate) {
                if (
                    $this->socketAvailable(
                        $host,
                        (int) $candidate['port'],
                        (bool) $candidate['implicit_tls']
                    )
                ) {
                    return [
                        'host' => $host,
                        'port' => (int) $candidate['port'],
                        'encryption' =>
                            (string) $candidate['encryption'],

                        'verify_peer' => true,
                    ];
                }
            }
        }

        return null;
    }

    protected function prioritizeHosts(
        string $protocol,
        array $hosts
    ): array {
        usort(
            $hosts,
            static function (
                string $left,
                string $right
            ) use ($protocol): int {
                $leftScore =
                    str_starts_with(
                        $left,
                        $protocol . '.'
                    )
                        ? 0
                        : (
                            str_starts_with(
                                $left,
                                'mail.'
                            )
                                ? 1
                                : 2
                        );

                $rightScore =
                    str_starts_with(
                        $right,
                        $protocol . '.'
                    )
                        ? 0
                        : (
                            str_starts_with(
                                $right,
                                'mail.'
                            )
                                ? 1
                                : 2
                        );

                return $leftScore <=> $rightScore;
            }
        );

        return $hosts;
    }

    protected function socketAvailable(
        string $host,
        int $port,
        bool $implicitTls
    ): bool {
        $target =
            ($implicitTls ? 'ssl://' : '')
            . $host;

        $context = stream_context_create([
            'ssl' => [
                /*
                 * Production connection clients must verify TLS.
                 * Discovery also keeps verification enabled so a
                 * broken/untrusted certificate does not silently
                 * become an accepted automatic configuration.
                 */
                'verify_peer' => true,
                'verify_peer_name' => true,
                'peer_name' => $host,
                'SNI_enabled' => true,
            ],
        ]);

        $errno = 0;
        $error = '';

        $socket = @stream_socket_client(
            $target . ':' . $port,
            $errno,
            $error,
            3,
            STREAM_CLIENT_CONNECT,
            $context
        );

        if (!is_resource($socket)) {
            return false;
        }

        stream_set_timeout($socket, 3);

        /*
         * We intentionally do not authenticate here.
         * Authentication belongs to the connection tester.
         */
        fclose($socket);

        return true;
    }
}
