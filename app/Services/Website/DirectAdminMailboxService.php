<?php

namespace App\Services\Website;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class DirectAdminMailboxService
{
    /*
     * ESUBIZ_DIRECTADMIN_MAILBOX_SERVICE_V1
     *
     * Central-only DirectAdmin adapter.
     *
     * Supported operations:
     * - create managed mailbox
     * - delete managed mailbox
     * - fetch live mailbox storage usage
     *
     * DirectAdmin's administrator/login-key credentials never leave
     * Central Esubiz.
     */

    public function create(
        string $domain,
        string $localPart,
        string $password,
        int $quotaMb
    ): void {
        $response = $this->request()
            ->asForm()
            ->post(
                '/CMD_API_POP',
                [
                    'action' => 'create',
                    'domain' => $domain,
                    'user' => $localPart,
                    'passwd' => $password,
                    'passwd2' => $password,

                    /*
                     * ESUBIZ_MANAGED_MAIL_SHARED_STORAGE_CEILING_V2
                     *
                     * This is NOT additional email storage.
                     *
                     * DirectAdmin receives the Website's complete
                     * Core storage allowance as a hard mailbox ceiling.
                     * Esubiz still combines mailbox + files/media into
                     * the Website's one shared storage meter.
                     */
                    'quota' =>
                        max(
                            1,
                            $quotaMb
                        ),
                ]
            );

        $this->assertSuccessfulApiResponse(
            $response->body(),
            'create'
        );
    }

    public function delete(
        string $domain,
        string $localPart
    ): void {
        $response = $this->request()
            ->asForm()
            ->post(
                '/CMD_API_POP',
                [
                    'action' => 'delete',
                    'domain' => $domain,
                    'user' => $localPart,
                ]
            );

        $this->assertSuccessfulApiResponse(
            $response->body(),
            'delete'
        );
    }

    /**
     * Return the real current mailbox disk usage in bytes.
     *
     * DirectAdmin computes this from the mailbox itself, so purged
     * messages automatically stop consuming Core Website storage.
     */
    public function storageBytes(
        string $domain,
        string $localPart
    ): int {
        $response = $this->request()
            ->get(
                '/CMD_API_POP',
                [
                    'type' => 'quota',
                    'domain' => $domain,
                    'user' => $localPart,
                ]
            );

        if (!$response->successful()) {
            throw new RuntimeException(
                'DirectAdmin mailbox quota request failed with HTTP '
                . $response->status()
                . '.'
            );
        }

        $data = [];

        parse_str(
            trim($response->body()),
            $data
        );

        if (
            isset($data['error'])
            && (string) $data['error'] === '1'
        ) {
            throw new RuntimeException(
                'DirectAdmin mailbox quota lookup failed: '
                . trim(
                    (string) (
                        $data['details']
                        ?? $data['text']
                        ?? 'Unknown DirectAdmin error.'
                    )
                )
            );
        }

        /*
         * Current DirectAdmin quota API exposes total_bytes.
         * Older installations may expose total instead.
         */
        $bytes =
            $data['total_bytes']
            ?? $data['total']
            ?? 0;

        return max(
            0,
            (int) $bytes
        );
    }

    protected function request(): PendingRequest
    {
        if (
            !config(
                'esubiz_mail.directadmin.enabled',
                false
            )
        ) {
            throw new RuntimeException(
                'DirectAdmin managed-mail provisioning is disabled.'
            );
        }

        $username =
            trim(
                (string) config(
                    'esubiz_mail.directadmin.username'
                )
            );

        $loginKey =
            trim(
                (string) config(
                    'esubiz_mail.directadmin.login_key'
                )
            );

        if (
            $username === ''
            || $loginKey === ''
        ) {
            throw new RuntimeException(
                'DirectAdmin mail credentials are not configured.'
            );
        }

        $baseUrl =
            rtrim(
                trim(
                    (string) config(
                        'esubiz_mail.directadmin.base_url'
                    )
                ),
                '/'
            );

        if ($baseUrl === '') {
            throw new RuntimeException(
                'DirectAdmin URL is not configured.'
            );
        }

        $request =
            Http::baseUrl($baseUrl)
                ->withBasicAuth(
                    $username,
                    $loginKey
                )
                ->accept(
                    'application/x-www-form-urlencoded'
                )
                ->timeout(
                    max(
                        1,
                        (int) config(
                            'esubiz_mail.directadmin.timeout',
                            15
                        )
                    )
                );

        if (
            !config(
                'esubiz_mail.directadmin.verify_tls',
                true
            )
        ) {
            $request = $request->withoutVerifying();
        }

        return $request;
    }

    protected function assertSuccessfulApiResponse(
        string $body,
        string $operation
    ): void {
        $data = [];

        parse_str(
            trim($body),
            $data
        );

        if (
            isset($data['error'])
            && (string) $data['error'] === '0'
        ) {
            return;
        }

        throw new RuntimeException(
            'DirectAdmin mailbox '
            . $operation
            . ' failed: '
            . trim(
                (string) (
                    $data['details']
                    ?? $data['text']
                    ?? 'Unknown DirectAdmin response.'
                )
            )
        );
    }
}
