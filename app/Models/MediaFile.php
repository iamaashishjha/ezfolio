<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MediaFile extends Model
{
    protected $fillable = [
        'owner_type',
        'owner_id',
        'collection',
        'disk',
        'bucket',
        'path',
        'url',
        'mime_type',
        'extension',
        'size',
        'etag',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
    ];

    public function owner()
    {
        return $this->morphTo();
    }
}

