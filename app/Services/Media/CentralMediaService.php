<?php

namespace App\Services\Media;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class CentralMediaService
{
    protected string $root = 'central-media';

    public function store(
        UploadedFile $file,
        string $folder = 'general'
    ): string {
        $folder = $this->sanitizeFolder($folder);

        $extension = strtolower(
            $file->getClientOriginalExtension()
            ?: $file->extension()
            ?: 'bin'
        );

        $filename =
            Str::uuid()->toString()
            . '.'
            . $extension;

        return $file->storeAs(
            $this->root . '/' . $folder,
            $filename,
            'public'
        );
    }


    public function replace(
        ?string $oldPath,
        UploadedFile $file,
        string $folder = 'general'
    ): string {
        $newPath = $this->store(
            $file,
            $folder
        );

        if ($oldPath) {
            $this->delete($oldPath);
        }

        return $newPath;
    }


    public function delete(
        ?string $path
    ): bool {
        $relative = $this->normalizePath(
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

        return Storage::disk('public')
            ->delete($relative);
    }


    public function exists(
        ?string $path
    ): bool {
        $relative = $this->normalizePath(
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

        return Storage::disk('public')
            ->exists($relative);
    }


    public function url(
        ?string $path
    ): ?string {
        $relative = $this->normalizePath(
            $path
        );

        if (
            !$relative
            || !str_starts_with(
                $relative,
                $this->root . '/'
            )
            || !$this->exists($relative)
        ) {
            return null;
        }

        $insideRoot = substr(
            $relative,
            strlen($this->root . '/')
        );

        return rtrim(
            (string) config('app.url'),
            '/'
        )
        . '/media/central/'
        . collect(
            explode('/', $insideRoot)
        )
        ->map(
            fn ($part) => rawurlencode($part)
        )
        ->implode('/');
    }


    public function normalizePath(
        ?string $path
    ): ?string {
        $path = trim(
            (string) $path
        );

        if ($path === '') {
            return null;
        }

        if (
            str_starts_with($path, 'http://')
            || str_starts_with($path, 'https://')
        ) {
            return null;
        }

        $relative = ltrim(
            $path,
            '/'
        );

        if (
            str_starts_with(
                $relative,
                'storage/'
            )
        ) {
            $relative = substr(
                $relative,
                strlen('storage/')
            );
        }

        return $relative;
    }


    protected function sanitizeFolder(
        string $folder
    ): string {
        $parts = array_filter(
            explode(
                '/',
                trim($folder, '/')
            )
        );

        $parts = array_map(
            fn ($part) =>
                Str::slug($part)
                ?: 'media',
            $parts
        );

        return implode('/', $parts)
            ?: 'general';
    }
}
