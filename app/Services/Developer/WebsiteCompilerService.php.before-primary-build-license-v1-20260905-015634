<?php

namespace App\Services\Developer;

use App\Models\DeveloperBuild;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use RuntimeException;

class WebsiteCompilerService
{
    /**
     * Create an isolated build workspace for a developer project.
     */
    public function createWorkspace(string $projectName): array
    {
        $slug = Str::slug($projectName);

        if ($slug === '') {
            throw new RuntimeException('A valid project name is required.');
        }

        $buildId = $slug . '-' . Str::lower(Str::random(8));

        $root = storage_path('app/developer-builds/' . $buildId);

        File::makeDirectory($root, 0755, true);

        foreach ([
            'src',
            'public',
            'resources',
            'resources/css',
            'resources/js',
            'resources/images',
            'modules',
            'dist',
        ] as $directory) {
            File::makeDirectory($root . '/' . $directory, 0755, true);
        }

        File::put(
            $root . '/project.json',
            json_encode([
                'build_id' => $buildId,
                'project_name' => $projectName,
                'version' => '1.0.0',
                'type' => 'developer-website',
                'entry' => 'src/index.html',
                'output' => 'dist',
            ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . PHP_EOL
        );

        File::put(
            $root . '/src/index.html',
            '<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>' . e($projectName) . '</title>
</head>
<body>
    <main>
        <h1>' . e($projectName) . '</h1>
        <p>Developer website build.</p>
    </main>
</body>
</html>
'
        );

        return [
            'build_id' => $buildId,
            'project_name' => $projectName,
            'path' => $root,
            'dist' => $root . '/dist',
        ];
    }

    /**
     * Compile a developer website workspace into a distributable package.
     */
    public function compile(string $buildId): array
    {
        try {
            return $this->performCompile($buildId);
        } catch (\Throwable $e) {
            $build = DeveloperBuild::where('build_id', $buildId)->first();

            if ($build) {
                $build->update([
                    'status' => 'failed',
                    'error_message' => $e->getMessage(),
                    'completed_at' => now(),
                ]);
            }

            throw $e;
        }
    }

    /**
     * Perform the actual compilation.
     */
    protected function performCompile(string $buildId): array
    {
        $root = storage_path('app/developer-builds/' . $buildId);
        $source = $root . '/src';
        $dist = $root . '/dist';

        $build = DeveloperBuild::where('build_id', $buildId)->first();

        if ($build) {
            $build->update([
                'status' => 'building',
                'started_at' => now(),
            ]);
        }

        if (!File::isDirectory($root)) {
            throw new RuntimeException('Build workspace not found.');
        }

        if (!File::isDirectory($source)) {
            throw new RuntimeException('Build source directory not found.');
        }

        File::deleteDirectory($dist);
        File::makeDirectory($dist, 0755, true);

        File::copyDirectory($source, $dist);

        $manifest = $root . '/project.json';

        if (!File::exists($manifest)) {
            throw new RuntimeException('Project manifest not found.');
        }

        $zipDirectory = storage_path('app/developer-builds/packages');

        File::makeDirectory($zipDirectory, 0755, true);

        $zipPath = $zipDirectory . '/' . $buildId . '.zip';

        if (File::exists($zipPath)) {
            File::delete($zipPath);
        }

        $zip = new \ZipArchive();

        if ($zip->open($zipPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) !== true) {
            throw new RuntimeException('Unable to create build package.');
        }

        $files = File::allFiles($dist);

        foreach ($files as $file) {
            $relativePath = str_replace(
                $dist . DIRECTORY_SEPARATOR,
                '',
                $file->getPathname()
            );

            $zip->addFile($file->getPathname(), $relativePath);
        }

        $zip->close();

        $build = DeveloperBuild::where('build_id', $buildId)->first();

        if ($build) {
            $build->update([
                'status' => 'success',
                'files_count' => count($files),
                'package_size' => File::size($zipPath),
                'package_reference' => $zipPath,
                'completed_at' => now(),
            ]);
        }

        return [
            'build_id' => $buildId,
            'dist' => $dist,
            'package' => $zipPath,
            'files' => count($files),
            'size' => File::size($zipPath),
        ];
    }
}
