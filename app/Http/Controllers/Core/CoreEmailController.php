<?php

namespace App\Http\Controllers\Core;

use App\Http\Controllers\Controller;
use App\Models\Website;
use App\Services\Core\CoreMailGateway;
use App\Services\Core\CoreMailboxAttachmentService;
use App\Services\Core\EmailServiceSettingService;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Schema;

class CoreEmailController extends Controller
{

    /**
     * ESUBIZ_CORE_EXTERNAL_MAILBOX_CONNECT_V1
     *
     * Off-server Core mailbox registration.
     *
     * Normal flow:
     * email + password -> automatic SMTP/IMAP discovery ->
     * authenticate BOTH -> encrypt credential -> register mailbox.
     *
     * If automatic discovery/authentication fails, the client receives
     * manual_required=true and may retry with explicit SMTP/IMAP
     * connection parameters.
     *
     * SaaS must never use this endpoint. SaaS mailboxes are provisioned
     * through the Central-owned managed-mailbox architecture.
     */
    public function connectExternalMailbox(Request $request)
    {
        $website = $this->centralWebsite($request);

        abort_if(
            $website->isSaas(),
            403,
            'External mailbox connection is available only to off-server Core installations.'
        );

        $validated = $request->validate([
            'email' => [
                'required',
                'email:rfc',
                'max:255',
            ],

            'password' => [
                'required',
                'string',
                'max:4096',
            ],

            'manual' => [
                'nullable',
                'boolean',
            ],

            'smtp_host' => [
                'nullable',
                'string',
                'max:255',
            ],

            'smtp_port' => [
                'nullable',
                'integer',
                'min:1',
                'max:65535',
            ],

            'smtp_encryption' => [
                'nullable',
                'in:ssl,tls,none',
            ],

            'imap_host' => [
                'nullable',
                'string',
                'max:255',
            ],

            'imap_port' => [
                'nullable',
                'integer',
                'min:1',
                'max:65535',
            ],

            'imap_encryption' => [
                'nullable',
                'in:ssl,tls,none',
            ],
        ]);

        $email = strtolower(
            trim((string) $validated['email'])
        );

        $password =
            (string) $validated['password'];

        $manual =
            (bool) ($validated['manual'] ?? false);

        $schema =
            Schema::connection('website_tenant');

        if (!$schema->hasTable('email_boxes')) {
            return response()->json([
                'ok' => false,
                'message' =>
                    'The Core mailbox registry is unavailable.',
            ], 422);
        }

        if (
            !$schema->hasColumn(
                'email_boxes',
                'credential_secret'
            )
        ) {
            return response()->json([
                'ok' => false,
                'message' =>
                    'The secure mailbox credential update has not been applied to this Core installation.',
            ], 409);
        }

        $db =
            DB::connection('website_tenant');

        if (
            $db->table('email_boxes')
                ->whereRaw(
                    'LOWER(email) = ?',
                    [$email]
                )
                ->exists()
        ) {
            return response()->json([
                'ok' => false,
                'message' =>
                    'This email address is already registered in Core.',
            ], 422);
        }

        $connector = app(
            \App\Services\Core\CoreMailboxAutoConnectionService::class
        );

        try {
            if ($manual) {
                $result = $connector->testManual(
                    $email,
                    $password,
                    [
                        'host' =>
                            $validated['smtp_host']
                                ?? '',

                        'port' =>
                            $validated['smtp_port']
                                ?? 0,

                        'encryption' =>
                            $validated['smtp_encryption']
                                ?? 'tls',

                        'verify_peer' => true,
                    ],
                    [
                        'host' =>
                            $validated['imap_host']
                                ?? '',

                        'port' =>
                            $validated['imap_port']
                                ?? 0,

                        'encryption' =>
                            $validated['imap_encryption']
                                ?? 'ssl',

                        'verify_peer' => true,
                    ]
                );
            } else {
                $result = $connector->connect(
                    $email,
                    $password
                );
            }
        } catch (\Throwable $e) {
            /*
             * ESUBIZ_CORE_MAILBOX_SAFE_CONNECTION_ERRORS_V2
             *
             * Connection libraries can expose server/authentication
             * details in exception strings. Keep those server-side.
             */
            report($e);

            return response()->json([
                'ok' => false,
                'manual_required' => $manual,
                'message' =>
                    $manual
                        ? 'The mailbox could not be authenticated with the supplied server settings.'
                        : 'Automatic mailbox setup could not be completed. Try the manual server settings.',
            ], 422);
        }

        if (!($result['success'] ?? false)) {
            return response()->json([
                'ok' => false,
                'manual_required' =>
                    (bool) (
                        $result['manual_required']
                        ?? true
                    ),

                'message' =>
                    (string) (
                        $result['reason']
                        ?? (
                            $manual
                                ? 'The supplied SMTP/IMAP settings could not authenticate this mailbox.'
                                : 'Automatic mailbox setup could not authenticate both SMTP and IMAP. Enter the server settings manually.'
                        )
                    ),

                'suggested' =>
                    $result['suggested']
                        ?? null,

            ], 422);
        }

        $connection =
            $result['connection']
                ?? null;

        if (
            !is_array($connection)
            || !is_array($connection['smtp'] ?? null)
            || !is_array($connection['imap'] ?? null)
        ) {
            return response()->json([
                'ok' => false,
                'message' =>
                    'The authenticated mailbox connection is incomplete.',
            ], 422);
        }

        $settings = [
            'provider' => 'external',
            'managed_by_esubiz' => false,

            'connection' => [
                'smtp' => [
                    'host' =>
                        (string) (
                            $connection['smtp']['host']
                            ?? ''
                        ),

                    'port' =>
                        (int) (
                            $connection['smtp']['port']
                            ?? 0
                        ),

                    'encryption' =>
                        (string) (
                            $connection['smtp']['encryption']
                            ?? 'tls'
                        ),

                    'verify_peer' => true,
                ],

                'imap' => [
                    'host' =>
                        (string) (
                            $connection['imap']['host']
                            ?? ''
                        ),

                    'port' =>
                        (int) (
                            $connection['imap']['port']
                            ?? 0
                        ),

                    'encryption' =>
                        (string) (
                            $connection['imap']['encryption']
                            ?? 'ssl'
                        ),

                    'verify_peer' => true,
                ],
            ],

            'discovery' => [
                'mode' =>
                    ($result['automatic'] ?? false)
                        ? 'automatic'
                        : 'manual',

                'verified_at' =>
                    now()->toIso8601String(),
            ],
        ];

        /*
         * Encrypt BEFORE persistence.
         *
         * The raw mailbox password must never enter email_boxes.settings,
         * logs, responses or any other plaintext tenant field.
         */
        $encryptedCredential =
            Crypt::encryptString($password);

        try {
            $mailboxId = $db->transaction(
                function () use (
                    $db,
                    $email,
                    $encryptedCredential,
                    $settings
                ) {
                    $now = now();

                    return $db
                        ->table('email_boxes')
                        ->insertGetId([
                            'name' => $email,
                            'email' => $email,
                            'username' => $email,

                            'credential_secret' =>
                                $encryptedCredential,

                            'status' => 'active',

                            'settings' =>
                                json_encode(
                                    $settings,
                                    JSON_UNESCAPED_SLASHES
                                ),

                            'created_at' => $now,
                            'updated_at' => $now,
                        ]);
                }
            );
        } catch (\Throwable $e) {
            report($e);

            return response()->json([
                'ok' => false,
                'message' =>
                    'The mailbox authenticated successfully but Core could not save it.',
            ], 500);
        }

        return response()->json([
            'ok' => true,

            'message' =>
                'Email address connected successfully.',

            'mailbox' => [
                'id' => (int) $mailboxId,
                'email' => $email,

                'connection_mode' =>
                    ($result['automatic'] ?? false)
                        ? 'automatic'
                        : 'manual',
            ],
        ]);
    }

