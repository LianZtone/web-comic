<?php

namespace App\Support;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class ComicMedia
{
    public const MANAGED_BASE = 'assets/komik/uploads';
    private const IMPORT_BASE = 'assets/komik';
    private const ALLOWED_IMAGE_MIME_TYPES = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp',
        'image/gif' => 'gif',
    ];

    public static function storeComicImage(UploadedFile $file, string $comicSlug, string $slot): string
    {
        $directory = public_path(self::MANAGED_BASE.'/'.$comicSlug);
        File::ensureDirectoryExists($directory);

        $extension = self::validatedImageExtension($file, $slot);
        $filename = $slot.'.'.$extension;

        $file->move($directory, $filename);

        return self::MANAGED_BASE.'/'.$comicSlug.'/'.$filename;
    }

    public static function storeCommentImage(UploadedFile $file, string $scope, string $subjectSlug, int $commentId): string
    {
        $directory = public_path(self::MANAGED_BASE.'/'.$subjectSlug.'/comments/'.$scope);
        File::ensureDirectoryExists($directory);

        $extension = self::validatedImageExtension($file, 'comment_image');
        $filename = 'comment-'.$commentId.'.'.$extension;

        foreach (self::ALLOWED_IMAGE_MIME_TYPES as $allowedExtension) {
            $candidate = $directory.'/comment-'.$commentId.'.'.$allowedExtension;

            if (File::exists($candidate)) {
                File::delete($candidate);
            }
        }

        $file->move($directory, $filename);

        return self::MANAGED_BASE.'/'.$subjectSlug.'/comments/'.$scope.'/'.$filename;
    }

    /**
     * @param  array<int, UploadedFile>  $files
     * @param  array<int, string>  $captions
     * @return array<int, array{number:int,caption:string,image:string}>
     */
    public static function storeChapterImages(array $files, string $comicSlug, int $chapterNumber, array $captions = []): array
    {
        $relativeDirectory = self::MANAGED_BASE.'/'.$comicSlug.'/chapter-'.str_pad((string) $chapterNumber, 2, '0', STR_PAD_LEFT);
        $directory = public_path($relativeDirectory);

        File::ensureDirectoryExists($directory);
        File::cleanDirectory($directory);

        return collect(array_values($files))
            ->map(function (UploadedFile $file, int $index) use ($directory, $relativeDirectory, $captions) {
                $pageNumber = $index + 1;
                $extension = self::validatedImageExtension($file, 'page_images');
                $filename = $pageNumber.'.'.$extension;

                $file->move($directory, $filename);

                return [
                    'number' => $pageNumber,
                    'caption' => trim((string) ($captions[$index] ?? '')),
                    'image' => $relativeDirectory.'/'.$filename,
                ];
            })
            ->all();
    }

    /**
     * @param  array<int, string>  $captions
     * @return array<int, array{number:int,caption:string,image:string}>
     */
    public static function importChapterDirectory(string $source, array $captions = []): array
    {
        $directory = self::resolvePublicDirectory($source);
        $files = collect(File::files($directory))
            ->filter(function ($file) {
                $realPath = $file->getRealPath();

                if (! is_string($realPath) || $realPath === '') {
                    return false;
                }

                $dimensions = @getimagesize($realPath);
                $mimeType = File::mimeType($realPath);

                return is_array($dimensions)
                    && ($dimensions[0] ?? 0) > 0
                    && ($dimensions[1] ?? 0) > 0
                    && is_string($mimeType)
                    && array_key_exists(strtolower($mimeType), self::ALLOWED_IMAGE_MIME_TYPES);
            })
            ->sortBy(fn ($file) => $file->getFilename(), SORT_NATURAL | SORT_FLAG_CASE)
            ->values();

        if ($files->isEmpty()) {
            throw ValidationException::withMessages([
                'page_source_folder' => 'Folder chapter tidak berisi gambar yang bisa dipakai.',
            ]);
        }

        $publicRoot = rtrim(str_replace('\\', '/', public_path()), '/');

        return $files->map(function ($file, int $index) use ($captions, $publicRoot) {
            $relative = ltrim(Str::after(str_replace('\\', '/', $file->getPathname()), $publicRoot), '/');

            return [
                'number' => $index + 1,
                'caption' => trim((string) ($captions[$index] ?? '')),
                'image' => $relative,
            ];
        })->all();
    }

    /**
     * @param  array<int, mixed>  $existingPages
     * @param  array<int, string>  $captions
     * @return array<int, mixed>
     */
    public static function mergeExistingPages(array $existingPages, array $captions = []): array
    {
        return collect($existingPages)
            ->values()
            ->map(function ($page, int $index) use ($captions) {
                if (! is_array($page)) {
                    return trim((string) ($captions[$index] ?? $page));
                }

                return [
                    'number' => (int) ($page['number'] ?? ($index + 1)),
                    'caption' => trim((string) ($captions[$index] ?? ($page['caption'] ?? ''))),
                    'image' => (string) ($page['image'] ?? ''),
                ];
            })
            ->all();
    }

    /**
     * @return array<int, string>
     */
    public static function captionsFromText(?string $value): array
    {
        if (! is_string($value) || trim($value) === '') {
            return [];
        }

        return Collection::make(preg_split('/\r\n|\r|\n/', $value))
            ->map(fn (?string $item) => trim((string) $item))
            ->all();
    }

    public static function resolveMediaPath(?string $path): ?string
    {
        if (! is_string($path) || trim($path) === '') {
            return null;
        }

        $path = trim($path);

        if (Str::startsWith($path, ['http://', 'https://'])) {
            return $path;
        }

        return asset(ltrim($path, '/'));
    }

    public static function normalizeMediaReference(?string $path): ?string
    {
        if (! is_string($path)) {
            return null;
        }

        $path = trim($path);

        if ($path === '') {
            return null;
        }

        if (Str::startsWith($path, ['http://', 'https://'])) {
            if (! filter_var($path, FILTER_VALIDATE_URL)) {
                throw ValidationException::withMessages([
                    'media' => 'URL media tidak valid.',
                ]);
            }

            return $path;
        }

        if (
            Str::startsWith($path, ['data:', 'javascript:', 'vbscript:'])
            || str_contains($path, '..')
        ) {
            throw ValidationException::withMessages([
                'media' => 'Path media tidak aman.',
            ]);
        }

        return ltrim($path, '/');
    }

    public static function deleteManagedPath(?string $path): void
    {
        if (! is_string($path) || trim($path) === '' || ! Str::startsWith($path, self::MANAGED_BASE.'/')) {
            return;
        }

        $fullPath = public_path($path);

        if (File::exists($fullPath)) {
            File::delete($fullPath);
        }
    }

    /**
     * @param  array<int, mixed>  $pages
     */
    public static function deleteManagedPages(array $pages): void
    {
        $directories = [];

        foreach ($pages as $page) {
            if (! is_array($page)) {
                continue;
            }

            $image = trim((string) ($page['image'] ?? ''));

            if ($image === '' || ! Str::startsWith($image, self::MANAGED_BASE.'/')) {
                continue;
            }

            $fullPath = public_path($image);
            $directories[] = dirname($fullPath);

            if (File::exists($fullPath)) {
                File::delete($fullPath);
            }
        }

        foreach (array_unique($directories) as $directory) {
            if (
                File::isDirectory($directory)
                && File::files($directory) === []
                && File::directories($directory) === []
            ) {
                File::deleteDirectory($directory);
            }
        }
    }

    public static function pageCaptionFromValue(mixed $page): string
    {
        if (is_array($page)) {
            return trim((string) ($page['caption'] ?? ''));
        }

        return trim((string) $page);
    }

    private static function resolvePublicDirectory(string $source): string
    {
        $candidate = trim($source);

        if ($candidate === '') {
            throw ValidationException::withMessages([
                'page_source_folder' => 'Folder sumber chapter tidak boleh kosong.',
            ]);
        }

        $publicRoot = realpath(public_path());
        $importRoot = realpath(public_path(self::IMPORT_BASE));
        $direct = realpath($candidate);
        $publicRelative = realpath(public_path(ltrim($candidate, '/')));
        $resolved = $direct ?: $publicRelative;

        if (
            ! $publicRoot
            || ! $importRoot
            || ! $resolved
            || ! str_starts_with(str_replace('\\', '/', $resolved), str_replace('\\', '/', $publicRoot))
            || ! str_starts_with(str_replace('\\', '/', $resolved), str_replace('\\', '/', $importRoot))
        ) {
            throw ValidationException::withMessages([
                'page_source_folder' => 'Folder sumber chapter harus berada di dalam public/assets/komik.',
            ]);
        }

        return $resolved;
    }

    private static function validatedImageExtension(UploadedFile $file, string $field): string
    {
        $realPath = $file->getRealPath();
        $mimeType = strtolower((string) $file->getMimeType());

        if (
            ! is_string($realPath)
            || $realPath === ''
            || ! array_key_exists($mimeType, self::ALLOWED_IMAGE_MIME_TYPES)
            || ! is_array(@getimagesize($realPath))
        ) {
            throw ValidationException::withMessages([
                $field => 'File gambar tidak valid atau formatnya tidak diizinkan.',
            ]);
        }

        return self::ALLOWED_IMAGE_MIME_TYPES[$mimeType];
    }
}
