<?php

namespace App\Services\Core;

use App\Models\Website;
use App\Models\WebsiteMailbox;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use RuntimeException;
use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mailer\Transport\Smtp\EsmtpTransport;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;

class CoreMailboxTransportService
{
    public function __construct(
        protected CoreEmailBrandingService $branding
    ) {
    }

    /*
     * ESUBIZ_CORE_REAL_SMTP_TRANSPORT_V1
     *
     * Universal outbound transport for Core.
     *
     * SaaS:
     *   email_boxes.settings.central_mailbox_id
     *       -> Central WebsiteMailbox
     *       -> centrally encrypted mailbox password.
     *
     * Off-server:
     *   email_boxes.credential_secret
     *       -> Laravel encrypted mailbox password.
     *
     * SMTP public connection parameters are read from the Core mailbox
     * settings for off-server mailboxes.
     *
     * This service TRANSPORTS only.
     * CoreMailGateway remains the canonical persistence/ledger boundary.
     */

    public function send(
        Website $website,
        int $mailboxId,
        array $message
    ): array {
        $mailbox =
            DB::connection('website_tenant')
                ->table('email_boxes')
                ->where('id', $mailboxId)
                ->where('status', 'active')
                ->first();

        if (!$mailbox) {
            throw new RuntimeException(
                'The selected email address is unavailable or inactive.'
            );
        }

        $from =
            strtolower(
                trim((string) $mailbox->email)
            );

        if (!filter_var($from, FILTER_VALIDATE_EMAIL)) {
            throw new RuntimeException(
                'The selected mailbox does not have a valid email address.'
            );
        }

        $resolved =
            $this->resolveConnection(
                $website,
                $mailbox
            );

        $smtp =
            $resolved['smtp'];

        $password =
            $resolved['password'];

        $username =
            trim(
                (string) (
                    $resolved['username']
                    ?? $from
                )
            );

        if ($username === '') {
            $username = $from;
        }

        $transport =
            $this->makeTransport(
                $smtp,
                $username,
                $password
            );

        /*
         * ESUBIZ_CORE_OUTGOING_EMAIL_BRANDING_V1
         *
         * Apply the website's saved Core Email Branding immediately
         * before the MIME message is built and transported.
         *
         * Only HTML is wrapped. The plain-text alternative remains
         * unchanged for clients that prefer text email.
         */
        $bodyHtml = trim(
            (string) (
                $message['body_html']
                    ?? ''
            )
        );

        if ($bodyHtml !== '') {
            $message['body_html'] =
                $this->branding->render(
                    $website,
                    $bodyHtml
                );
        }

        $email =
            $this->buildMessage(
                $website,
                $from,
                $message
            );

        $mailer = new Mailer($transport);

        /*
         * ESUBIZ_CORE_SMTP_MESSAGE_ID_BEFORE_SEND_V1
         *
         * Establish the RFC Message-ID before SMTP transmission so
         * Sent persistence and the later IMAP copy can correlate to
         * the same real message.
         */
        $messageId =
            trim(
                (string) (
                    $email->getHeaders()
                        ->get('Message-ID')
                        ?->getBodyAsString()
                    ?? ''
                )
            );

        if ($messageId === '') {
            $messageId =
                bin2hex(
                    random_bytes(16)
                )
                . '@esubiz.com';

            $email->getHeaders()
                ->addIdHeader(
                    'Message-ID',
                    $messageId
                );
        }

        /*
         * A successful return from Symfony Mailer means the SMTP
         * transport accepted the message. Persistence into Sent is
         * deliberately handled by CoreMailGateway outside this class.
         */
        $mailer->send($email);

        return [
            'success' => true,

            'from' => $from,

            'message_id' =>
                $messageId,

            'transport' => [
                'host' =>
                    (string) $smtp['host'],

                'port' =>
                    (int) $smtp['port'],

                'encryption' =>
                    (string) $smtp['encryption'],
            ],
        ];
    }

    protected function resolveConnection(
        Website $website,
        object $mailbox
    ): array {
        $settings =
            $this->decodeSettings(
                $mailbox->settings
                    ?? null
            );

        $managed =
            (bool) (
                $settings['managed_by_esubiz']
                ?? false
            );

        if ($managed) {
            return $this->resolveManagedConnection(
                $website,
                $mailbox,
                $settings
            );
        }

        return $this->resolveExternalConnection(
            $mailbox,
            $settings
        );
    }

