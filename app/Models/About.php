<?php

namespace App\Models;

use App\Helpers\MediaUrl;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class About extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'about';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'job_title',
        'avatar',
        'cover',
        'email',
        'phone',
        'address',
        'description',
        'taglines',
        'hero_subtitle',
        'about_highlights',
        'social_links',
        'cv',
    ];

    protected $appends = [
        'avatar_url',
        'cover_url',
        'cv_url',
    ];

    public function getAvatarUrlAttribute()
    {
        return $this->resolveMediaUrl($this->avatar);
    }

    public function getCoverUrlAttribute()
    {
        return $this->resolveMediaUrl($this->cover);
    }

    public function getCvUrlAttribute()
    {
        return $this->resolveMediaUrl($this->cv);
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
