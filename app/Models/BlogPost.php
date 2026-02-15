<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class BlogPost extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'category_id',
        'title',
        'slug',
        'excerpt',
        'body',
        'cover_image',
        'status',
        'allow_comments',
        'published_at',
        'meta_title',
        'meta_description',
        'meta_keywords',
        'canonical_url',
    ];

    protected $casts = [
        'allow_comments' => 'boolean',
        'published_at' => 'datetime',
    ];

    protected $appends = [
        'cover_image_url',
    ];

    public function category()
    {
        return $this->belongsTo(BlogCategory::class, 'category_id');
    }

    public function tags()
    {
        return $this->belongsToMany(BlogTag::class, 'blog_post_tag');
    }

    public function comments()
    {
        return $this->hasMany(BlogComment::class, 'blog_post_id');
    }

    public function getCoverImageUrlAttribute()
    {
        if (!$this->cover_image) {
            return null;
        }

        if (Str::startsWith($this->cover_image, 'http://') || Str::startsWith($this->cover_image, 'https://')) {
            return $this->cover_image;
        }

        if (Str::startsWith($this->cover_image, 'assets/')) {
            return asset($this->cover_image);
        }

        try {
            return Storage::disk(config('filesystems.media_disk', 'minio'))->url($this->cover_image);
        } catch (\Throwable $th) {
            return asset($this->cover_image);
        }
    }
}