    private const PER_PAGE = 10;

    /**
     * ESUBIZ_CORE_GENERAL_EMAIL_WORKSPACE_V1
     *
     * email_messages is the canonical Core email ledger.
     *
     * Other Core functions may display/reference an email, but they
     * must not create another storage-owning copy of that email or
     * its attachments.
     */
    public function index(Request $request)
    {
        $db = DB::connection('website_tenant');
        $schema = Schema::connection('website_tenant');

        $mailboxes = collect();

        if ($schema->hasTable('email_boxes')) {
            $mailboxes = $db
                ->table('email_boxes')
                ->where('status', 'active')
                ->orderByDesc('is_primary')
                ->orderBy('id')
                ->get();
        }

        $selectedMailboxId = $this->resolveMailboxId(
            $mailboxes,
            $request->integer('mailbox')
        );

        /*
         * ESUBIZ_CORE_EMAIL_SENDER_MODE_V3
         *
         * Sender mode belongs to the Website, not an individual
         * mailbox. Every Core source therefore uses one transport
         * preference while retaining its selected From mailbox.
         */
        $website = $this->centralWebsite($request);

        $emailServiceData = app(
            EmailServiceSettingService::class
        )->dashboardData(
            (int) $website->id,
            1,
            self::PER_PAGE
        );

        $emailSenderTransport =
            $this->emailSenderTransport($website);


        /*
         * ESUBIZ_CORE_EMAIL_SITE_SETTINGS_CONTEXT_V9
         *
         * Email Branding uses the website's own Core site settings.
         * This is portable across SaaS and off-server Core.
         */
        $coreEmailSiteSettings = [];

        try {
            if (
                Schema::connection('website_tenant')
                    ->hasTable('site_settings')
            ) {
                $coreEmailSiteSettings =
                    DB::connection('website_tenant')
                        ->table('site_settings')
                        ->pluck('value', 'key')
                        ->all();
            }
        } catch (\Throwable $e) {
            $coreEmailSiteSettings = [];
        }

return view('tenant.admin.email.index', [
            'website' => $website,
            'coreEmailSiteSettings' => $coreEmailSiteSettings,
            'mailboxes' => $mailboxes,
            'selectedMailboxId' => $selectedMailboxId,
            'emailServiceData' => $emailServiceData,
            'emailSenderTransport' => $emailSenderTransport,
        ]);
    }

