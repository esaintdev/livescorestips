<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VipPlan extends Model
{
    const CATEGORIES = [
        'Mega Plan',
    ];

    protected $fillable = [
        'category',
        'name',
        'duration',
        'duration_days',
        'price',
        'currency',
        'country_name',
        'country_code',
        'badge_text',
        'features',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'features' => 'array',
        'is_active' => 'boolean',
        'price' => 'decimal:2',
    ];

    /**
     * Get the subscriptions for this plan.
     */
    public function subscriptions()
    {
        return $this->hasMany(VipSubscription::class);
    }

    /**
     * Scope to get only active plans.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to order by sort_order.
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }

    /**
     * Get formatted price with currency.
     */
    public function getFormattedPriceAttribute()
    {
        return $this->currency . number_format((float) $this->price, 0);
    }
}
