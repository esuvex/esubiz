<?php

namespace App\Http\Controllers;

use App\Models\Website;
use App\Models\WebsiteTenant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class TenantMediaController extends Controller
{
    protected function currentWebsite(): Website
    {
        $tenant = WebsiteTenant::current();

        abort_unless(
            $tenant,
            404,
            'Website tenant not found.'
        );

        return Website::query()
            ->where('id', $tenant->website_id)
            ->where('status', 'active')
            ->where('user_enabled', true)
            ->firstOrFail();
    }


    protected function authorizeCms(): Website
    {
        $website = $this->currentWebsite();

        abort_unless(
            session()->get('tenant_cms_authenticated') === true
            && (int) session()->get('tenant_cms_website_id')
                === (int) $website->id,
            403,
            'Please sign in to this website administration area.'
        );

        return $website;
    }


    /**
     * Upload an image into this website's own storage bucket.
     *
     * All website assets will progressively use:
     *
     * tenant-websites/{website_id}/...
     *
     * Media, files, themes, modules and other website-owned
     * assets will therefore consume the same website storage.
     */
    public function uploadImage(Request $request)
    {
        $website = $this->authorizeCms();

        $request->validate([
            'image' => [
                'required',
                'file',
                'image',
                'mimes:jpg,jpeg,png',
                'max:10240',
            ],
        ]);

        $file = $request->file('image');

        $extension = strtolower(
            $file->getClientOriginalExtension()
        );

        $filename =
            now()->format('YmdHis')
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
         * Core storage allocation enforcement will plug in here.
         * Storage Add-ons will increase the same effective quota.
         */

        $path = Storage::disk('local')
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

        return response()->json([
            'success' => true,

            'path' => $path,

            /*
             * Public delivery always goes through the tenant
             * media route. No public/storage symlink required.
             */
            'url' => route(
                'tenant.website.media',
                [
                    'subdomain' => $website->subdomain,
                    'path' => $path,
                ]
            ),

            'filename' => $filename,

            'bytes' => (int) $file->getSize(),

            'mime' => $file->getMimeType(),
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
        $website = $this->currentWebsite();

        $newPrefix =
            'tenant-websites/'
            . $website->id
            . '/media/';

        /*
         * Temporary backward compatibility for images uploaded
         * before the website-storage structure was introduced.
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


        /*
         * New website-specific storage.
         */
        if (
            str_starts_with(
                $path,
                $newPrefix
            )
        ) {
            $disk =
                Storage::disk('local');
        } else {
            $disk =
                Storage::disk('public');
        }


        abort_unless(
            $disk->exists($path),
            404,
            'Media file not found.'
        );


        return response()->file(
            $disk->path($path),
            [
                'Content-Type' =>
                    $disk->mimeType($path)
                    ?: 'application/octet-stream',

                'Cache-Control' =>
                    'public, max-age=31536000',
            ]
        );
    }
}