    /**
     * ESUBIZ_CORE_EMAIL_AJAX_MESSAGES_V1
     *
     * Folder/search/pagination are always scoped to the selected
     * mailbox. Exactly ten messages are displayed per page.
     */
    public function messages(Request $request)
    {
        abort_unless($request->ajax(), 404);

        $validated = $request->validate([
            'mailbox' => ['required', 'integer', 'min:1'],
            'folder' => ['nullable', 'string', 'max:20'],
            'search' => ['nullable', 'string', 'max:200'],
            'page' => ['nullable', 'integer', 'min:1'],
        ]);

        $db = DB::connection('website_tenant');
        $schema = Schema::connection('website_tenant');

        if (
            !$schema->hasTable('email_boxes') ||
            !$schema->hasTable('email_messages')
        ) {
            return $this->emptyResponse();
        }

        $mailboxId = (int) $validated['mailbox'];

        abort_unless(
            $db->table('email_boxes')
                ->where('id', $mailboxId)
                ->where('status', 'active')
                ->exists(),
            404
        );

        $folder = $this->normalizeFolder(
            (string) ($validated['folder'] ?? 'inbox')
        );

        $page = max(
            1,
            (int) ($validated['page'] ?? 1)
        );

        $search = trim(
            (string) ($validated['search'] ?? '')
        );

        $query = $db
            ->table('email_messages')
            ->where('email_box_id', $mailboxId)
            ->where('folder', $folder);

        if ($search !== '') {
            $like = '%' . $search . '%';

            $query->where(function ($q) use ($like) {
                $q->where('from_address', 'like', $like)
                    ->orWhere('to_addresses', 'like', $like)
                    ->orWhere('subject', 'like', $like)
                    ->orWhere('body', 'like', $like)
                    ->orWhere('source_label', 'like', $like)
                    ->orWhere('source_type', 'like', $like);
            });
        }

        /*
         * Fetch 11 solely to determine whether Next is available.
         * Only the first 10 are rendered.
         */
        $rows = $query
            ->orderByDesc('id')
            ->offset(($page - 1) * self::PER_PAGE)
            ->limit(self::PER_PAGE + 1)
            ->get();

        $hasNext = $rows->count() > self::PER_PAGE;

        $messages = $rows
            ->take(self::PER_PAGE)
            ->values();

        return response()->json([
            'html' => view(
                'tenant.admin.email.partials.messages',
                compact('messages')
            )->render(),
            /*
             * ESUBIZ_CORE_EMAIL_SEARCH_RESPONSE_V5
             *
             * Normal folder rendering continues to consume
             * `html`. The folder-scoped AJAX search dropdown
             * consumes this lightweight normalized array.
             *
             * Both representations come from the exact same
             * mailbox/folder/search query above.
             */
            'messages' => $messages
                ->map(
                    static function ($message) {
                        $direction =
                            (string) (
                                $message->direction
                                    ?? 'inbound'
                            );

                        $counterparty =
                            $direction === 'outbound'
                                ? (
                                    $message
                                        ->to_addresses
                                        ?? ''
                                )
                                : (
                                    $message
                                        ->from_address
                                        ?? ''
                                );

                        return [
                            'id' =>
                                (int) $message->id,

                            'counterparty' =>
                                (string) $counterparty,

                            'subject' =>
                                (string) (
                                    $message->subject
                                        ?: '(No subject)'
                                ),

                            'source_label' =>
                                (string) (
                                    $message
                                        ->source_label
                                        ?? 'Direct Email'
                                ),

                            'has_attachments' =>
                                (bool) (
                                    $message
                                        ->has_attachments
                                        ?? false
                                ),

                            'created_at' =>
                                $message->created_at
                                    ?? null,
                        ];
                    }
                )
                ->values()
                ->all(),

            'page' => $page,
            'has_previous' => $page > 1,
            'has_next' => $hasNext,
        ]);
    }

