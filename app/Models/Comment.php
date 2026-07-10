<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Comment extends Model
{
    protected $fillable = [
        'article_id', // Keep for backwards compatibility
        'commentable_type',
        'commentable_id',
        'name',
        'email',
        'comment',
        'is_approved'
    ];

    protected $casts = [
        'is_approved' => 'boolean',
    ];

    /**
     * Get the parent commentable model (Article or Prediction).
     */
    public function commentable()
    {
        return $this->morphTo();
    }

    /**
     * Get the article that owns the comment (backwards compatibility).
     */
    public function article()
    {
        return $this->belongsTo(Article::class);
    }

    /**
     * Scope a query to only include approved comments.
     */
    public function scopeApproved($query)
    {
        return $query->where('is_approved', true);
    }
}
