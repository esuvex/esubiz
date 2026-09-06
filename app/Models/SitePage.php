<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SitePage extends Model
{
    protected $fillable = [
        'title',
        'slug',
        'status',
        'builder_json',
        'meta_title',
        'meta_description',
    ];

    protected $casts = [
        'builder_json' => 'array',
    ];
}