    /**
     * ESUBIZ_CORE_EMAIL_MESSAGE_VIEW_V1
     *
     * One canonical message-detail endpoint for folder rows
     * and folder-scoped AJAX search results.
     *
     * A message must belong to the explicitly selected active
     * mailbox. This prevents cross-mailbox message access.
     */
    public function message(Request $request, int $message)
    {
        abort_unless($request->ajax(), 404);

        $validated = $request->validate([
            'mailbox' => [
                'required',
                'integer',
                'min:1',
            ],
        ]);

        $mailboxId =
            (int) $validated['mailbox'];

        $db =
            DB::connection('website_tenant');

        $schema =
            Schema::connection('website_tenant');

        abort_unless(
            $schema->hasTable('email_boxes')
                && $schema->hasTable(
                    'email_messages'
                ),
            404
        );

        abort_unless(
            $db->table('email_boxes')
                ->where('id', $mailboxId)
                ->where('status', 'active')
                ->exists(),
            404
        );

        $row = $db
            ->table('email_messages')
            ->where('id', $message)
            ->where(
                'email_box_id',
                $mailboxId
            )
            ->first();

        abort_unless($row, 404);

        /*
         * Opening an inbound message marks the canonical
         * mailbox ledger row as read. No second copy exists.
         */
        if (
            ($row->direction ?? 'inbound')
                === 'inbound'
            && empty($row->read_at)
        ) {
            $readAt = now();

            $db->table('email_messages')
                ->where('id', $row->id)
                ->where(
                    'email_box_id',
                    $mailboxId
                )
                ->update([
                    'read_at' => $readAt,
                ]);

            $row->read_at = $readAt;
        }

        $attachments = collect();

        if (
            $schema->hasTable(
                'email_attachments'
            )
        ) {
            $attachments = $db
                ->table('email_attachments')
                ->where(
                    'email_message_id',
                    $row->id
                )
                ->orderBy('id')
                ->get();
        }

        return response()->json([
            'ok' => true,
            'message' => [
                'id' =>
                    (int) $row->id,

                'direction' =>
                    (string) (
                        $row->direction
                            ?? 'inbound'
                    ),

                'folder' =>
                    (string) (
                        $row->folder
                            ?? 'inbox'
                    ),

                'from' =>
                    (string) (
                        $row->from_address
                            ?? ''
                    ),

                'to' =>
                    (string) (
                        $row->to_addresses
                            ?? ''
                    ),

                'cc' =>
                    (string) (
                        $row->cc_addresses
                            ?? ''
                    ),

                'bcc' =>
                    (string) (
                        $row->bcc_addresses
                            ?? ''
                    ),

                'reply_to' =>
                    (string) (
                        $row->reply_to
                            ?? ''
                    ),

                'subject' =>
                    (string) (
                        $row->subject
                            ?: '(No subject)'
                    ),

                'body' =>
                    (string) (
                        $row->body
                            ?? ''
                    ),

                'body_html' =>
                    (string) (
                        $row->body_html
                            ?? ''
                    ),

                'source_type' =>
                    (string) (
                        $row->source_type
                            ?? 'direct_email'
                    ),

                'source_label' =>
                    (string) (
                        $row->source_label
                            ?? 'Direct Email'
                    ),

                'read_at' =>
                    $row->read_at
                        ?? null,

                'received_at' =>
                    $row->received_at
                        ?? null,

                'sent_at' =>
                    $row->sent_at
                        ?? null,

                'created_at' =>
                    $row->created_at
                        ?? null,
            ],

            /*
             * Attachment metadata only. Physical bytes remain
             * owned by the canonical mailbox storage layer.
             */
            'attachments' =>
                $attachments
                    ->map(
                        static function ($attachment) {
                            return [
                                'id' =>
                                    (int) $attachment->id,

                                'name' =>
                                    (string) (
                                        $attachment
                                            ->original_name
                                        ?? $attachment
                                            ->filename
                                        ?? 'Attachment'
                                    ),

                                'mime_type' =>
                                    (string) (
                                        $attachment
                                            ->mime_type
                                        ?? ''
                                    ),

                                'size' =>
                                    (int) (
                                        $attachment
                                            ->size_bytes
                                        ?? $attachment
                                            ->size
                                        ?? 0
                                    ),
                            ];
                        }
                    )
                    ->values()
                    ->all(),
        ]);
    }

