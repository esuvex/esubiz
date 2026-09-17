<?php

namespace App\Services\Core;

use App\Models\Website;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;

/**
 * ESUBIZ_CORE_MAILBOX_ATTACHMENT_STORAGE_V1
 *
 * Canonical physical storage owner for Core email attachments.
 *
 * Files live inside the owning website storage namespace:
 *
 * tenant-websites/{website_id}/mail/{mailbox_id}/attachments/{message_id}/...
 *
 * email_attachments.mailbox_locator stores the physical locator.
 *
 * Proxy systems must reference the canonical email/attachment records
 * instead of storing another physical copy.
 *
 * IMPORTANT:
 * Email attachments preserve their original bytes. They are deliberately
 * not passed through website media optimization/transcoding.
 */
class CoreMailboxAttachmentService
{
    private const DISK = 'local';

    public function store(
        Website $website,
        int $mailboxId,
        int $messageId,
        UploadedFile $file
    ): int {
        $originalName =
            trim($file->getClientOriginalName());

        if ($originalName === '') {
            $originalName = 'attachment';
        }

        $extension = strtolower(
            (string) $file->getClientOriginalExtension()
        );

        $filename =
            now()->format('YmdHis')
            . '-'
            . Str::lower(Str::random(20))
            . (
                $extension !== ''
                    ? '.' . $extension
                    : ''
            );

        $directory =
            'tenant-websites/'
            . (int) $website->id
            . '/mail/'
            . $mailboxId
            . '/attachments/'
            . $messageId;

        $path = Storage::disk(self::DISK)
            ->putFileAs(
                $directory,
                $file,
                $filename
            );

        if (!$path) {
            throw new RuntimeException(
                'Email attachment could not be stored.'
            );
        }

        try {
            $bytes = (int) (
                Storage::disk(self::DISK)
                    ->size($path)
                ?: $file->getSize()
                ?: 0
            );

            $mime =
                $file->getMimeType()
                ?: $file->getClientMimeType()
                ?: 'application/octet-stream';

            $id = DB::connection('website_tenant')
                ->table('email_attachments')
                ->insertGetId([
                    'email_message_id' =>
                        $messageId,

                    'provider_attachment_id' =>
                        null,

                    'original_name' =>
                        mb_substr(
                            $originalName,
                            0,
                            255
                        ),

                    'mime_type' =>
                        mb_substr(
                            (string) $mime,
                            0,
                            191
                        ),

                    'size_bytes' =>
                        max(0, $bytes),

                    'mailbox_locator' =>
                        $path,

                    'created_at' =>
                        now(),

                    'updated_at' =>
                        now(),
                ]);

            DB::connection('website_tenant')
                ->table('email_messages')
                ->where('id', $messageId)
                ->where(
                    'email_box_id',
                    $mailboxId
                )
                ->update([
                    'has_attachments' =>
                        true,

                    'updated_at' =>
                        now(),
                ]);

            return (int) $id;
        } catch (\Throwable $e) {
            Storage::disk(self::DISK)
                ->delete($path);

            throw $e;
        }
    }

