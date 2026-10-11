<?php

namespace App\Services;

use App\Models\Media;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Laravel\Facades\Image;

class MediaService
{
    protected array $allowedMimes = [
        'image/jpeg',
        'image/png',
        'image/webp',
        'image/avif',
    ];

    protected int $maxBytes = 5 * 1024 * 1024;

    public function upload(UploadedFile $file, ?string $folder = null): Media
    {
        $this->validateFile($file);

        $folder = $this->sanitizeFolder($folder);
        $disk = 'public';
        $baseName = Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)) ?: 'image';
        $unique = $baseName.'-'.Str::lower(Str::random(8));
        $originalExt = strtolower($file->getClientOriginalExtension() ?: 'jpg');

        $directory = $folder.'/'.now()->format('Y/m');
        $storedName = $unique.'.'.$originalExt;
        $path = $file->storeAs($directory, $storedName, $disk);

        $width = null;
        $height = null;
        $finalPath = $path;
        $finalFilename = $storedName;
        $mime = $file->getMimeType() ?: 'image/jpeg';
        $size = $file->getSize() ?: 0;

        try {
            $absolute = Storage::disk($disk)->path($path);
            $image = Image::read($absolute);
            $image->scaleDown(1600, 1600);
            $width = $image->width();
            $height = $image->height();

            $webpName = $unique.'.webp';
            $webpRelative = $directory.'/'.$webpName;
            $webpAbsolute = Storage::disk($disk)->path($webpRelative);
            $image->toWebp(80)->save($webpAbsolute);

            $thumbName = $unique.'_thumb.webp';
            $thumbRelative = $directory.'/'.$thumbName;
            $thumbAbsolute = Storage::disk($disk)->path($thumbRelative);
            Image::read($webpAbsolute)
                ->cover(400, 400)
                ->toWebp(78)
                ->save($thumbAbsolute);

            if (is_file($webpAbsolute)) {
                $finalPath = $webpRelative;
                $finalFilename = $webpName;
                $mime = 'image/webp';
                $size = filesize($webpAbsolute) ?: $size;
                if ($path !== $webpRelative && Storage::disk($disk)->exists($path)) {
                    Storage::disk($disk)->delete($path);
                }
            }
        } catch (\Throwable $e) {
            Log::info('Image optimization skipped', ['error' => $e->getMessage()]);
        }

        return Media::query()->create([
            'folder' => $folder,
            'disk' => $disk,
            'path' => $finalPath,
            'filename' => $finalFilename,
            'mime_type' => $mime,
            'size' => $size,
            'width' => $width,
            'height' => $height,
            'alt' => $baseName,
            'title' => $baseName,
        ]);
    }

    public function delete(Media $media): void
    {
        $disk = $media->disk ?: 'public';
        $path = $media->path;

        if ($path && Storage::disk($disk)->exists($path)) {
            Storage::disk($disk)->delete($path);
        }

        $dir = dirname($path);
        $name = pathinfo($path, PATHINFO_FILENAME);

        foreach ([
            $dir.'/'.$name.'_thumb.webp',
            $dir.'/'.preg_replace('/_thumb$/', '', $name).'_thumb.webp',
            $dir.'/'.pathinfo($media->filename ?? '', PATHINFO_FILENAME).'.webp',
        ] as $related) {
            if ($related && Storage::disk($disk)->exists($related)) {
                Storage::disk($disk)->delete($related);
            }
        }

        $media->delete();
    }

    protected function sanitizeFolder(?string $folder): string
    {
        $folder = str_replace(['\\', "\0"], '', (string) $folder);
        $folder = str_replace('..', '', $folder);
        $folder = trim($folder, '/');

        if ($folder === '' || ! preg_match('/^[a-zA-Z0-9_\-\/]+$/', $folder)) {
            return 'uploads';
        }

        return $folder;
    }

    protected function validateFile(UploadedFile $file): void
    {
        if ($file->getSize() > $this->maxBytes) {
            throw new \InvalidArgumentException('Datei darf maximal 5 MB groß sein.');
        }

        $mime = $file->getMimeType();
        if (! in_array($mime, $this->allowedMimes, true)) {
            throw new \InvalidArgumentException('Nur JPEG, PNG, WebP und AVIF sind erlaubt.');
        }
    }
}