    /**
     * ESUBIZ_CORE_EMAIL_SAVE_DRAFT_V1
     *
     * Compose drafts use CoreMailGateway so email_messages
     * remains the single canonical mailbox ledger.
     *
     * Attachment persistence is intentionally excluded until
     * the canonical mailbox attachment storage contract is
     * wired. We never create a second attachment copy here.
     */
    /**
     * ESUBIZ_CORE_EMAIL_REAL_SEND_V1
     *
     * Transport one direct email through the selected real mailbox.
     *
     * The canonical email_messages row is created/updated as Draft
     * before transport so attachments always have one stable owner.
     *
     * SMTP failure leaves that same row in Draft.
     * SMTP success converts that same row to Sent.
     */
    public function sendEmail(Request $request)
    {
        $validated = $request->validate([
            'mailbox' => [
                'required',
                'integer',
                'min:1',
            ],
            'to' => [
                'required',
                'string',
                'max:4000',
            ],
            'cc' => [
                'nullable',
                'string',
                'max:4000',
            ],
            'bcc' => [
                'nullable',
                'string',
                'max:4000',
            ],
            'subject' => [
                'nullable',
                'string',
                'max:998',
            ],
            'body' => [
                'nullable',
                'string',
            ],
            'body_html' => [
                'nullable',
                'string',
            ],
            'draft_id' => [
                'nullable',
                'integer',
                'min:1',
            ],
            'attachments' => [
                'nullable',
                'array',
            ],
            'attachments.*' => [
                'file',
                'max:25600',
            ],
        ]);

        $website =
            $this->centralWebsite($request);

        $mailboxId =
            (int) $validated['mailbox'];

        $db =
            DB::connection('website_tenant');

        $schema =
            Schema::connection('website_tenant');

        abort_unless(
            $schema->hasTable('email_boxes')
                && $schema->hasTable(
                    'email_messages'
                )
                && $schema->hasTable(
                    'email_attachments'
                ),
            404
        );

        $mailbox =
            $db->table('email_boxes')
                ->where('id', $mailboxId)
                ->where('status', 'active')
                ->first();

        abort_unless($mailbox, 404);

        $gateway =
            app(CoreMailGateway::class);

        $message = [
            'source_type' =>
                'direct_email',

            'source_label' =>
                'Direct Email',

            'from' =>
                (string) $mailbox->email,

            'to' =>
                $validated['to'],

            'cc' =>
                $validated['cc']
                    ?? '',

            'bcc' =>
                $validated['bcc']
                    ?? '',

            'subject' =>
                $validated['subject']
                    ?? null,

            'body' =>
                $validated['body']
                    ?? null,

            'body_html' =>
                $validated['body_html']
                    ?? null,
        ];

        $draftId =
            (int) (
                $validated['draft_id']
                    ?? 0
            );

        /*
         * Existing Draft:
         * update the canonical row in place.
         *
         * New Compose:
         * create exactly one canonical Draft row before SMTP.
         */
        if ($draftId > 0) {
            $updated =
                $gateway->updateDraft(
                    $draftId,
                    $message,
                    $mailboxId
                );

            if (!$updated) {
                return response()->json([
                    'ok' => false,
                    'message' =>
                        'This draft is unavailable or could not be updated.',
                ], 422);
            }
        } else {
            $draftId =
                (int) (
                    $gateway->saveDraft(
                        $message,
                        $mailboxId
                    )
                    ?? 0
                );

            if ($draftId < 1) {
                return response()->json([
                    'ok' => false,
                    'message' =>
                        'Direct Email is disabled for this mailbox.',
                ], 422);
            }
        }

        /*
         * Store newly uploaded attachments against the canonical
         * Draft row. Existing Draft attachments are not copied.
         */
        $files =
            $request->file(
                'attachments',
                []
            );

        if (!is_array($files)) {
            $files = [$files];
        }

        if ($files !== []) {
            $attachmentService =
                app(
                    CoreMailboxAttachmentService::class
                );

            foreach ($files as $file) {
                if (!$file) {
                    continue;
                }

                $attachmentService->store(
                    $website,
                    $mailboxId,
                    $draftId,
                    $file
                );
            }
        }

        /*
         * Resolve attachment metadata and physical paths from the
         * single mailbox-owned attachment records.
         */
        $attachmentRows =
            $db->table('email_attachments')
                ->where(
                    'email_message_id',
                    $draftId
                )
                ->orderBy('id')
                ->get([
                    'original_name',
                    'mime_type',
                    'mailbox_locator',
                ]);

        $transportAttachments = [];

        foreach (
            $attachmentRows
            as $attachment
        ) {
            $locator =
                ltrim(
                    (string)
                        $attachment
                            ->mailbox_locator,
                    '/'
                );

            if ($locator === '') {
                continue;
            }

            /*
             * CoreMailboxAttachmentService uses Laravel's local disk.
             * Laravel's default local disk root is storage/app/private.
             */
            $physicalPath =
                storage_path(
                    'app/private/'
                    . $locator
                );

            if (!is_file($physicalPath)) {
                report(
                    new \RuntimeException(
                        'Core mailbox attachment is missing: '
                        . $locator
                    )
                );

                return response()->json([
                    'ok' => false,
                    'draft_id' =>
                        $draftId,
                    'message' =>
                        'One or more attachments are unavailable. The email remains in Draft.',
                ], 422);
            }

            $transportAttachments[] = [
                'path' =>
                    $physicalPath,

                'name' =>
                    (string)
                        $attachment
                            ->original_name,

                'mime_type' =>
                    $attachment
                        ->mime_type
                        ?: null,
            ];
        }

        $transportMessage =
            $message;

        $transportMessage['attachments'] =
            $transportAttachments;

        /*
         * Direct Email now converges on the universal Core Mail Engine.
         *
         * The canonical Draft already exists, so sendDraft() performs
         * real SMTP transport and transitions THIS SAME row to Sent.
         */
        try {
            $sendResult =
                app(
                    \App\Services\Core\CoreMailEngine::class
                )->sendDraft(
                    $website,
                    $mailboxId,
                    $draftId,
                    $transportMessage
                );
        } catch (\Throwable $e) {
            report($e);

            return response()->json([
                'ok' =>
                    false,

                'draft_id' =>
                    $draftId,

                'message' =>
                    'Email could not be sent. It has been kept in Draft so you can retry.',
            ], 422);
        }

        if (
            $sendResult[
                'ledger_warning'
            ]
                ?? false
        ) {
            return response()->json([
                'ok' =>
                    true,

                'sent' =>
                    true,

                'ledger_warning' =>
                    true,

                'message_id' =>
                    $draftId,

                'provider_message_id' =>
                    $sendResult[
                        'provider_message_id'
                    ]
                        ?? null,

                'message' =>
                    'Email was sent, but its Sent-folder status needs synchronization.',
            ]);
        }

        return response()->json([
            'ok' =>
                true,

            'sent' =>
                true,

            'message_id' =>
                $draftId,

            'provider_message_id' =>
                $sendResult[
                    'provider_message_id'
                ]
                    ?? null,

            'message' =>
                'Email sent successfully.',
        ]);
    }