        /**
     * ESUBIZ_CORE_INBOUND_ATTACHMENT_INGEST_V3
     *
     * Store one attachment from a real inbound IMAP message.
     *
     * Uses the same canonical namespace as store():
     *
     * tenant-websites/{website_id}/mail/{mailbox_id}/
     * attachments/{message_id}/...
     *
     * The mailbox remains the single physical attachment owner.
     * Source systems reference the canonical attachment record.
     */
    public function storeInbound(
        Website $website,
        int $mailboxId,
        int $messageId,
        string $originalName,
        ?string $mimeType,
        string $bytes,
        ?string $providerAttachmentId = null
    ): int {
        /*
         * ESUBIZ_CORE_INBOUND_ATTACHMENT_IDEMPOTENCY_V1
         *
         * Validate the canonical message before writing any bytes.
         * Retry idempotency is provider ID first, then a conservative
         * filename + exact byte-size fallback when no provider ID exists.
         */
        $db =
            DB::connection(
                'website_tenant'
            );

        $ownsMessage =
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
                ->exists();

        if (!$ownsMessage) {
            throw new RuntimeException(
                'Inbound attachment canonical message does not belong to the mailbox.'
            );
        }

        $providerAttachmentId =
            $providerAttachmentId !== null
                ? trim(
                    $providerAttachmentId
                )
                : '';

        $byteSize =
            strlen(
                $bytes
            );

        $existingQuery =
            $db->table(
                'email_attachments'
            )
                ->where(
                    'email_message_id',
                    $messageId
                );

        if ($providerAttachmentId !== '') {
            $existingQuery
                ->where(
                    'provider_attachment_id',
                    mb_substr(
                        $providerAttachmentId,
                        0,
                        255
                    )
                );
        } else {
            $existingQuery
                ->whereNull(
                    'provider_attachment_id'
                )
                ->where(
                    'original_name',
                    mb_substr(
                        trim(
                            $originalName
                        ) !== ''
                            ? trim(
                                $originalName
                            )
                            : 'attachment',
                        0,
                        255
                    )
                )
                ->where(
                    'size_bytes',
                    $byteSize
                );
        }

        $existingAttachment =
            $existingQuery->first();

        if ($existingAttachment) {
            $existingLocator =
                trim(
                    (string) (
                        $existingAttachment
                            ->mailbox_locator
                            ?? ''
                    )
                );

            /*
             * Metadata plus a real physical object means this retry
             * is already complete.
             */
            if (
                $existingLocator !== ''
                && Storage::disk(
                    self::DISK
                )->exists(
                    $existingLocator
                )
            ) {
                return (int)
                    $existingAttachment->id;
            }

            /*
             * Stale metadata must not permanently block recovery.
             * Remove only this attachment metadata row; the new bytes
             * will be written below and a fresh locator created.
             */
            $db->table(
                'email_attachments'
            )
                ->where(
                    'id',
                    (int)
                        $existingAttachment->id
                )
                ->where(
                    'email_message_id',
                    $messageId
                )
                ->delete();
        }


        $originalName =
            trim(
                $originalName
            );

        if ($originalName === '') {
            $originalName =
                'attachment';
        }

        $extension =
            strtolower(
                pathinfo(
                    $originalName,
                    PATHINFO_EXTENSION
                )
            );

        $extension =
            preg_replace(
                '/[^a-z0-9]+/',
                '',
                $extension
            )
                ?: '';

        $filename =
            now()->format(
                'YmdHis'
            )
            . '-'
            . Str::lower(
                Str::random(20)
            )
            . (
                $extension !== ''
                    ? '.'
                        . mb_substr(
                            $extension,
                            0,
                            20
                        )
                    : ''
            );

        /*
         * Exact same physical namespace as store().
         * Original attachment bytes are preserved.
         */
        $directory =
            'tenant-websites/'
            . (int) $website->id
            . '/mail/'
            . $mailboxId
            . '/attachments/'
            . $messageId;

        $path =
            $directory
            . '/'
            . $filename;

        $written =
            Storage::disk(
                self::DISK
            )->put(
                $path,
                $bytes
            );

        if (!$written) {
            throw new RuntimeException(
                'Inbound email attachment could not be stored.'
            );
        }

        try {
            $id =
                DB::connection(
                    'website_tenant'
                )
                    ->table(
                        'email_attachments'
                    )
                    ->insertGetId([
                        'email_message_id' =>
                            $messageId,

                        'provider_attachment_id' =>
                            $providerAttachmentId !== ''
                                ? mb_substr(
                                    $providerAttachmentId,
                                    0,
                                    255
                                )
                                : null,

                        'original_name' =>
                            mb_substr(
                                $originalName,
                                0,
                                255
                            ),

                        'mime_type' =>
                            mb_substr(
                                trim(
                                    (string) (
                                        $mimeType
                                        ?: 'application/octet-stream'
                                    )
                                ),
                                0,
                                191
                            ),

                        'size_bytes' =>
                            $byteSize,

                        'mailbox_locator' =>
                            $path,

                        'created_at' =>
                            now(),

                        'updated_at' =>
                            now(),
                    ]);

            DB::connection(
                'website_tenant'
            )
                ->table(
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
                ->update([
                    'has_attachments' =>
                        true,

                    'updated_at' =>
                        now(),
                ]);

            return (int) $id;
        } catch (\Throwable $e) {
            Storage::disk(
                self::DISK
            )->delete(
                $path
            );

            throw $e;
        }
    }

public function remove(
        int $mailboxId,
        int $messageId,
        int $attachmentId
    ): bool {
        $db =
            DB::connection('website_tenant');

        $attachment = $db
            ->table('email_attachments')
            ->join(
                'email_messages',
                'email_messages.id',
                '=',
                'email_attachments.email_message_id'
            )
            ->where(
                'email_attachments.id',
                $attachmentId
            )
            ->where(
                'email_attachments.email_message_id',
                $messageId
            )
            ->where(
                'email_messages.email_box_id',
                $mailboxId
            )
            ->where(
                'email_messages.folder',
                'draft'
            )
            ->select([
                'email_attachments.id',
                'email_attachments.mailbox_locator',
            ])
            ->first();

        if (!$attachment) {
            return false;
        }

        $locator =
            trim(
                (string) (
                    $attachment->mailbox_locator
                    ?? ''
                )
            );

        /*
         * Delete metadata only after the physical file was either
         * successfully removed or no physical locator exists.
         */
        if (
            $locator !== ''
            && Storage::disk(self::DISK)
                ->exists($locator)
            && !Storage::disk(self::DISK)
                ->delete($locator)
        ) {
            throw new RuntimeException(
                'Email attachment could not be removed.'
            );
        }

        $deleted = $db
            ->table('email_attachments')
            ->where(
                'id',
                (int) $attachment->id
            )
            ->where(
                'email_message_id',
                $messageId
            )
            ->delete();

        $remaining = $db
            ->table('email_attachments')
            ->where(
                'email_message_id',
                $messageId
            )
            ->exists();

        $db->table('email_messages')
            ->where('id', $messageId)
            ->where(
                'email_box_id',
                $mailboxId
            )
            ->update([
                'has_attachments' =>
                    $remaining,

                'updated_at' =>
                    now(),
            ]);

        return $deleted > 0;
    }
}
