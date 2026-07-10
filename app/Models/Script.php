<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Script extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'code',
        'position', // 'head', 'body'
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeHead($query)
    {
        return $query->where('position', 'head');
    }

    public function scopeBody($query)
    {
        return $query->where('position', 'body');
    }

    public function scopeBanner($query)
    {
        return $query->where('position', 'banner');
    }
}