    public function saveDraft(Request $request)
    {
        $validated = $request->validate([
            'mailbox' => [
                'required',
                'integer',
                'min:1',
            ],
            'to' => [
                'nullable',
                'string',
                'max:4000',
            ],
            'cc' => [
                'nullable',
                'string',
                'max:4000',
            ],
            'bcc' => [
                'nullable',
                'string',
                'max:4000',
            ],
            'subject' => [
                'nullable',
                'string',
                'max:998',
            ],
            'body' => [
                'nullable',
                'string',
            ],
            'body_html' => [
                'nullable',
                'string',
            ],
            'draft_id' => [
                'nullable',
                'integer',
                'min:1',
            ],
            'attachments' => [
                'nullable',
                'array',
            ],
            'attachments.*' => [
                'file',
                'max:25600',
            ],
        ]);

        $mailboxId =
            (int) $validated['mailbox'];

        $db =
            DB::connection('website_tenant');

        $schema =
            Schema::connection('website_tenant');

        abort_unless(
            $schema->hasTable('email_boxes')
                && $schema->hasTable(
                    'email_messages'
                ),
            404
        );

        $mailbox = $db
            ->table('email_boxes')
            ->where('id', $mailboxId)
            ->where('status', 'active')
            ->first();

        abort_unless($mailbox, 404);

        /*
         * ESUBIZ_CORE_EMAIL_DRAFT_EDIT_V2
         *
         * Existing drafts are updated in place. A missing
         * draft_id continues through the original create path.
         */
        $existingDraftId =
            (int) (
                $validated['draft_id']
                    ?? 0
            );

        $wasExisting =
            $existingDraftId > 0;

        if ($wasExisting) {
            $updated = app(
                CoreMailGateway::class
            )->updateDraft(
                $existingDraftId,
                [
                    'source_type' =>
                        'direct_email',

                    'source_label' =>
                        'Direct Email',

                    'from' =>
                        (string) $mailbox->email,

                    'to' =>
                        $validated['to']
                            ?? '',

                    'cc' =>
                        $validated['cc']
                            ?? '',

                    'bcc' =>
                        $validated['bcc']
                            ?? '',

                    'subject' =>
                        $validated['subject']
                            ?? null,

                    'body' =>
                        $validated['body']
                            ?? null,

                    'body_html' =>
                        $validated['body_html']
                            ?? null,
                ],
                $mailboxId
            );

            if (!$updated) {
                return response()->json([
                    'ok' => false,
                    'message' =>
                        'This draft is unavailable or could not be updated.',
                ], 422);
            }

            $draftId =
                $existingDraftId;
        } else {
            $draftId = app(
                CoreMailGateway::class
            )->saveDraft(
                [
                    'source_type' =>
                        'direct_email',

                    'source_label' =>
                        'Direct Email',

                    'from' =>
                        (string) $mailbox->email,

                    'to' =>
                        $validated['to']
                            ?? '',

                    'cc' =>
                        $validated['cc']
                            ?? '',

                    'bcc' =>
                        $validated['bcc']
                            ?? '',

                    'subject' =>
                        $validated['subject']
                            ?? null,

                    'body' =>
                        $validated['body']
                            ?? null,

                    'body_html' =>
                        $validated['body_html']
                            ?? null,
                ],
                $mailboxId
            );

            if ($draftId === null) {
                return response()->json([
                    'ok' => false,
                    'message' =>
                        'Direct Email is disabled for this mailbox.',
                ], 422);
            }
        }

        /*
         * ESUBIZ_CORE_MAILBOX_ATTACHMENT_STORAGE_V1
         *
         * email_attachments owns attachment metadata.
         * Physical bytes are stored once inside the owning
         * website/mailbox namespace.
         */
        $files =
            $request->file(
                'attachments',
                []
            );

        if (!is_array($files)) {
            $files = [$files];
        }

        if ($files !== []) {
            $website =
                $this->centralWebsite(
                    $request
                );

            $attachmentService =
                app(
                    CoreMailboxAttachmentService::class
                );

            foreach ($files as $file) {
                if (!$file) {
                    continue;
                }

                $attachmentService->store(
                    $website,
                    $mailboxId,
                    (int) $draftId,
                    $file
                );
            }
        }

        return response()->json([
            'ok' => true,
            'draft_id' =>
                (int) $draftId,
            'message' =>
                $wasExisting
                    ? 'Draft updated.'
                    : 'Draft saved.',
        ]);
    }

    /**
     * ESUBIZ_CORE_EMAIL_DRAFT_ATTACHMENT_REMOVE_V1
     *
     * Removal is limited to a Draft belonging to the selected
     * active mailbox.
     */
    public function removeDraftAttachment(
        Request $request,
        int $message,
        int $attachment
    ) {
        abort_unless(
            $request->ajax(),
            404
        );

        $validated =
            $request->validate([
                'mailbox' => [
                    'required',
                    'integer',
                    'min:1',
                ],
            ]);

        $mailboxId =
            (int) $validated['mailbox'];

        $mailbox =
            DB::connection(
                'website_tenant'
            )
                ->table('email_boxes')
                ->where('id', $mailboxId)
                ->where('status', 'active')
                ->first();

        abort_unless(
            $mailbox,
            404
        );

        $removed = app(
            CoreMailboxAttachmentService::class
        )->remove(
            $mailboxId,
            $message,
            $attachment
        );

        abort_unless(
            $removed,
            404
        );

        return response()->json([
            'ok' => true,
            'message' =>
                'Attachment removed.',
        ]);
    }

