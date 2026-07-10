<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SeoPage extends Model
{
    protected $fillable = [
        'title',
        'sub_header',
        'slug',
        'content',
        'meta_title',
        'meta_description',
        'meta_keywords',
        'is_active',
    ];
}
