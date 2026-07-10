<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SiteLink extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'url',
        'image',
        'section',
        'is_active',
        'order',
    ];

    // Scopes for easy filtering
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopePartners($query)
    {
        return $query->where('section', 'partner')->orderBy('order', 'asc');
    }

    public function scopeFooter($query)
    {
        return $query->where('section', 'footer')->orderBy('order', 'asc');
    }
}
