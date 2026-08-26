<?php

namespace App\Http\Controllers\Media;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class CentralMediaController extends Controller
{
    public function show(
        string $path
    ): BinaryFileResponse {
        $path = trim(
            $path,
            '/'
        );

        abort_if(
            $path === ''
            || str_contains($path, '..')
            || str_contains($path, '\\'),
            404
        );

        $relative =
            'central-media/'
            . $path;

        $disk = Storage::disk(
            'public'
        );

        abort_unless(
            $disk->exists($relative),
            404
        );

        $absolute = $disk->path(
            $relative
        );

        $mime =
            mime_content_type($absolute)
            ?: 'application/octet-stream';

        return response()->file(
            $absolute,
            [
                'Content-Type' => $mime,
                'Cache-Control' =>
                    'public, max-age=86400',
            ]
        );
    }
}
