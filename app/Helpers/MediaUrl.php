<?php

namespace App\Helpers;

class MediaUrl
{
    /**
     * Build a proxy URL for a storage path.
     *
     * @param string|null $path
     * @return string|null
     */
    public static function forPath(?string $path)
    {
        if (!$path) {
            return null;
        }

        $normalized = trim($path, '/');
        if ($normalized === '') {
            return null;
        }

        $segments = explode('/', $normalized);
        $encodedSegments = array_map(function ($segment) {
            return rawurlencode($segment);
        }, $segments);

        return url('media/' . implode('/', $encodedSegments));
    }
}