    /**
     * ESUBIZ_CORE_EMAIL_PREMIUM_AJAX_V3
     */
    public function premiumData(Request $request)
    {
        abort_unless($request->ajax(), 404);

        $validated = $request->validate([
            'page' => ['nullable', 'integer', 'min:1'],
        ]);

        $website = $this->centralWebsite($request);

        return response()->json(
            app(
                EmailServiceSettingService::class
            )->dashboardData(
                (int) $website->id,
                max(
                    1,
                    (int) ($validated['page'] ?? 1)
                ),
                self::PER_PAGE
            )
        );
    }

    /**
     * ESUBIZ_CORE_EMAIL_SENDER_MODE_UPDATE_V3
     */
    public function updateSenderMode(Request $request)
    {
        $validated = $request->validate([
            'transport' => [
                'required',
                'string',
                'in:php,premium_smtp',
            ],
        ]);

        $website = $this->centralWebsite($request);

        $transport = app(
            EmailServiceSettingService::class
        )->normalizeTransport(
            $validated['transport']
        );

        if (
            $transport ===
                CoreMailGateway::TRANSPORT_PREMIUM_SMTP
        ) {
            $premium = app(
                EmailServiceSettingService::class
            )->service('premium');

            abort_unless(
                $premium && $premium->is_enabled,
                422,
                'Esubiz Premium email is currently unavailable.'
            );
        }

        $settings = $this->websiteSettings($website);

        $settings['email_sender_transport'] =
            $transport;

        $website->settings = $settings;
        $website->save();

        return response()->json([
            'ok' => true,
            'transport' => $transport,
        ]);
    }

    private function centralWebsite(
        Request $request
    ): Website {
        $subdomain = trim(
            (string) $request->route('subdomain')
        );

        $website = Website::query()
            ->where('subdomain', $subdomain)
            ->first();

        abort_unless($website, 404);

        return $website;
    }

    private function websiteSettings(
        Website $website
    ): array {
        $settings = $website->settings;

        if (is_array($settings)) {
            return $settings;
        }

        if (is_string($settings)) {
            $decoded = json_decode(
                $settings,
                true
            );

            return is_array($decoded)
                ? $decoded
                : [];
        }

        return [];
    }

    private function emailSenderTransport(
        Website $website
    ): string {
        $settings =
            $this->websiteSettings($website);

        return app(
            EmailServiceSettingService::class
        )->normalizeTransport(
            $settings['email_sender_transport']
                ?? null
        );
    }

    private function resolveMailboxId(
        Collection $mailboxes,
        int $requested
    ): ?int {
        if (
            $requested > 0 &&
            $mailboxes->contains(
                fn ($mailbox) =>
                    (int) $mailbox->id === $requested
            )
        ) {
            return $requested;
        }

        return $mailboxes->isNotEmpty()
            ? (int) $mailboxes->first()->id
            : null;
    }

    private function normalizeFolder(string $folder): string
    {
        $folder = strtolower(trim($folder));

        return in_array(
            $folder,
            ['inbox', 'spam', 'draft', 'sent', 'trash'],
            true
        ) ? $folder : 'inbox';
    }

    private function emptyResponse()
    {
        return response()->json([
            'html' => view(
                'tenant.admin.email.partials.messages',
                ['messages' => collect()]
            )->render(),
            'page' => 1,
            'has_previous' => false,
            'has_next' => false,
        ]);
    }


