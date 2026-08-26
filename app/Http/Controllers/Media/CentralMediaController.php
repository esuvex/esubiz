<?php

namespace App\Http\Controllers\Media;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;

class CentralMediaController extends Controller
{
    public function show(
        Request $request,
        string $path
    ) {

        $path =
            trim(
                $path,
                '/'
            );


        abort_if(
            $path === ''
            || str_contains(
                $path,
                '..'
            )
            || str_contains(
                $path,
                '\\'
            ),
            404
        );


        $relative =
            'central-media/'
            . $path;


        $disk =
            Storage::disk(
                'public'
            );


        abort_unless(
            $disk->exists(
                $relative
            ),
            404
        );


        $absolute =
            $disk->path(
                $relative
            );


        $mtime =
            filemtime(
                $absolute
            )
            ?: time();


        $size =
            filesize(
                $absolute
            )
            ?: 0;


        $etag =
            '"'
            . sha1(
                $relative
                . '|'
                . $mtime
                . '|'
                . $size
            )
            . '"';


        /*
        |--------------------------------------------------------------------------
        | Browser conditional cache
        |--------------------------------------------------------------------------
        */

        if (
            trim(
                (string) $request
                    ->header(
                        'If-None-Match'
                    )
            )
            === $etag
        ) {

            return response(
                '',
                Response::HTTP_NOT_MODIFIED,
                [
                    'ETag' =>
                        $etag,

                    'Cache-Control' =>
                        'public, max-age=31536000, immutable',

                    'Last-Modified' =>
                        gmdate(
                            'D, d M Y H:i:s',
                            $mtime
                        )
                        . ' GMT',
                ]
            );
        }


        $mime =
            mime_content_type(
                $absolute
            )
            ?: 'application/octet-stream';


        return response()->file(
            $absolute,
            [
                'Content-Type' =>
                    $mime,

                /*
                 * UUID filenames change whenever media is replaced,
                 * therefore they can safely be cached for one year.
                 */
                'Cache-Control' =>
                    'public, max-age=31536000, immutable',

                'ETag' =>
                    $etag,

                'Last-Modified' =>
                    gmdate(
                        'D, d M Y H:i:s',
                        $mtime
                    )
                    . ' GMT',

                'X-Content-Type-Options' =>
                    'nosniff',
            ]
        );
    }
}
