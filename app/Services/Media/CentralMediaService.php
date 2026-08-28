<?php

namespace App\Services\Media;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class CentralMediaService
{
    protected string $root =
        'central-media';


    public function store(
        UploadedFile $file,
        string $folder = 'general'
    ): string {

        $folder =
            $this->sanitizeFolder(
                $folder
            );


        /*
        |--------------------------------------------------------------------------
        | AI AVATARS
        |--------------------------------------------------------------------------
        |
        | Official avatars are displayed as small profile images across
        | Central Esubiz and tenant websites.
        |
        | Store them as optimized 512x512 WEBP rather than retaining
        | multi-megabyte PNG uploads.
        |
        */

        if (
            $folder === 'ai/avatars'
            && $this->canOptimizeImage(
                $file
            )
        ) {

            $optimized =
                $this->storeOptimizedImage(
                    $file,
                    $folder,
                    512,
                    512,
                    82
                );


            if ($optimized) {
                return $optimized;
            }
        }


        /*
        |--------------------------------------------------------------------------
        | ESUBIZ_GLOBAL_IMAGE_OPTIMIZATION_V1
        |--------------------------------------------------------------------------
        |
        | Every ordinary JPEG, PNG or WEBP image entering the Central
        | Media service is normalized and optimized before storage.
        |
        | Large source images are reduced to a maximum long edge of
        | 1920px while preserving their original aspect ratio.
        |
        | Smaller images are never enlarged.
        |
        | storeOptimizedImage() handles the final WEBP encoding and
        | metadata-free optimized output.
        |
        */

        if (
            $this->canOptimizeImage(
                $file
            )
        ) {

            $sourcePath =
                $file->getRealPath();

            $imageInfo =
                $sourcePath
                    ? @getimagesize(
                        $sourcePath
                    )
                    : false;

            if (
                is_array(
                    $imageInfo
                )
                && isset(
                    $imageInfo[0],
                    $imageInfo[1]
                )
                && $imageInfo[0] > 0
                && $imageInfo[1] > 0
            ) {

                $sourceWidth =
                    (int) $imageInfo[0];

                $sourceHeight =
                    (int) $imageInfo[1];

                $maximumEdge =
                    1920;

                $largestEdge =
                    max(
                        $sourceWidth,
                        $sourceHeight
                    );

                $scale =
                    min(
                        1,
                        $maximumEdge
                        / $largestEdge
                    );

                $targetWidth =
                    max(
                        1,
                        (int) round(
                            $sourceWidth
                            * $scale
                        )
                    );

                $targetHeight =
                    max(
                        1,
                        (int) round(
                            $sourceHeight
                            * $scale
                        )
                    );

                $optimized =
                    $this->storeOptimizedImage(
                        $file,
                        $folder,
                        $targetWidth,
                        $targetHeight,
                        82
                    );

                if ($optimized) {
                    return $optimized;
                }
            }
        }


        /*
         * Generic Central media fallback.
         *
         * Non-image files and images that GD cannot process
         * retain the original storage behaviour.
         */
        $extension =
            strtolower(
                $file->getClientOriginalExtension()
                ?: $file->extension()
                ?: 'bin'
            );


        $filename =
            Str::uuid()->toString()
            . '.'
            . $extension;


        return $file->storeAs(
            $this->root
            . '/'
            . $folder,
            $filename,
            'public'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | ESUBIZ_CANONICAL_DISK_IMAGE_OPTIMIZATION_V1
    |--------------------------------------------------------------------------
    |
    | Optimize an uploaded website image while preserving the caller's
    | canonical storage disk and directory.
    |
    | Tenant media must remain under:
    |
    | tenant-websites/{website_id}/media/...
    |
    | rather than being moved into central-media.
    |
    */
    public function storeOptimizedToDisk(
        UploadedFile $file,
        string $disk,
        string $directory,
        int $maximumEdge = 1920,
        int $quality = 82
    ): ?string {

        if (
            !$this->canOptimizeImage(
                $file
            )
        ) {
            return null;
        }


        $sourcePath =
            $file->getRealPath();


        $imageInfo =
            $sourcePath
                ? @getimagesize(
                    $sourcePath
                )
                : false;


        if (
            !is_array(
                $imageInfo
            )
            || !isset(
                $imageInfo[0],
                $imageInfo[1]
            )
            || $imageInfo[0] < 1
            || $imageInfo[1] < 1
        ) {
            return null;
        }


        $sourceWidth =
            (int) $imageInfo[0];

        $sourceHeight =
            (int) $imageInfo[1];


        $largestEdge =
            max(
                $sourceWidth,
                $sourceHeight
            );


        $scale =
            min(
                1,
                $maximumEdge
                / $largestEdge
            );


        $targetWidth =
            max(
                1,
                (int) round(
                    $sourceWidth
                    * $scale
                )
            );


        $targetHeight =
            max(
                1,
                (int) round(
                    $sourceHeight
                    * $scale
                )
            );


        return $this->storeOptimizedImage(
            $file,
            'unused',
            $targetWidth,
            $targetHeight,
            $quality,
            $disk,
            trim(
                $directory,
                '/'
            )
        );
    }


    public function replace(
        ?string $oldPath,
        UploadedFile $file,
        string $folder = 'general'
    ): string {

        $newPath =
            $this->store(
                $file,
                $folder
            );


        if ($oldPath) {
            $this->delete(
                $oldPath
            );
        }


        return $newPath;
    }


    public function delete(
        ?string $path
    ): bool {

        $relative =
            $this->normalizePath(
                $path
            );


        if (
            !$relative
            || !str_starts_with(
                $relative,
                $this->root . '/'
            )
        ) {
            return false;
        }


        return Storage::disk(
            'public'
        )->delete(
            $relative
        );
    }


    public function exists(
        ?string $path
    ): bool {

        $relative =
            $this->normalizePath(
                $path
            );


        if (
            !$relative
            || !str_starts_with(
                $relative,
                $this->root . '/'
            )
        ) {
            return false;
        }


        return Storage::disk(
            'public'
        )->exists(
            $relative
        );
    }


    public function url(
        ?string $path
    ): ?string {

        $relative =
            $this->normalizePath(
                $path
            );


        if (
            !$relative
            || !str_starts_with(
                $relative,
                $this->root . '/'
            )
            || !$this->exists(
                $relative
            )
        ) {
            return null;
        }


        $insideRoot =
            substr(
                $relative,
                strlen(
                    $this->root . '/'
                )
            );


        return rtrim(
            (string) config(
                'app.url'
            ),
            '/'
        )
        . '/media/central/'
        . collect(
            explode(
                '/',
                $insideRoot
            )
        )
        ->map(
            fn ($part) =>
                rawurlencode(
                    $part
                )
        )
        ->implode('/');
    }


    public function normalizePath(
        ?string $path
    ): ?string {

        $path =
            trim(
                (string) $path
            );


        if ($path === '') {
            return null;
        }


        if (
            str_starts_with(
                $path,
                'http://'
            )
            || str_starts_with(
                $path,
                'https://'
            )
        ) {
            return null;
        }


        $relative =
            ltrim(
                $path,
                '/'
            );


        if (
            str_starts_with(
                $relative,
                'storage/'
            )
        ) {
            $relative =
                substr(
                    $relative,
                    strlen(
                        'storage/'
                    )
                );
        }


        return $relative;
    }


    /*
    |--------------------------------------------------------------------------
    | IMAGE OPTIMIZATION
    |--------------------------------------------------------------------------
    */

    protected function canOptimizeImage(
        UploadedFile $file
    ): bool {

        if (
            !extension_loaded(
                'gd'
            )
        ) {
            return false;
        }


        $mime =
            strtolower(
                (string) $file->getMimeType()
            );


        return in_array(
            $mime,
            [
                'image/jpeg',
                'image/png',
                'image/webp',
            ],
            true
        );
    }


    protected function storeOptimizedImage(
        UploadedFile $file,
        string $folder,
        int $width,
        int $height,
        int $quality = 82,
        string $disk = 'public',
        ?string $directory = null
    ): ?string {

        $sourcePath =
            $file->getRealPath();


        if (
            !$sourcePath
            || !is_file(
                $sourcePath
            )
        ) {
            return null;
        }


        $info =
            @getimagesize(
                $sourcePath
            );


        if (!$info) {
            return null;
        }


        [$sourceWidth, $sourceHeight] =
            $info;


        if (
            $sourceWidth < 1
            || $sourceHeight < 1
        ) {
            return null;
        }


        $mime =
            strtolower(
                (string) (
                    $info['mime']
                    ?? ''
                )
            );


        $source =
            match ($mime) {

                'image/jpeg' =>
                    @imagecreatefromjpeg(
                        $sourcePath
                    ),

                'image/png' =>
                    @imagecreatefrompng(
                        $sourcePath
                    ),

                'image/webp' =>
                    function_exists(
                        'imagecreatefromwebp'
                    )
                        ? @imagecreatefromwebp(
                            $sourcePath
                        )
                        : false,

                default =>
                    false,
            };


        if (!$source) {
            return null;
        }


        /*
         * Cover-crop into the required dimensions.
         */
        $sourceRatio =
            $sourceWidth
            / $sourceHeight;


        $targetRatio =
            $width
            / $height;


        if (
            $sourceRatio
            > $targetRatio
        ) {

            $cropHeight =
                $sourceHeight;

            $cropWidth =
                (int) round(
                    $sourceHeight
                    * $targetRatio
                );

            $sourceX =
                (int) round(
                    (
                        $sourceWidth
                        - $cropWidth
                    )
                    / 2
                );

            $sourceY =
                0;


        } else {

            $cropWidth =
                $sourceWidth;

            $cropHeight =
                (int) round(
                    $sourceWidth
                    / $targetRatio
                );

            $sourceX =
                0;

            $sourceY =
                (int) round(
                    (
                        $sourceHeight
                        - $cropHeight
                    )
                    / 2
                );
        }


        $target =
            imagecreatetruecolor(
                $width,
                $height
            );


        if (!$target) {

            imagedestroy(
                $source
            );

            return null;
        }


        imagealphablending(
            $target,
            false
        );

        imagesavealpha(
            $target,
            true
        );


        $transparent =
            imagecolorallocatealpha(
                $target,
                0,
                0,
                0,
                127
            );


        imagefilledrectangle(
            $target,
            0,
            0,
            $width,
            $height,
            $transparent
        );


        imagecopyresampled(
            $target,
            $source,
            0,
            0,
            $sourceX,
            $sourceY,
            $width,
            $height,
            $cropWidth,
            $cropHeight
        );


        $filename =
            Str::uuid()->toString()
            . '.webp';


        $baseDirectory =
            $directory !== null
                ? trim(
                    $directory,
                    '/'
                )
                : (
                    $this->root
                    . '/'
                    . $folder
                );


        $relative =
            $baseDirectory
            . '/'
            . $filename;


        $absolute =
            Storage::disk(
                $disk
            )->path(
                $relative
            );


        $directory =
            dirname(
                $absolute
            );


        if (
            !is_dir(
                $directory
            )
        ) {
            mkdir(
                $directory,
                0775,
                true
            );
        }


        $saved =
            function_exists(
                'imagewebp'
            )
            && imagewebp(
                $target,
                $absolute,
                $quality
            );


        imagedestroy(
            $source
        );

        imagedestroy(
            $target
        );


        if (!$saved) {
            return null;
        }


        @chmod(
            $absolute,
            0664
        );


        return $relative;
    }


    protected function sanitizeFolder(
        string $folder
    ): string {

        $parts =
            array_filter(
                explode(
                    '/',
                    trim(
                        $folder,
                        '/'
                    )
                )
            );


        $parts =
            array_map(
                fn ($part) =>
                    Str::slug(
                        $part
                    )
                    ?: 'media',
                $parts
            );


        return implode(
            '/',
            $parts
        )
        ?: 'general';
    }
}