    /**
     * ESUBIZ_CORE_EMAIL_MAILBOX_ACTIONS_V16
     *
     * Canonical mailbox message mutation boundary.
     *
     * Messages remain owned by email_messages.
     * Every mutation is scoped to the selected website
     * tenant database and mailbox.
     */
    public function messageAction(
        Request $request,
        int $message
    ) {
        $website = $this->centralWebsite($request);

        $validated = $request->validate([
            'mailbox_id' => [
                'required',
                'integer',
                'min:1',
            ],
            'action' => [
                'required',
                'string',
                'in:read,unread,spam,inbox,delete,restore,delete_permanently',
            ],
        ]);

        $mailboxId =
            (int) $validated['mailbox_id'];

        $action =
            (string) $validated['action'];

        $connection =
            DB::connection('website_tenant');

        $mailbox =
            $connection
                ->table('email_boxes')
                ->where('id', $mailboxId)
                ->first();

        if (!$mailbox) {
            abort(404);
        }

        $record =
            $connection
                ->table('email_messages')
                ->where('id', $message)
                ->where('mailbox_id', $mailboxId)
                ->first();

        if (!$record) {
            abort(404);
        }

        $folder =
            strtolower(
                trim(
                    (string) (
                        $record->folder ?? ''
                    )
                )
            );

        $direction =
            strtolower(
                trim(
                    (string) (
                        $record->direction
                        ?? 'inbound'
                    )
                )
            );

        $updates = [
            'updated_at' => now(),
        ];

        if ($action === 'read') {
            $updates['read_at'] = now();
        }

        if ($action === 'unread') {
            $updates['read_at'] = null;
        }

        if ($action === 'spam') {
            if (!in_array(
                $folder,
                ['inbox', 'spam'],
                true
            )) {
                return response()->json([
                    'ok' => false,
                    'message' =>
                        'Only Inbox or Spam messages can be moved to Spam.',
                ], 422);
            }

            $updates['folder'] = 'spam';
        }

        if ($action === 'inbox') {
            if (!in_array(
                $folder,
                ['inbox', 'spam'],
                true
            )) {
                return response()->json([
                    'ok' => false,
                    'message' =>
                        'Only Inbox or Spam messages can be moved to Inbox.',
                ], 422);
            }

            $updates['folder'] = 'inbox';
        }

        if ($action === 'delete') {
            if ($folder === 'trash') {
                return response()->json([
                    'ok' => false,
                    'message' =>
                        'Use Delete Permanently for messages already in Trash.',
                ], 422);
            }

            /*
             * Preserve the canonical origin inside metadata
             * when the schema exposes metadata.
             *
             * If metadata is not available, Restore falls
             * back safely by direction.
             */
            if (
                Schema::connection('website_tenant')
                    ->hasColumn(
                        'email_messages',
                        'metadata'
                    )
            ) {
                $metadata = [];

                if (!empty($record->metadata)) {
                    $decoded =
                        json_decode(
                            (string) $record->metadata,
                            true
                        );

                    if (is_array($decoded)) {
                        $metadata = $decoded;
                    }
                }

                $metadata[
                    'trash_previous_folder'
                ] = $folder;

                $updates['metadata'] =
                    json_encode(
                        $metadata,
                        JSON_UNESCAPED_SLASHES
                        | JSON_UNESCAPED_UNICODE
                    );
            }

            $updates['folder'] = 'trash';
        }

        if ($action === 'restore') {
            if ($folder !== 'trash') {
                return response()->json([
                    'ok' => false,
                    'message' =>
                        'Only Trash messages can be restored.',
                ], 422);
            }

            $restoreFolder = null;

            if (
                Schema::connection('website_tenant')
                    ->hasColumn(
                        'email_messages',
                        'metadata'
                    )
                && !empty($record->metadata)
            ) {
                $decoded =
                    json_decode(
                        (string) $record->metadata,
                        true
                    );

                if (is_array($decoded)) {
                    $candidate =
                        strtolower(
                            trim(
                                (string) (
                                    $decoded[
                                        'trash_previous_folder'
                                    ]
                                    ?? ''
                                )
                            )
                        );

                    if (in_array(
                        $candidate,
                        [
                            'inbox',
                            'spam',
                            'sent',
                            'draft',
                        ],
                        true
                    )) {
                        $restoreFolder =
                            $candidate;
                    }
                }
            }

            if (!$restoreFolder) {
                $restoreFolder =
                    $direction === 'outbound'
                        ? 'sent'
                        : 'inbox';
            }

            $updates['folder'] =
                $restoreFolder;
        }

        if (
            $action ===
            'delete_permanently'
        ) {
            if ($folder !== 'trash') {
                return response()->json([
                    'ok' => false,
                    'message' =>
                        'Only Trash messages can be permanently deleted.',
                ], 422);
            }

            /*
             * Delete mailbox-owned attachment bytes first.
             * Metadata is removed only after the physical
             * mailbox file is addressed.
             */
            $attachments =
                $connection
                    ->table('email_attachments')
                    ->where(
                        'message_id',
                        $message
                    )
                    ->get();

            foreach ($attachments as $attachment) {
                $locator =
                    trim(
                        (string) (
                            $attachment
                                ->mailbox_locator
                            ?? ''
                        )
                    );

                if ($locator !== '') {
                    try {
                        \Illuminate\Support\Facades\Storage
                            ::disk('local')
                            ->delete($locator);
                    } catch (\Throwable $e) {
                        report($e);

                        return response()->json([
                            'ok' => false,
                            'message' =>
                                'An attachment could not be removed safely.',
                        ], 500);
                    }
                }
            }

            $connection->transaction(
                function () use (
                    $connection,
                    $message,
                    $mailboxId
                ) {
                    $connection
                        ->table(
                            'email_attachments'
                        )
                        ->where(
                            'message_id',
                            $message
                        )
                        ->delete();

                    $connection
                        ->table(
                            'email_messages'
                        )
                        ->where(
                            'id',
                            $message
                        )
                        ->where(
                            'mailbox_id',
                            $mailboxId
                        )
                        ->delete();
                }
            );

            return response()->json([
                'ok' => true,
                'action' =>
                    'delete_permanently',
                'message_id' =>
                    $message,
            ]);
        }

        $connection
            ->table('email_messages')
            ->where('id', $message)
            ->where(
                'mailbox_id',
                $mailboxId
            )
            ->update($updates);

        $fresh =
            $connection
                ->table('email_messages')
                ->where('id', $message)
                ->where(
                    'mailbox_id',
                    $mailboxId
                )
                ->first();

        return response()->json([
            'ok' => true,
            'action' => $action,
            'message_id' => $message,
            'folder' =>
                (string) (
                    $fresh->folder ?? ''
                ),
            'read_at' =>
                $fresh->read_at ?? null,
        ]);
    }

}
