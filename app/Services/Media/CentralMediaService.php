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

        /*
         * ESUBIZ_CENTRAL_STORE_UNIVERSAL_BRIDGE_V1
         *
         * Existing CentralMediaService callers automatically inherit
         * the universal MIME-aware storage pipeline.
         *
         * Central landlord assets remain isolated under:
         * public / central-media / {folder}
         */

        $folder =
            $this->sanitizeFolder(
                $folder
            );


        /*
         * AI avatars retain their dedicated small-profile policy.
         *
         * Keep the existing 512 x 512 WEBP behaviour rather than
         * treating avatars like ordinary full-size media.
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
         * Everything else now enters the single universal pipeline.
         *
         * Raster images:
         *   optimized WEBP / max edge 1920
         *
         * SVG:
         *   preserved
         *
         * Video/audio:
         *   FFmpeg automatically when central transcoders are installed
         *
         * Other permitted files:
         *   stored unchanged
         */
        return $this->storeMediaToDisk(
            $file,
            'public',
            $this->root
                . '/'
                . $folder,
            [
                'maximum_edge' => 1920,
                'image_quality' => 82,
            ]
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

    /*
     * ESUBIZ_CENTRAL_MEDIA_UNIVERSAL_STORE_V1
     *
     * Universal media-storage entry point for Esubiz.
     *
     * IMPORTANT:
     * - The caller remains responsible for choosing the disk and directory.
     * - This preserves landlord/tenant storage isolation.
     * - Raster images are optimized centrally.
     * - SVG is preserved as SVG.
     * - Video/audio can transparently use FFmpeg once those central
     *   transcoders are installed.
     * - Other files retain their original format.
     */
    /*
     * ESUBIZ_FFMPEG_MEDIA_OPTIMIZATION_V1
     *
     * Universal video/audio optimization.
     *
     * VIDEO
     * - MP4 / H.264
     * - AAC audio
     * - maximum 1920 x 1080
     * - aspect ratio preserved
     * - never upscale
     * - CRF 27
     * - faster preset
     * - faststart enabled
     *
     * AUDIO
     * - M4A / AAC
     * - 128 kbps
     *
     * If FFmpeg fails for any reason, the original upload is preserved
     * rather than blocking the user's upload.
     */

    protected function storeOptimizedVideoToDisk(
        UploadedFile $file,
        string $disk,
        string $directory
    ): string {

        $ffmpeg =
            $this->ffmpegBinary();


        if (!$ffmpeg) {
            return $this->storeOriginalMediaToDisk(
                $file,
                $disk,
                $directory
            );
        }


        $source =
            $file->getRealPath();


        if (
            !$source
            || !is_file(
                $source
            )
        ) {
            return $this->storeOriginalMediaToDisk(
                $file,
                $disk,
                $directory
            );
        }


        $temporaryOutput =
            sys_get_temp_dir()
            . '/'
            . 'esubiz-video-'
            . Str::uuid()
            . '.mp4';


        $filter =
            "scale="
            . "'trunc(min(1920,iw)/2)*2':"
            . "'trunc(min(1080,ih)/2)*2':"
            . "force_original_aspect_ratio=decrease";


        $command =
            escapeshellarg(
                $ffmpeg
            )
            . ' -y'
            . ' -hide_banner'
            . ' -loglevel error'
            . ' -i '
            . escapeshellarg(
                $source
            )
            . ' -map 0:v:0'
            . ' -map 0:a?'
            . ' -vf '
            . escapeshellarg(
                $filter
            )
            . ' -c:v libx264'
            . ' -preset faster'
            . ' -crf 27'
            . ' -pix_fmt yuv420p'
            . ' -c:a aac'
            . ' -b:a 128k'
            . ' -movflags +faststart'
            . ' -map_metadata -1 '
            . escapeshellarg(
                $temporaryOutput
            );


        $output = [];
        $exitCode = 1;


        @exec(
            $command . ' 2>&1',
            $output,
            $exitCode
        );


        if (
            $exitCode !== 0
            || !is_file(
                $temporaryOutput
            )
            || filesize(
                $temporaryOutput
            ) < 1
        ) {

            @unlink(
                $temporaryOutput
            );


            return $this->storeOriginalMediaToDisk(
                $file,
                $disk,
                $directory
            );
        }


        $path =
            trim(
                $directory,
                '/'
            )
            . '/'
            . Str::uuid()
            . '.mp4';


        $stream =
            @fopen(
                $temporaryOutput,
                'rb'
            );


        if (!$stream) {

            @unlink(
                $temporaryOutput
            );


            return $this->storeOriginalMediaToDisk(
                $file,
                $disk,
                $directory
            );
        }


        try {

            $stored =
                Storage::disk(
                    $disk
                )->put(
                    $path,
                    $stream
                );

        } finally {

            if (
                is_resource(
                    $stream
                )
            ) {
                fclose(
                    $stream
                );
            }


            @unlink(
                $temporaryOutput
            );
        }


        if (!$stored) {
            return $this->storeOriginalMediaToDisk(
                $file,
                $disk,
                $directory
            );
        }


        return $path;
    }


    protected function storeOptimizedAudioToDisk(
        UploadedFile $file,
        string $disk,
        string $directory
    ): string {

        $ffmpeg =
            $this->ffmpegBinary();


        if (!$ffmpeg) {
            return $this->storeOriginalMediaToDisk(
                $file,
                $disk,
                $directory
            );
        }


        $source =
            $file->getRealPath();


        if (
            !$source
            || !is_file(
                $source
            )
        ) {
            return $this->storeOriginalMediaToDisk(
                $file,
                $disk,
                $directory
            );
        }


        $temporaryOutput =
            sys_get_temp_dir()
            . '/'
            . 'esubiz-audio-'
            . Str::uuid()
            . '.m4a';


        $command =
            escapeshellarg(
                $ffmpeg
            )
            . ' -y'
            . ' -hide_banner'
            . ' -loglevel error'
            . ' -i '
            . escapeshellarg(
                $source
            )
            . ' -vn'
            . ' -c:a aac'
            . ' -b:a 128k'
            . ' -movflags +faststart'
            . ' -map_metadata -1 '
            . escapeshellarg(
                $temporaryOutput
            );


        $output = [];
        $exitCode = 1;


        @exec(
            $command . ' 2>&1',
            $output,
            $exitCode
        );


        if (
            $exitCode !== 0
            || !is_file(
                $temporaryOutput
            )
            || filesize(
                $temporaryOutput
            ) < 1
        ) {

            @unlink(
                $temporaryOutput
            );


            return $this->storeOriginalMediaToDisk(
                $file,
                $disk,
                $directory
            );
        }


        $path =
            trim(
                $directory,
                '/'
            )
            . '/'
            . Str::uuid()
            . '.m4a';


        $stream =
            @fopen(
                $temporaryOutput,
                'rb'
            );


        if (!$stream) {

            @unlink(
                $temporaryOutput
            );


            return $this->storeOriginalMediaToDisk(
                $file,
                $disk,
                $directory
            );
        }


        try {

            $stored =
                Storage::disk(
                    $disk
                )->put(
                    $path,
                    $stream
                );

        } finally {

            if (
                is_resource(
                    $stream
                )
            ) {
                fclose(
                    $stream
                );
            }


            @unlink(
                $temporaryOutput
            );
        }


        if (!$stored) {
            return $this->storeOriginalMediaToDisk(
                $file,
                $disk,
                $directory
            );
        }


        return $path;
    }


    protected function ffmpegBinary(): ?string
    {
        if (
            !function_exists(
                'exec'
            )
        ) {
            return null;
        }


        $disabled =
            array_map(
                'trim',
                explode(
                    ',',
                    (string) ini_get(
                        'disable_functions'
                    )
                )
            );


        if (
            in_array(
                'exec',
                $disabled,
                true
            )
        ) {
            return null;
        }


        foreach (
            [
                '/usr/bin/ffmpeg',
                '/usr/local/bin/ffmpeg',
                '/bin/ffmpeg',
            ]
            as $binary
        ) {

            if (
                is_file(
                    $binary
                )
                && is_executable(
                    $binary
                )
            ) {
                return $binary;
            }
        }


        $output = [];
        $exitCode = 1;


        @exec(
            'command -v ffmpeg 2>/dev/null',
            $output,
            $exitCode
        );


        if (
            $exitCode === 0
            && !empty(
                $output[0]
            )
        ) {

            $binary =
                trim(
                    $output[0]
                );


            if (
                is_file(
                    $binary
                )
                && is_executable(
                    $binary
                )
            ) {
                return $binary;
            }
        }


        return null;
    }


    /*
     * ESUBIZ_BINARY_IMAGE_OPTIMIZATION_V1
     *
     * Handles image bytes that do not originate from UploadedFile,
     * including AI-generated images.
     *
     * - WEBP output
     * - maximum long edge 1920
     * - preserve aspect ratio
     * - never upscale
     * - quality 82
     */
    public function storeBinaryImageToDisk(
        string $binary,
        string $disk,
        string $directory,
        array $options = []
    ): ?string {

        if (
            $binary === ''
            || !function_exists(
                'imagecreatefromstring'
            )
        ) {
            return null;
        }


        $image =
            @imagecreatefromstring(
                $binary
            );


        if (!$image) {
            return null;
        }


        $sourceWidth =
            imagesx(
                $image
            );

        $sourceHeight =
            imagesy(
                $image
            );


        if (
            $sourceWidth < 1
            || $sourceHeight < 1
        ) {

            imagedestroy(
                $image
            );

            return null;
        }


        $maximumEdge =
            max(
                1,
                (int) (
                    $options['maximum_edge']
                    ?? 1920
                )
            );

        $quality =
            max(
                1,
                min(
                    100,
                    (int) (
                        $options['image_quality']
                        ?? 82
                    )
                )
            );


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


        $target =
            imagecreatetruecolor(
                $targetWidth,
                $targetHeight
            );


        if (!$target) {

            imagedestroy(
                $image
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

        imagefill(
            $target,
            0,
            0,
            $transparent
        );


        imagecopyresampled(
            $target,
            $image,
            0,
            0,
            0,
            0,
            $targetWidth,
            $targetHeight,
            $sourceWidth,
            $sourceHeight
        );


        ob_start();

        $encoded =
            imagewebp(
                $target,
                null,
                $quality
            );

        $webp =
            ob_get_clean();


        imagedestroy(
            $target
        );

        imagedestroy(
            $image
        );


        if (
            !$encoded
            || !is_string(
                $webp
            )
            || $webp === ''
        ) {
            return null;
        }


        $path =
            trim(
                $directory,
                '/'
            )
            . '/'
            . Str::uuid()
            . '.webp';


        $stored =
            Storage::disk(
                $disk
            )->put(
                $path,
                $webp
            );


        return $stored
            ? $path
            : null;
    }


    public function storeMediaToDisk(
        \Illuminate\Http\UploadedFile $file,
        string $disk,
        string $directory,
        array $options = []
    ): string {
        $directory = trim($directory, '/');

        if ($directory === '') {
            throw new \InvalidArgumentException(
                'A media storage directory is required.'
            );
        }

        $mime = strtolower(
            (string) (
                $file->getMimeType()
                ?: $file->getClientMimeType()
                ?: 'application/octet-stream'
            )
        );

        $maximumEdge = max(
            1,
            (int) ($options['maximum_edge'] ?? 1920)
        );

        $imageQuality = min(
            100,
            max(
                1,
                (int) ($options['image_quality'] ?? 82)
            )
        );

        /*
         * Raster image.
         *
         * Existing central image optimizer remains authoritative.
         */
        if (
            in_array(
                $mime,
                [
                    'image/jpeg',
                    'image/jpg',
                    'image/png',
                    'image/webp',
                ],
                true
            )
        ) {
            $optimized = $this->storeOptimizedToDisk(
                $file,
                $disk,
                $directory,
                $maximumEdge,
                $imageQuality
            );

            if ($optimized) {
                return $optimized;
            }

            /*
             * Graceful fallback if GD/optimization is unavailable.
             */
            return $this->storeOriginalMediaToDisk(
                $file,
                $disk,
                $directory
            );
        }

        /*
         * SVG remains vector.
         *
         * We deliberately do not rasterize it.
         */
        if (
            $mime === 'image/svg+xml'
            || strtolower(
                (string) $file->getClientOriginalExtension()
            ) === 'svg'
        ) {
            return $this->storeOriginalMediaToDisk(
                $file,
                $disk,
                $directory
            );
        }

        /*
         * Video.
         *
         * Automatically delegates to the central FFmpeg implementation
         * when that method is installed. Until then, the original media
         * is stored without crossing the caller's storage boundary.
         */
        if (str_starts_with($mime, 'video/')) {
            if (
                method_exists(
                    $this,
                    'storeOptimizedVideoToDisk'
                )
            ) {
                $optimized = $this->storeOptimizedVideoToDisk(
                    $file,
                    $disk,
                    $directory
                );

                if ($optimized) {
                    return $optimized;
                }
            }

            return $this->storeOriginalMediaToDisk(
                $file,
                $disk,
                $directory
            );
        }

        /*
         * Audio.
         */
        if (str_starts_with($mime, 'audio/')) {
            if (
                method_exists(
                    $this,
                    'storeOptimizedAudioToDisk'
                )
            ) {
                $optimized = $this->storeOptimizedAudioToDisk(
                    $file,
                    $disk,
                    $directory
                );

                if ($optimized) {
                    return $optimized;
                }
            }

            return $this->storeOriginalMediaToDisk(
                $file,
                $disk,
                $directory
            );
        }

        /*
         * Documents and every other permitted file type.
         *
         * No transcoding.
         */
        return $this->storeOriginalMediaToDisk(
            $file,
            $disk,
            $directory
        );
    }


    /*
     * Canonical raw-storage fallback.
     *
     * This method intentionally accepts disk + directory from the caller
     * instead of deciding ownership itself.
     */
    protected function storeOriginalMediaToDisk(
        \Illuminate\Http\UploadedFile $file,
        string $disk,
        string $directory
    ): string {
        $directory = trim($directory, '/');

        $extension = strtolower(
            (string) (
                $file->getClientOriginalExtension()
                ?: $file->extension()
                ?: 'bin'
            )
        );

        $extension = preg_replace(
            '/[^a-z0-9]+/',
            '',
            $extension
        ) ?: 'bin';

        $filename =
            (string) \Illuminate\Support\Str::uuid()
            . '.'
            . $extension;

        $stored = \Illuminate\Support\Facades\Storage::disk(
            $disk
        )->putFileAs(
            $directory,
            $file,
            $filename
        );

        if (!$stored) {
            throw new \RuntimeException(
                'Unable to store media file.'
            );
        }

        return $stored;
    }

}
