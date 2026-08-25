<?php

namespace App\Http\Controllers;

use App\Models\Website;
use App\Models\WebsiteTenant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class TenantMediaController extends Controller
{
    protected function currentWebsite(): Website
    {
        $tenant =
            WebsiteTenant::current();

        abort_unless(
            $tenant,
            404,
            'Website tenant not found.'
        );

        return Website::query()
            ->where(
                'id',
                $tenant->website_id
            )
            ->where(
                'status',
                'active'
            )
            ->where(
                'user_enabled',
                true
            )
            ->firstOrFail();
    }


    protected function authorizeCms(): Website
    {
        $website =
            $this->currentWebsite();

        abort_unless(
            session()->get(
                'tenant_cms_authenticated'
            ) === true
            && (int) session()->get(
                'tenant_cms_website_id'
            ) === (int) $website->id,
            403,
            'Please sign in to this website administration area.'
        );

        return $website;
    }


    /**
     * Determine broad media type.
     */
    protected function mediaType(
        ?string $mime
    ): string {
        $mime =
            strtolower(
                (string) $mime
            );

        return match (true) {

            str_starts_with(
                $mime,
                'image/'
            ) =>
                'image',

            str_starts_with(
                $mime,
                'video/'
            ) =>
                'video',

            str_starts_with(
                $mime,
                'audio/'
            ) =>
                'audio',

            default =>
                'file',
        };
    }


    /**
     * Register a physical website file in the shared
     * tenant Media Library.
     */
    protected function registerMedia(
        Website $website,
        string $path,
        string $filename,
        ?string $originalName,
        ?string $mime,
        int $bytes,
        string $source = 'media_library',
        ?string $sourceContext = null
    ): void {
        DB::connection('website_tenant')
            ->table('website_media')
            ->updateOrInsert(
                [
                    'path' =>
                        $path,
                ],
                [
                    'uuid' =>
                        (string)
                        Str::uuid(),

                    'filename' =>
                        $filename,

                    'original_name' =>
                        $originalName,

                    'title' =>
                        pathinfo(
                            $originalName
                            ?: $filename,
                            PATHINFO_FILENAME
                        ),

                    'mime_type' =>
                        $mime,

                    'media_type' =>
                        $this->mediaType(
                            $mime
                        ),

                    'size_bytes' =>
                        $bytes,

                    'source' =>
                        $source,

                    'source_context' =>
                        $sourceContext,

                    'updated_at' =>
                        now(),

                    'created_at' =>
                        now(),
                ]
            );
    }


    /**
     * Upload an image into this website's canonical
     * Media Library.
     *
     * Existing Page Builder / AI callers can keep
     * using the same endpoint.
     */
    public function uploadImage(
        Request $request
    ) {
        $website =
            $this->authorizeCms();

        $request->validate([
            'image' => [
                'required',
                'file',
                'image',
                'mimes:jpg,jpeg,png',
                'max:10240',
            ],

            'source' => [
                'nullable',
                'string',
                'max:50',
            ],

            'source_context' => [
                'nullable',
                'string',
                'max:150',
            ],
        ]);


        $file =
            $request->file(
                'image'
            );

        $extension =
            strtolower(
                $file
                    ->getClientOriginalExtension()
            );

        $filename =
            now()->format(
                'YmdHis'
            )
            . '-'
            . Str::lower(
                Str::random(12)
            )
            . '.'
            . $extension;

        $directory =
            'tenant-websites/'
            . $website->id
            . '/media';

        /*
         * All website media consumes the same storage quota.
         */
        $path =
            Storage::disk('local')
                ->putFileAs(
                    $directory,
                    $file,
                    $filename
                );

        abort_unless(
            $path,
            500,
            'Image could not be stored.'
        );


        $mime =
            $file->getMimeType();

        $bytes =
            (int)
            $file->getSize();


        $this->registerMedia(
            $website,
            $path,
            $filename,
            $file->getClientOriginalName(),
            $mime,
            $bytes,
            $request->input(
                'source',
                'media_library'
            ),
            $request->input(
                'source_context'
            )
        );


        return response()->json([
            'success' =>
                true,

            'path' =>
                $path,

            'url' =>
                route(
                    'tenant.website.media',
                    [
                        'subdomain' =>
                            $website->subdomain,

                        'path' =>
                            $path,
                    ]
                ),

            'filename' =>
                $filename,

            'bytes' =>
                $bytes,

            'mime' =>
                $mime,
        ]);
    }


    /**
     * Public delivery of this website's stored media.
     */
    public function show(
        Request $request,
        string $subdomain,
        string $path
    ) {
        $website =
            $this->currentWebsite();

        $newPrefix =
            'tenant-websites/'
            . $website->id
            . '/media/';

        /*
         * Backward compatibility for older Page Builder media.
         */
        $oldPrefix =
            'websites/'
            . $website->id
            . '/media/';


        abort_unless(
            str_starts_with(
                $path,
                $newPrefix
            )
            || str_starts_with(
                $path,
                $oldPrefix
            ),
            404
        );


        if (
            str_starts_with(
                $path,
                $newPrefix
            )
        ) {
            $disk =
                Storage::disk(
                    'local'
                );
        } else {
            $disk =
                Storage::disk(
                    'public'
                );
        }


        abort_unless(
            $disk->exists(
                $path
            ),
            404,
            'Media file not found.'
        );


        return response()->file(
            $disk->path(
                $path
            ),
            [
                'Content-Type' =>
                    $disk->mimeType(
                        $path
                    )
                    ?: 'application/octet-stream',

                'Cache-Control' =>
                    'public, max-age=31536000',
            ]
        );
    }
}
