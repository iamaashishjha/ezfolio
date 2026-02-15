<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Storage;

class MediaController extends Controller
{
    /**
     * Stream media objects from the configured storage disk.
     *
     * @param string $path
     * @return \Symfony\Component\HttpFoundation\StreamedResponse
     */
    public function show(string $path)
    {
        $disk = config('filesystems.media_disk', 'minio');
        $normalizedPath = ltrim($path, '/');

        if ($normalizedPath === '') {
            abort(404);
        }

        $storage = Storage::disk($disk);
        if (!$storage->exists($normalizedPath)) {
            abort(404);
        }

        $stream = $storage->readStream($normalizedPath);
        if (!is_resource($stream)) {
            abort(404);
        }

        $mimeType = $storage->mimeType($normalizedPath) ?: 'application/octet-stream';
        $headers = [
            'Content-Type' => $mimeType,
            'Cache-Control' => 'public, max-age=31536000',
        ];

        $size = $storage->size($normalizedPath);
        if (is_numeric($size)) {
            $headers['Content-Length'] = (string) $size;
        }

        return response()->stream(function () use ($stream) {
            fpassthru($stream);
            if (is_resource($stream)) {
                fclose($stream);
            }
        }, 200, $headers);
    }
}

