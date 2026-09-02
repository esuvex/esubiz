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

        /*
         * ESUBIZ_UNIVERSAL_TENANT_AUTH_REDIRECT_V1
         *
         * Authentication is website-scoped. Old/stale tenant admin URLs
         * redirect to this website's login instead of displaying a 403.
         */
        $websiteId = (int) $website->id;

        $tenantAuthenticated =
            session()->get(
                "tenant_cms_sites.{$websiteId}.authenticated"
            ) === true
            || (
                session()->get('tenant_cms_authenticated') === true
                && (int) session()->get('tenant_cms_website_id')
                    === $websiteId
            );

        if (!$tenantAuthenticated) {
            session()->put(
                'url.intended',
                request()->fullUrl()
            );

            redirect()
                ->to('/login')
                ->send();

            exit;
        }

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
        /*
         * ESUBIZ_TENANT_MEDIA_DATABASE_CONTEXT_V1
         *
         * Media uploads can arrive through their own AJAX request,
         * therefore they cannot assume another controller has already
         * configured Laravel's dynamic website_tenant connection.
         *
         * Establish the owning website's database context immediately
         * before registering the media record. Physical storage remains
         * isolated by the website-specific storage directory.
         */
        app(
            \App\Services\Website\WebsiteTenantDatabaseService::class
        )->connect(
            $website
        );

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
         * ESUBIZ_TENANT_MEDIA_OPTIMIZATION_V1
         *
         * All website media consumes the same storage quota.
         *
         * Images are optimized centrally while remaining inside
         * the website's canonical tenant media directory.
         */
        $path =
            /* ESUBIZ_TENANT_UNIVERSAL_MEDIA_STORE_V1 */
        app(
                \App\Services\Media\CentralMediaService::class
            )->storeMediaToDisk(
                $file,
                'local',
                $directory,
                [
                    'maximum_edge' => 1920,
                    'image_quality' => 82,
                ]
            );


        if (!$path) {

            $path =
                Storage::disk('local')
                    ->putFileAs(
                        $directory,
                        $file,
                        $filename
                    );
        }


        abort_unless(
            $path,
            500,
            'Image could not be stored.'
        );


        /*
         * Use metadata from the final stored asset rather than
         * the original upload because optimized images become WEBP.
         */
        $filename =
            basename(
                $path
            );


        $mime =
            strtolower(
                pathinfo(
                    $filename,
                    PATHINFO_EXTENSION
                )
            ) === 'webp'
                ? 'image/webp'
                : $file->getMimeType();


        $bytes =
            (int) (
                Storage::disk('local')
                    ->size(
                        $path
                    )
                ?: $file->getSize()
            );


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


    /*
     * ESUBIZ_TENANT_VIDEO_UPLOAD_V1
     *
     * Store tenant video through the same canonical media
     * architecture used by the Media Library. CentralMediaService
     * handles FFmpeg optimization/transcoding when available.
     */
    public function uploadVideo(
        Request $request
    ) {
        $website =
            $this->authorizeCms();

        $request->validate([
            'video' => [
                'required',
                'file',
                'mimetypes:video/mp4,video/webm,video/quicktime,video/x-m4v',
                'max:102400',
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
                'video'
            );

        $directory =
            'tenant-websites/'
            . $website->id
            . '/media';

        $path =
            app(
                \App\Services\Media\CentralMediaService::class
            )->storeMediaToDisk(
                $file,
                'local',
                $directory
            );

        if (!$path) {

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
                . (
                    $extension
                        ? '.' . $extension
                        : ''
                );

            $path =
                Storage::disk('local')
                    ->putFileAs(
                        $directory,
                        $file,
                        $filename
                    );
        }

        abort_unless(
            $path,
            500,
            'Video could not be stored.'
        );

        $filename =
            basename(
                $path
            );

        $mime =
            Storage::disk('local')
                ->mimeType(
                    $path
                )
            ?: $file->getMimeType()
            ?: 'video/mp4';

        $bytes =
            (int) (
                Storage::disk('local')
                    ->size(
                        $path
                    )
                ?: $file->getSize()
            );

        $this->registerMedia(
            $website,
            $path,
            $filename,
            $file->getClientOriginalName(),
            $mime,
            $bytes,
            $request->input(
                'source',
                'page_builder'
            ),
            $request->input(
                'source_context',
                'gallery'
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

            'media_type' =>
                'video',
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


        /*
         * ESUBIZ_TENANT_MEDIA_BANDWIDTH_ACCOUNTING_V1
         *
         * Count the actual stored file size whenever this tenant
         * media endpoint successfully serves a file.
         */
        try {
            $bytes =
                (int) $disk->size(
                    $path
                );

            app(
                \App\Services\Website\TenantBandwidthUsageService::class
            )->recordBytes(
                $website,
                $bytes,
                'tenant_media',
                null,
                [
                    'path' => $path,
                    'mime' =>
                        $disk->mimeType(
                            $path
                        ),
                ]
            );
        } catch (\Throwable $e) {
            /*
             * Bandwidth accounting must never prevent a valid
             * tenant asset from being delivered.
             */
        }

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
