<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
}
