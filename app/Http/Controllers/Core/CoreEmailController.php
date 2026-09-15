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
use Illuminate\Support\Facades\Schema;

class CoreEmailController extends Controller
{
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
                'max:10',
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
}
