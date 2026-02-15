<?php

namespace App\Models;

use App\Helpers\MediaUrl;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Project extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'title',
        'categories',
        'thumbnail',
        'images',
        'details',
        'link'
    ];

    protected $appends = [
        'thumbnail_url',
        'images_urls',
    ];

    public function getThumbnailUrlAttribute()
    {
        return $this->resolveMediaUrl($this->thumbnail);
    }

    public function getImagesUrlsAttribute()
    {
        $images = json_decode($this->images, true);
        if (!is_array($images)) {
            return [];
        }

        return array_values(array_map(function ($path) {
            return $this->resolveMediaUrl($path);
        }, $images));
    }

    private function resolveMediaUrl(?string $path)
    {
        if (!$path) {
            return null;
        }

        if (Str::startsWith($path, 'http://') || Str::startsWith($path, 'https://')) {
            return $path;
        }

        if (Str::startsWith($path, 'assets/')) {
            return asset($path);
        }

        try {
            return MediaUrl::forPath($path);
        } catch (\Throwable $th) {
            return asset($path);
        }
    }
}
