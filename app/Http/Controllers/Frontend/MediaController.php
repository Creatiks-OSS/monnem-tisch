<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class MediaController extends Controller
{
    public function show(string $path): BinaryFileResponse
    {
        $file = public_media_file($path);
        if ($file === null) {
            abort(404);
        }

        $mime = @mime_content_type($file) ?: 'application/octet-stream';

        return response()->file($file, [
            'Content-Type' => $mime,
            'Cache-Control' => 'public, max-age=86400',
        ]);
    }
}