    protected function resolveExternalConnection(
        object $mailbox,
        array $settings
    ): array {
        $smtp =
            $settings['connection']['smtp']
                ?? null;

        if (!is_array($smtp)) {
            throw new RuntimeException(
                'SMTP settings are missing for this email address.'
            );
        }

        $encrypted =
            trim(
                (string) (
                    $mailbox->credential_secret
                    ?? ''
                )
            );

        if ($encrypted === '') {
            throw new RuntimeException(
                'The mailbox credential is unavailable.'
            );
        }

        try {
            $password =
                Crypt::decryptString(
                    $encrypted
                );
        } catch (\Throwable $e) {
            throw new RuntimeException(
                'The mailbox credential could not be decrypted.',
                0,
                $e
            );
        }

        return [
            'smtp' =>
                $this->normalizeSmtp($smtp),

            'username' =>
                trim(
                    (string) (
                        $mailbox->username
                        ?? $mailbox->email
                    )
                ),

            'password' => $password,
        ];
    }

    protected function resolveManagedConnection(
        Website $website,
        object $mailbox,
        array $settings
    ): array {
        $centralMailboxId =
            (int) (
                $settings['central_mailbox_id']
                ?? 0
            );

        if ($centralMailboxId < 1) {
            throw new RuntimeException(
                'The managed mailbox is not linked to Central.'
            );
        }

        $centralMailbox =
            WebsiteMailbox::query()
                ->whereKey($centralMailboxId)
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
                'The Central managed mailbox is unavailable.'
            );
        }

        $password =
            (string) (
                $centralMailbox->password
                ?? ''
            );

        if ($password === '') {
            throw new RuntimeException(
                'The managed mailbox credential is unavailable.'
            );
        }

        /*
         * Managed SaaS SMTP parameters may be supplied in the tenant
         * registry when Central provisions/synchronizes the mailbox.
         *
         * We intentionally do not guess DirectAdmin/Exim ports here.
         */
        /*
         * ESUBIZ_CORE_MANAGED_SMTP_PLATFORM_CONNECTION_V1
         *
         * Managed SaaS mailbox credentials come from Central while
         * public SMTP connection parameters are Esubiz infrastructure.
         * Tenant-specific synchronized settings remain compatible,
         * but are not required for managed mailboxes.
         */
        $smtp =
            $settings['connection']['smtp']
                ?? config(
                    'esubiz_mail.managed_mail.smtp'
                );

        if (!is_array($smtp)) {
            throw new RuntimeException(
                'The Esubiz managed SMTP connection is unavailable.'
            );
        }

        return [
            'smtp' =>
                $this->normalizeSmtp($smtp),

            'username' =>
                trim(
                    (string) (
                        $centralMailbox->email_address
                        ?? $mailbox->email
                    )
                ),

            'password' => $password,
        ];
    }

    protected function makeTransport(
        array $smtp,
        string $username,
        string $password
    ): EsmtpTransport {
        $host =
            (string) $smtp['host'];

        $port =
            (int) $smtp['port'];

        $encryption =
            (string) $smtp['encryption'];

        /*
         * Symfony's third constructor argument means implicit TLS.
         * STARTTLS remains available to EsmtpTransport for normal
         * submission ports such as 587.
         */
        $implicitTls =
            $encryption === 'ssl';

        $transport =
            new EsmtpTransport(
                $host,
                $port,
                $implicitTls
            );

        $transport->setUsername($username);
        $transport->setPassword($password);

        return $transport;
    }

    protected function buildMessage(
        Website $website,
        string $from,
        array $message
    ): Email {
        $to =
            $this->normalizeAddresses(
                $message['to']
                    ?? []
            );

        if ($to === []) {
            throw new RuntimeException(
                'At least one recipient is required.'
            );
        }

        $email = new Email();

        $displayName =
            trim(
                (string) (
                    $message['from_name']
                    ?? $website->website_name
                    ?? $website->name
                    ?? ''
                )
            );

        $email->from(
            new Address(
                $from,
                $displayName
            )
        );

        $email->to(
            ...$this->addresses($to)
        );

        $cc =
            $this->normalizeAddresses(
                $message['cc']
                    ?? []
            );

        if ($cc !== []) {
            $email->cc(
                ...$this->addresses($cc)
            );
        }

        $bcc =
            $this->normalizeAddresses(
                $message['bcc']
                    ?? []
            );

        if ($bcc !== []) {
            $email->bcc(
                ...$this->addresses($bcc)
            );
        }

        $replyTo =
            $this->normalizeAddresses(
                $message['reply_to']
                    ?? []
            );

        if ($replyTo !== []) {
            $email->replyTo(
                ...$this->addresses($replyTo)
            );
        }

        $email->subject(
            trim(
                (string) (
                    $message['subject']
                    ?? ''
                )
            )
        );

        /*
         * ESUBIZ_CORE_SMTP_THREAD_HEADERS_V2
         */
        $inReplyTo =
            $this->safeThreadHeader(
                $message['in_reply_to']
                    ?? null
            );

        if ($inReplyTo !== null) {
            $email->getHeaders()
                ->addTextHeader(
                    'In-Reply-To',
                    $inReplyTo
                );
        }

        $references =
            $this->safeThreadHeader(
                $message[
                    'message_references'
                ]
                    ?? null
            );

        if ($references !== null) {
            $email->getHeaders()
                ->addTextHeader(
                    'References',
                    $references
                );
        }

        $html =
            (string) (
                $message['body_html']
                ?? ''
            );

        $text =
            (string) (
                $message['body']
                ?? ''
            );

        if ($html !== '') {
            $email->html($html);

            if ($text !== '') {
                $email->text($text);
            }
        } else {
            $email->text($text);
        }

        foreach (
            $message['attachments']
                ?? []
            as $attachment
        ) {
            if (!is_array($attachment)) {
                continue;
            }

            $path =
                (string) (
                    $attachment['path']
                    ?? ''
                );

            if (
                $path === ''
                || !is_file($path)
            ) {
                throw new RuntimeException(
                    'A message attachment is unavailable.'
                );
            }

            $email->attachFromPath(
                $path,
                (string) (
                    $attachment['name']
                    ?? basename($path)
                ),
                $attachment['mime_type']
                    ?? null
            );
        }

        return $email;
    }

    protected function normalizeSmtp(
        array $smtp
    ): array {
        $host =
            strtolower(
                trim(
                    (string) (
                        $smtp['host']
                        ?? ''
                    )
                )
            );

        $port =
            (int) (
                $smtp['port']
                ?? 0
            );

        $encryption =
            strtolower(
                trim(
                    (string) (
                        $smtp['encryption']
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
                'The SMTP server configuration is invalid.'
            );
        }

        if (
            !in_array(
                $encryption,
                [
                    'ssl',
                    'tls',
                    'none',
                ],
                true
            )
        ) {
            throw new RuntimeException(
                'The SMTP security mode is invalid.'
            );
        }

        return [
            'host' => $host,
            'port' => $port,
            'encryption' => $encryption,

            'verify_peer' =>
                array_key_exists(
                    'verify_peer',
                    $smtp
                )
                    ? (bool) $smtp['verify_peer']
                    : true,
        ];
    }

    protected function normalizeAddresses(
        mixed $addresses
    ): array {
        if (is_string($addresses)) {
            $addresses =
                preg_split(
                    '/[,;]+/',
                    $addresses
                ) ?: [];
        }

        if (!is_array($addresses)) {
            return [];
        }

        $normalized = [];

        foreach ($addresses as $address) {
            $address =
                strtolower(
                    trim(
                        (string) $address
                    )
                );

            if ($address === '') {
                continue;
            }

            if (
                !filter_var(
                    $address,
                    FILTER_VALIDATE_EMAIL
                )
            ) {
                throw new RuntimeException(
                    "Invalid email address: {$address}"
                );
            }

            $normalized[$address] =
                $address;
        }

        return array_values(
            $normalized
        );
    }

    protected function addresses(
        array $addresses
    ): array {
        return array_map(
            static fn (string $address) =>
                new Address($address),

            $addresses
        );
    }

    protected function decodeSettings(
        mixed $settings
    ): array {
        if (is_array($settings)) {
            return $settings;
        }

        if (
            !is_string($settings)
            || trim($settings) === ''
        ) {
            return [];
        }

        $decoded =
            json_decode(
                $settings,
                true
            );

        return is_array($decoded)
            ? $decoded
            : [];
    }

    protected function safeThreadHeader(
        mixed $value
    ): ?string {
        if (
            !is_scalar($value)
            || trim(
                (string) $value
            ) === ''
        ) {
            return null;
        }

        $value =
            preg_replace(
                '/[\r\n]+/',
                ' ',
                trim(
                    (string) $value
                )
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

        return $value !== ''
            ? $value
            : null;
    }

}
