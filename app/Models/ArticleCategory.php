<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ArticleCategory extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'description',
    ];

    /**
     * Get the articles for this category.
     */
    public function articles()
    {
        return $this->hasMany(Article::class, 'category_id');
    }
}
